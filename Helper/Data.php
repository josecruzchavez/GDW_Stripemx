<?php
declare(strict_types=1);

namespace GDW\Stripemx\Helper;

class Data extends \GDW\Core\Helper\Data
{
    private const GDW_MODULE_CODE = 'payment/gdw_stripemx/';

    public function getModuleCode(): string
    {
        return self::GDW_MODULE_CODE;
    }

    /** @return mixed */
    public function getDirectVal(string $field)
    {
        return $this->getConfigValue($this->getModuleCode() . $field);
    }
}
