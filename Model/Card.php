<?php
namespace GDW\Stripemx\Model;

use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Framework\Model\Context;
use Magento\Payment\Model\Method\Cc;
use GDW\Stripemx\Model\StripemxCard;
use Magento\Payment\Model\Method\Logger;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Card extends Cc
{
    const CODE = 'gdw_stripemx';
    protected $_code = self::CODE;   
    protected $_stripemx;
    protected $_typesCards;
    protected $_scopeConfig;
    protected $_countryFactory;
    protected $_canRefund = true;
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;   
    protected $_canRefundInvoicePartial = true;
    protected $_supportedCurrencyCodes = ["MXN"];

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ModuleListInterface $moduleList,
        TimezoneInterface $localeDate,
        CountryFactory $countryFactory,
        StripemxCard $_stripemx,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );
        $this->_stripemx = $_stripemx;
        $this->_scopeConfig = $scopeConfig;
        $this->_countryFactory = $countryFactory;
        $this->_typesCards = $this->getConfigData('cctypes');    
    }

    public function assignData(\Magento\Framework\DataObject $data) {

        parent::assignData($data);

        $content = (array) $data->getData();

        $info = $this->getInfoInstance();

        if (key_exists('additional_data', $content)) {
            $additionalData = $content['additional_data'];
            $card = json_decode($additionalData['card'], true);
            $this->_stripemx->setLogs('additionalData', $additionalData);
            $info->setAdditionalInformation('card', $additionalData['card']);
            $info->setAdditionalInformation('selected_plan', $additionalData['selected_plan']);
            $info->setAdditionalInformation('payment_intent_id', $additionalData['payment_intent_id']);

            if (isset($card['paymentMethod']['card'])) {
                $cardData = $card['paymentMethod']['card'];
                $info->setAdditionalInformation('cc_type', $cardData['brand'] ?? null);
                $info->setAdditionalInformation('cc_last4', $cardData['last4'] ?? null);
                $info->setAdditionalInformation('cc_exp_year', $cardData['exp_year'] ?? null);
                $info->setAdditionalInformation('cc_exp_month', $cardData['exp_month'] ?? null);
            }
        }

        return $this;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $message = '';
        $info = $this->getInfoInstance();
        $payment_intent_id = $info->getAdditionalInformation('payment_intent_id');
        $selected_plan = $info->getAdditionalInformation('selected_plan');

        
        try {
            \Stripe\Stripe::setApiKey($this->_stripemx->keySecret());  
            $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
            $charge = $payment_intent;
            $paymentIntentData = $payment_intent->toArray();
            $availablePlans = $paymentIntentData['payment_method_options']['card']['installments']['available_plans'] ?? [];
            $selectedPlanSupported = false;

            foreach ($availablePlans as $availablePlan) {
                $availablePlanCount = is_array($availablePlan) ? ($availablePlan['count'] ?? null) : (is_object($availablePlan) ? ($availablePlan->count ?? null) : null);
                if ((int) $availablePlanCount === (int) $selected_plan) {
                    $selectedPlanSupported = true;
                    break;
                }
            }

            if ($payment_intent->status !== 'succeeded') {
                if($selected_plan == 0){
                    $charge = $payment_intent->confirm();
                    $message = 'Cargo único | ';
                }elseif ($selectedPlanSupported) {
                    $data = ['payment_method_options' => [
                        'card' => [
                            'installments' => [
                                'plan' => [
                                    'count' => $selected_plan,
                                    'interval' => 'month',
                                    'type' => 'fixed_count'
                                    ]
                                ]
                            ]
                        ]
                    ]; 
                    $charge = $payment_intent->confirm($data);
                    $message = $selected_plan.' Meses sin intereses | ';
                } else {
                    $charge = $payment_intent->confirm();
                    $message = $selected_plan.' Meses sin intereses | ';
                }
            } else {
                $message = $selected_plan == 0 ? 'Cargo único | ' : $selected_plan.' Meses sin intereses | ';
            }

            if($charge->status != 'succeeded'){
                $this->_stripemx->setLogs('$charge', $charge);
                throw new \Magento\Framework\Validator\Exception(
                    __($charge->status)
                );
            }

            $this->_stripemx->setLogs('$charge', $charge);

            $chargeData = $charge->toArray();
            $firstCharge = $chargeData['charges']['data'][0] ?? null;

            if ($firstCharge !== null) {
                $disputed = !empty($firstCharge['disputed']) ? 'Con disputas' : 'Sin disputas';
                $payment->setAdditionalInformation('disputed', $disputed);

                if (!empty($firstCharge['outcome']['risk_level'])) {
                    $payment->setAdditionalInformation('risk_level', $firstCharge['outcome']['risk_level']);
                }
                if (!empty($firstCharge['outcome']['risk_score'])) {
                    $payment->setAdditionalInformation('risk_score', $firstCharge['outcome']['risk_score']);
                }
                
                if (!empty($firstCharge['payment_method_details']['card']['network'])) {
                    $payment->setAdditionalInformation('network', $firstCharge['payment_method_details']['card']['brand'] ?? null);
                }

                if (!empty($firstCharge['payment_method_details']['card']['funding'])) {
                    $payment->setAdditionalInformation('type_card', $firstCharge['payment_method_details']['card']['funding']);
                }
            }
            
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment->setTransactionId($charge->id);
            $payment->setAdditionalInformation('prepared_message', $message);
            $payment->setIsTransactionClosed(false);

        } catch (\Throwable $th) {
            $this->_stripemx->setLogs('Error Capture', $th->getMessage());
            if($this->_stripemx->globalerrorshow() != ''){
                throw new \Magento\Framework\Validator\Exception(__($this->_stripemx->globalerrorshow()));
            }else{
                throw new \Magento\Framework\Validator\Exception(__($th->getMessage()));
            }
        }

        return $this;
    }

    public function validate()
    {
        $this->getInfoInstance();
        return $this;
    }


    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        /* Check Key's */
        if (empty($this->_stripemx->keyPublic()) || empty($this->_stripemx->keySecret())) {
            $this->_logger->error(__('Please set credencials valid in your admin.'));
            $this->_stripemx->setLogs('error key','Please set credencials valid in your admin.');
            return false;
        }
        return parent::isAvailable($quote);
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            $this->_stripemx->setLogs('error Currency','Stripemx only enable in MXN currency');
            return false;
        }
        return true;
    }
}