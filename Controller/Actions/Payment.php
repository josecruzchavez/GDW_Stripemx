<?php
namespace GDW\Stripemx\Controller\Actions;

use \GDW\Stripemx\Model\StripemxCard;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\Result\Redirect;
use \Magento\Framework\Controller\Result\JsonFactory;

class Payment extends \Magento\Framework\App\Action\Action
{
    protected $request;
    protected $stripemx;
    protected $pageFactory;
    protected $resultJsonFactory;
    protected $resultRedirectFactory;
    
    public function __construct(
        Http $request,
        Context $context,
        StripemxCard $stripemx,
        PageFactory $pageFactory,
        JsonFactory $resultJsonFactory,
        Redirect $resultRedirectFactory
        ){
        $this->request = $request;
        $this->stripemx = $stripemx;
        $this->pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        return parent::__construct($context);
        
    }

    public function execute()
    {
        /* return $this->process(); */
        if ($this->getRequest()->isPost()) {
            return $this->process();
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('');
            return $resultRedirect;
        }
    }

    protected function process()
    {
        $data = $this->request->getParams();
        $resultJson = $this->resultJsonFactory->create();
        try {
            \Stripe\Stripe::setApiKey($this->stripemx->keySecret());

            $const = [];
            $const['payment_method'] = $data['payment']['paymentMethod']['id'];
            $const['amount'] = number_format($data['totals']['base_grand_total'], 2, '', '');
            $const['currency'] = strtolower($data['totals']['base_currency_code']);
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

            $iniPlans = $intent->payment_method_options->card->installments->available_plans;


            if(!empty($iniPlans)){
                $iniPlans = $this->getCoutas($iniPlans);
            }

            return $resultJson->setData(['intent_id' => $intent->id, 'available_plans' => $iniPlans]);

        } catch (\Throwable $th) {
            return $resultJson->setData(['error' => $th->getMessage()]);
        }
    }
    
    public function getDiscount($data){
        if($data['totals']['base_discount_amount'] != 0){
            return $data['totals']['base_discount_amount'];
        }
        return false;
    }

    public function getCoupon($data){
        if(isset($data['totals']['coupon_code'])){
            return $data['totals']['coupon_code'];
        }
        return false;
    }

    public function getCoutas($iniPlans) {
        $enableCoutas = explode(',', $this->stripemx->getCoutas());
        foreach($iniPlans as $key => $plan){
            if(!in_array($plan->count,$enableCoutas)){
               unset($iniPlans[$key]);
            }
        }
        return $iniPlans;
    }
}
