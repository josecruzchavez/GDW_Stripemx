<?php

namespace GDW\Stripemx\Model\Ui;

use \Magento\Payment\Model\CcConfig;
use \GDW\Stripemx\Model\StripemxCard;
use \Magento\Framework\View\Asset\Source;
use \Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'gdw_stripemx';

    protected $_ccoptions = [
        'visa' => 'Visa',
        'amex' => 'American Express',
        'mastercard' => 'Mastercard'
    ];

    protected $ccConfig;
    protected $stripemx;
    protected $assetSource;

    public function __construct(
        CcConfig $ccConfig,
        Source $assetSource,
        StripemxCard $stripemx
    ) {
        $this->stripemx = $stripemx;
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'note' => $this->stripemx->note(),
                    'isDebug' => $this->stripemx->isDebug(),
                    'notemsi' => $this->stripemx->notemsi(),
                    'urlcheck' => 'stripemx/actions/payment',
                    'PublicKey' => $this->stripemx->keyPublic(),
                    'years' => [self::CODE => $this->_getYears()],
                    'months' => [self::CODE => $this->_getMonths()],
                    'ssStartYears' => [self::CODE => $this->_getStartYears()],
                    'availableTypes' => [self::CODE => $this->getCcAvailableTypes()],
                    'hasVerification' => [self::CODE => $this->ccConfig->hasVerification()]
                ],
            ],
        ];
    }

    protected function getCcAvailableTypes()
    {
        return $this->_ccoptions;
    }

    private function _getMonths()
    {
        return [
            "1" => "01 - Enero",
            "2" => "02 - Febrero",
            "3" => "03 - Marzo",
            "4" => "04 - Abril",
            "5" => "05 - Mayo",
            "6" => "06 - Junio",
            "7" => "07 - Julio",
            "8" => "08 - Augosto",
            "9" => "09 - Septiembre",
            "10" => "10 - Octubre",
            "11" => "11 - Noviembre",
            "12" => "12 - Diciembre",
        ];
    }

    private function _getYears()
    {
        $years = [];
        $cYear = (integer) date("Y");
        $cYear = $cYear - 1;
        for ($i = 1; $i <= 12; $i++) {
            $year = (string) ($cYear + $i);
            $years[$year] = $year;
        }

        return $years;
    }

    private function _getStartYears()
    {
        $years = [];
        $cYear = (integer) date("Y");

        for ($i = 5; $i >= 0; $i--) {
            $year = (string) ($cYear - $i);
            $years[$year] = $year;
        }

        return $years;
    }
}
