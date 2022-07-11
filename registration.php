<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'GDW_Stripemx',
    isset($file) ? dirname($file) : __DIR__
);
