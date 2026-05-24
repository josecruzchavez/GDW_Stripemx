<?php
declare(strict_types=1);

namespace GDW\Stripemx\Helper;

final class GdwModuleMeta
{
    /** @return array{desc:string, config_path:string, config_anchor:string, repo_url:string, docs_url:string} */
    public static function getMeta(): array
    {
        return [
            'desc'           => 'Permite cobrar con Stripe y MSI en México.',
            'config_path'    => 'adminhtml/system_config/edit/section/payment/',
            'config_anchor'  => '#payment_other_gdw_stripemx-link',
            'repo_url'       => 'https://github.com/josecruzchavez/GDW_Stripemx',
            'docs_url'       => 'https://docs.gdw.mx/modulos/gdw_stripemx',
        ];
    }
}
