<?php
namespace GDW\Stripemx\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
{
    private function normalizeAdditionalValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }

        return null;
    }

    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);

        $paymentInfo = $method->getInfoInstance();

        if ($data->getDataByKey('card') !== null) {
            $paymentInfo->setAdditionalInformation(
                'card',
                $this->normalizeAdditionalValue($data->getData('card'))
            );
        }

        if ($data->getDataByKey('selected_plan') !== null) {
            $paymentInfo->setAdditionalInformation(
                'selected_plan',
                $this->normalizeAdditionalValue($data->getData('selected_plan'))
            );
        }

        if ($data->getDataByKey('payment_intent_id') !== null) {
            $paymentInfo->setAdditionalInformation(
                'payment_intent_id',
                $this->normalizeAdditionalValue($data->getData('payment_intent_id'))
            );
        }
    }
}
