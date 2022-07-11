<?php
namespace GDW\Stripemx\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
{

    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);

        $paymentInfo = $method->getInfoInstance();

        if ($data->getDataByKey('card') !== null) {
            $paymentInfo->setAdditionalInformation(
                'card',
                $data->getData('card')
            );
        }

        if ($data->getDataByKey('selected_plan') !== null) {
            $paymentInfo->setAdditionalInformation(
                'selected_plan',
                $data->getData('selected_plan')
            );
        }

        if ($data->getDataByKey('payment_intent_id') !== null) {
            $paymentInfo->setAdditionalInformation(
                'payment_intent_id',
                $data->getData('payment_intent_id')
            );
        }
    }
}
