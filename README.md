![gdw_stripemx](https://medios.gdw.mx/github_assets/gdw_stripemx/gdw_stripemx_01.jpg)

# GDW_Stripemx para Magento 2

[![Latest Stable Version](https://img.shields.io/packagist/v/gdw/stripemx?style=for-the-badge)](https://packagist.org/packages/gdw/stripemx) [![PHP Version Require](https://img.shields.io/packagist/dependency-v/gdw/stripemx/php?style=for-the-badge)](https://packagist.org/packages/gdw/stripemx) [![Magento Framework Require](https://img.shields.io/packagist/dependency-v/gdw/stripemx/magento%2Fframework?style=for-the-badge)](https://packagist.org/packages/gdw/stripemx) [![License](https://img.shields.io/packagist/l/gdw/stripemx?style=for-the-badge)](https://packagist.org/packages/gdw/stripemx)


El módulo GDW_Stripemx para Magento 2 permite realizar cobros a meses in intereses con ayuda de la API de Stripe. 

GDW_stripemx fue pensado para realizar el cobro de forma inmediata (capture and sale), antes de terminar el proceso de compra, se realizará una petición a stripe para verificar si la tarjeta de crédito puede aceptar pagos a meses sin intereses, si la tarjeta lo permite, se mostrará un selectbox para que el cliente elija la opción que más le convenga, hasta 24 MSI.

## Compatibilidad
- Rama 4.4.x: Magento 2.4.4+ con PHP 8.1+
- Rama 4.x: Magento 2.4.0 a 2.4.3 con PHP 7.4
- Rama 3.x: Magento 2.3.x con PHP 7.4

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
php bin/magento module:disable GDW_Stripemx
composer remove gdw/stripemx
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

* [ Sitio web](https://gdw.mx/?utm_source=github&utm_medium=gdw&utm_campaign=stripemx&utm_id=link)
* [Listado de Módulos](https://gdw.mx/modulos)
* [Facebook](https://www.facebook.com/GestionDigitalWeb)
* [Youtube](https://www.youtube.com/c/Gestiondigitalweb)

### Documentación

- [https://docs.gdw.mx/modulos/gdw_stripemx](https://docs.gdw.mx/modulos/gdw_stripemx)

### Changelog
Consulta el changelog del módulo en:

- [https://docs.gdw.mx/modulos/gdw_stripemx/changelog](https://docs.gdw.mx/modulos/gdw_stripemx/changelog)
 
