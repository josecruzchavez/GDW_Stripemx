<?php
namespace GDW\Stripemx\Block\Adminhtml\System;

use GDW\Core\Block\Adminhtml\System\Core\ModuleInfoFull as Fieldset;

class ModuleInfoFull extends Fieldset
{
    const GDW_MODULE_CODE = 'GDW_Stripemx';
    const GDW_MODULE_LINK = 'adminhtml/system_config/edit/section/payment/';
    const GDW_MODULE_LINK_SECC = '#payment_other_gdw_stripemx-link';

    public function getDescFull()
    {
        $html =
<<<HTML
    <p>GDW_stripemx fue pensado para realizar el cobro de forma inmediata (capture and sale), antes de terminar el proceso de compra, se realizará una petición a stripe para verificar si la tarjeta de crédito puede aceptar pagos a meses sin intereses, si la tarjeta lo permite, se mostrará un selectbox para que el cliente elija la opción que más le convenga, hasta 24 MSI.</p>
    <p><a href="https://github.com/josecruzchavez/GDW_Stripemx" target="_blank">Más información &gt;&gt; </a></p>
HTML;
        return $html;
    }
}