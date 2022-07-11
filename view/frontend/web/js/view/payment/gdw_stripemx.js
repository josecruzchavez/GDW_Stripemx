define(['uiComponent', 'jquery', 'mage/url', 'Magento_Checkout/js/model/payment/renderer-list'],
    function (Component, $, urlBuilder, rendererList) {
        'use strict';
        
        var useComponent = 'GDW_Stripemx/js/view/payment/method-renderer/gdw_stripemx';

        rendererList.push({ type: 'gdw_stripemx', component: useComponent });

        return Component.extend({});
    }
);
