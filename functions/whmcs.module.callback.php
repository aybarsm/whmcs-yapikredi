<?php

// WHMCS Init - Avoid symlink problems with $_SERVER['SCRIPT_FILENAME']
require_once dirname($_SERVER['SCRIPT_FILENAME'],4) . DIRECTORY_SEPARATOR . 'init.php';

// Module Vendor Autoload
require_once __DIR__ . DIRECTORY_SEPARATOR . 'module.vendor.autoload.php';

WHMCS\Module\Gateway\YapiKredi\Posnet::paymentCallback();