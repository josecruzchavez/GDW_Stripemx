<?php
namespace GDW\Stripemx\Model\Adminhtml\Source;

class PaymentLimit implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 3, 'label' => __('3 meses (min. $300.00 MXN)')],
            ['value' => 6, 'label' => __('6 meses (min. $600.00 MXN)')],
            ['value' => 9, 'label' => __('9 meses (min. $900.00 MXN)')],
            ['value' => 12, 'label' => __('12 meses (min. $1200.00 MXN)')],
            ['value' => 18, 'label' => __('18 meses (min. $1800.00 MXN)')],
            ['value' => 24, 'label' => __('24 meses (min. $2400.00 MXN)')]
        ];
    }
}