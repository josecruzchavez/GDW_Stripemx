<?php
namespace GDW\Stripemx\Controller\Actions;

use \GDW\Stripemx\Model\StripemxCard;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\Result\RedirectFactory;
use \Magento\Framework\Controller\Result\JsonFactory;

class Payment extends \Magento\Framework\App\Action\Action
{
    protected Http $request;
    protected StripemxCard $stripemx;
    protected PageFactory $pageFactory;
    protected JsonFactory $resultJsonFactory;
    protected $resultRedirectFactory;
    
    public function __construct(
        Http $request,
        Context $context,
        StripemxCard $stripemx,
        PageFactory $pageFactory,
        JsonFactory $resultJsonFactory,
        RedirectFactory $resultRedirectFactory
        ){
        $this->request = $request;
        $this->stripemx = $stripemx;
        $this->pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
        
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        /* return $this->process(); */
        if ($this->request->isPost()) {
            return $this->process();
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('');
            return $resultRedirect;
        }
    }

    protected function process(): \Magento\Framework\Controller\Result\Json
    {
        $data = $this->request->getParams();
        $resultJson = $this->resultJsonFactory->create();
        try {
            \Stripe\Stripe::setApiKey($this->stripemx->keySecret());

            $const = [];
            $const['payment_method'] = $data['payment']['paymentMethod']['id'];
            $const['amount'] = (int) round(((float) $data['totals']['base_grand_total']) * 100);
            $const['currency'] = strtolower($data['totals']['base_currency_code']);
            $const['payment_method_types'] = ['card'];
            $const['payment_method_options']['card']['installments']['enabled'] = true;
            $const['metadata']['Envío'] = number_format($data['totals']['shipping_amount'], 2, '.', '');
            $const['metadata']['Impuestos'] = number_format($data['totals']['tax_amount'], 2, '.', '');
            
            if($this->getDiscount($data) != false){
                $const['metadata']['Descuento'] = $this->getDiscount($data);
            }

            if($this->getCoupon($data) != false){
                $const['metadata']['Cupón'] = $this->getCoupon($data);
            }

            foreach($data['totals']['items'] as $key => $item){
                $const['metadata']['Producto '.++$key.':'] = 'Qty: '.$item['qty'].' | '.$item['name'];
            }

            $intent = \Stripe\PaymentIntent::create($const);

            $intentData = $intent->toArray();
            $iniPlans = $intentData['payment_method_options']['card']['installments']['available_plans'] ?? [];
            $iniPlans = $this->normalizePlans($iniPlans);

            if(!empty($iniPlans)){
                $iniPlans = $this->getCoutas($iniPlans);
            } elseif ($this->stripemx->sandbox()) {
                $iniPlans = $this->getDemoCoutas();
            }

            return $resultJson->setData(['intent_id' => $intent->id, 'available_plans' => $iniPlans]);

        } catch (\Throwable $th) {
            return $resultJson->setData(['error' => $th->getMessage()]);
        }
    }
    
    /**
     * @param array<string, mixed> $data
     */
    public function getDiscount(array $data): string|false
    {
        $totals = $data['totals'] ?? null;
        if (!is_array($totals)) {
            return false;
        }

        $baseDiscountAmount = $totals['base_discount_amount'] ?? null;
        if (is_numeric($baseDiscountAmount) && (float) $baseDiscountAmount != 0.0) {
            return number_format((float) $baseDiscountAmount, 2, '.', '');
        }

        return false;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function getCoupon(array $data): string|false
    {
        $totals = $data['totals'] ?? null;
        if (!is_array($totals)) {
            return false;
        }

        $couponCode = $totals['coupon_code'] ?? null;
        if ($couponCode !== null && (is_scalar($couponCode) || (is_object($couponCode) && method_exists($couponCode, '__toString')))) {
            return (string) $couponCode;
        }

        return false;
    }

    /**
     * @param array<int, \stdClass|array<string, mixed>> $iniPlans
     * @return array<int, \stdClass|array<string, mixed>>
     */
    public function getCoutas(array $iniPlans): array
    {
        $enableCoutas = $this->normalizeCoutas();
        foreach($iniPlans as $key => $plan){
            $planCount = is_object($plan) ? ($plan->count ?? null) : ($plan['count'] ?? null);
            if ($planCount === null || !in_array((int) $planCount, $enableCoutas, true)){
               unset($iniPlans[$key]);
            }
        }
        return $iniPlans;
    }

    /**
     * @param array<int, array<string, mixed>> $iniPlans
     * @return array<int, \stdClass>
     */
    protected function normalizePlans(array $iniPlans): array
    {
        $plans = [];

        foreach ($iniPlans as $plan) {
            if (!is_array($plan)) {
                continue;
            }

            $normalizedPlan = new \stdClass();
            $count = $plan['count'] ?? null;
            $interval = $plan['interval'] ?? null;
            $type = $plan['type'] ?? null;

            $normalizedPlan->count = is_numeric($count) ? (int) $count : 0;
            $normalizedPlan->interval = (is_scalar($interval) || (is_object($interval) && method_exists($interval, '__toString'))) ? (string) $interval : 'month';
            $normalizedPlan->type = (is_scalar($type) || (is_object($type) && method_exists($type, '__toString'))) ? (string) $type : 'fixed_count';
            $plans[] = $normalizedPlan;
        }

        return $plans;
    }

    /**
     * @return array<int, int>
     */
    protected function normalizeCoutas(): array
    {
        $enableCoutas = explode(',', (string) $this->stripemx->getCoutas());
        $enableCoutas = array_map('trim', $enableCoutas);
        $enableCoutas = array_filter($enableCoutas, function ($value) {
            return $value !== '';
        });

        return array_map('intval', array_values($enableCoutas));
    }

    /**
     * @return array<int, \stdClass>
     */
    protected function getDemoCoutas(): array
    {
        $plans = [];
        $enableCoutas = $this->normalizeCoutas();

        foreach ($enableCoutas as $count) {
            if ($count <= 0) {
                continue;
            }

            $plan = new \stdClass();
            $plan->count = $count;
            $plan->interval = 'month';
            $plan->type = 'fixed_count';
            $plans[] = $plan;
        }

        return $plans;
    }
}
