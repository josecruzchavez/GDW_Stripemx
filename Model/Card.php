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
            $info->setCcType($card['paymentMethod']['card']['brand']);
            $info->setCcLast4($card['paymentMethod']['card']['last4']);
            $info->setCcExpYear($card['paymentMethod']['card']['exp_year']);
            $info->setCcExpMonth($card['paymentMethod']['card']['exp_month']);
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
            
            if($selected_plan == 0){
                $charge = $payment_intent->confirm();
                $message = 'Cargo único | ';
            }else{
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
            }

            if($charge->status != 'succeeded'){
                $this->_stripemx->setLogs('$charge', $charge);
                throw new \Magento\Framework\Validator\Exception(
                    __($charge->status)
                );
            }

            $this->_stripemx->setLogs('$charge', $charge);

            $disputed = $charge->charges->data[0]->disputed == true ? 'Con disputas' : 'Sin disputas';
            $payment->setAdditionalInformation('disputed', $disputed);

            if($charge->charges->data[0]->outcome->risk_level){
                $payment->setAdditionalInformation('risk_level', $charge->charges->data[0]->outcome->risk_level);
            }
            if($charge->charges->data[0]->outcome->risk_score){
                $payment->setAdditionalInformation('risk_score', $charge->charges->data[0]->outcome->risk_score);
            }
            
            if($charge->charges->data[0]->payment_method_details->card->network){
                $payment->setAdditionalInformation('network', $charge->charges->data[0]->payment_method_details->card->brand);
            }

            if($charge->charges->data[0]->payment_method_details->card->funding){
                $payment->setAdditionalInformation('type_card', $charge->charges->data[0]->payment_method_details->card->funding);
            }
            
            $payment->setTransactionId($charge->id)->setPreparedMessage($message)->setIsTransactionClosed(0);

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
        $errorMsg = false;
        $info = $this->getInfoInstance();
        if ($errorMsg) {
            throw new \Magento\Framework\Exception\LocalizedException($errorMsg);
        }
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