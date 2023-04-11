<?php
namespace GDW\Stripemx\Block\Adminhtml\System;

use GDW\Core\Block\Adminhtml\System\Core\ModuleInfo as Fieldset;

class ModuleInfo extends Fieldset
{
    const GDW_MODULE_CODE = 'GDW_Stripemx';

    public function getDesc()
    {
        return 'Permite cobrar con stripe y MSI en México.';
    }
}