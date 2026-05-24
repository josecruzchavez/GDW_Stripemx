<?php

namespace GDW\Stripemx\Model\Ui;

use \Magento\Payment\Model\CcConfig;
use \GDW\Stripemx\Model\StripemxCard;
use \Magento\Framework\View\Asset\Source;
use \Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'gdw_stripemx';

    /** @var array<string, string> */
    protected array $_ccoptions = [
        'visa' => 'Visa',
        'amex' => 'American Express',
        'mastercard' => 'Mastercard'
    ];

    protected CcConfig $ccConfig;
    protected StripemxCard $stripemx;
    protected Source $assetSource;

    public function __construct(
        CcConfig $ccConfig,
        Source $assetSource,
        StripemxCard $stripemx
    ) {
        $this->stripemx = $stripemx;
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
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

    /**
     * @return array<string, string>
     */
    protected function getCcAvailableTypes(): array
    {
        return $this->_ccoptions;
    }

    /**
     * @return array<int, string>
     */
    private function _getMonths(): array
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

    /**
     * @return array<int, string>
     */
    private function _getYears(): array
    {
        $years = [];
        $cYear = (integer) date("Y");
        $cYear = $cYear - 1;
        for ($i = 1; $i <= 12; $i++) {
            $year = $cYear + $i;
            $years[$year] = (string) $year;
        }

        return $years;
    }

    /**
     * @return array<int, string>
     */
    private function _getStartYears(): array
    {
        $years = [];
        $cYear = (integer) date("Y");

        for ($i = 5; $i >= 0; $i--) {
            $year = $cYear - $i;
            $years[$year] = (string) $year;
        }

        return $years;
    }
}
