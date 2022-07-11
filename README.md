# GDW Stripemx Magento 2
El módulo GDW_Stripemx para Magento 2 permite realizar cobros a meses in intereses con ayuda de la API de Stripe. 

GDW_stripemx fue pensado para realizar el cobro de forma inmediata (capture and sale), antes de terminar el proceso de compra, se realizará una petición a stripe para verificar si la tarjeta de crédito puede aceptar pagos a meses sin intereses, si la tarjeta lo permite, se mostrará un selectbox para que el cliente elija la opción que más le convenga, hasta 24 MSI.

![GDW_Stripemx](https://gestiondigitalweb.com/github_assets/gdw_stripemx/gdw_stripemx_01.jpg)

## Compatibilidad
✓ Magento 2.3.x, ✓ Magento 2.4.x

## Funciones destacadas

* Fácil configuración.
* Verificación de TDC en tiempo real.
* Selección de límite de cuotas desde el admin.
* Modo depuración.
* Mensaje personalizado deerror
* Campo para CSS adicional


###### Ejecuta los siguientes comandos en la ruta base de Magento.

### Instalación

```
composer require gdw/stripemx

php bin/magento module:enable GDW_Stripemx
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

### Actualización

```
composer update gdw/stripemx

php bin/magento module:enable GDW_Stripemx
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

### Eliminación

```
php bin/magento module:disbale GDW_Stripemx
composer remove gdw/opengraph
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

### Expresiones de Gratitud

* 📢 Comenta a otros sobre este proyecto.
* 👨🏽‍💻 Da las gracias públicamente.
* [🍺 Invítame una cerveza](https://www.paypal.me/gestiondigitalweb)


### Otros enlaces

* [ Sitio web](https://gestiondigitalweb.com/?utm_source=github&utm_medium=gdw&utm_campaign=opengraph&utm_id=link)
* [Listado de Módulos](https://gestiondigitalweb.com/gdw-modulos/index.php)
* [Facebook](https://www.facebook.com/GestionDigitalWeb)
* [Youtube](https://www.youtube.com/c/Gestiondigitalweb)
 
