<?php
namespace GDW\Stripemx\Block;

use Magento\Framework\DataObject;
use Magento\Payment\Block\Info\Cc;

class Info extends Cc
{
    /**
     * @param DataObject|null $transport
     * @return DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $info = $this->getInfo();

        if (!$info) {
            return $transport;
        }

        $details = [];

        $selectedPlan = $info->getAdditionalInformation('selected_plan');
        if (is_numeric($selectedPlan)) {
            $selectedPlan = (int) $selectedPlan;
            $details[(string) __('Tipo de pago')] = $selectedPlan === 0
                ? (string) __('Cargo unico')
                : (string) __('%1 Meses sin intereses', $selectedPlan);
        } else {
            $preparedMessage = $info->getAdditionalInformation('prepared_message');
            $preparedMessage = is_scalar($preparedMessage) ? trim((string) $preparedMessage) : '';
            if ($preparedMessage !== '') {
                $details[(string) __('Tipo de pago')] = $preparedMessage;
            }
        }

        $ccType = $info->getAdditionalInformation('cc_type');
        $ccType = is_scalar($ccType) ? (string) $ccType : '';
        if ($ccType !== '') {
            $details[(string) __('Marca')] = $ccType;
        }

        $ccLast4 = $info->getAdditionalInformation('cc_last4');
        $ccLast4 = is_scalar($ccLast4) ? (string) $ccLast4 : '';
        if ($ccLast4 !== '') {
            $details[(string) __('Tarjeta')] = '**** ' . $ccLast4;
        }

        $expMonth = $info->getAdditionalInformation('cc_exp_month');
        $expYear = $info->getAdditionalInformation('cc_exp_year');
        $expMonth = is_scalar($expMonth) ? (string) $expMonth : '';
        $expYear = is_scalar($expYear) ? (string) $expYear : '';
        if ($expMonth !== '' && $expYear !== '') {
            $details[(string) __('Expira')] = $expMonth . '/' . $expYear;
        }

        $intentId = $info->getAdditionalInformation('payment_intent_id');
        $intentId = is_scalar($intentId) ? (string) $intentId : '';
        if ($intentId !== '') {
            $details[(string) __('Payment Intent')] = $intentId;
        }

        if (!empty($details)) {
            $transport->setData(array_merge($transport->getData(), $details));
        }

        return $transport;
    }
}
