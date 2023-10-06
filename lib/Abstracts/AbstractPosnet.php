<?php

namespace WHMCS\Module\Gateway\YapiKredi\Abstracts;

use Illuminate\Support\Traits\Macroable;

use WHMCS\Module\Gateway\YapiKredi\Contracts\PosnetInterface;
use WHMCS\Module\Gateway\YapiKredi\Traits\Config;
use WHMCS\Module\Gateway\YapiKredi\Traits\Helper;
use WHMCS\Module\Gateway\YapiKredi\Traits\External;
use WHMCS\Module\Gateway\YapiKredi\Traits\Factory;
use WHMCS\Module\Gateway\YapiKredi\Traits\Whmcs;
use Aybarsm\Whmcs\Service\Whmcs as WhmcsService;

abstract class AbstractPosnet implements PosnetInterface
{
    use Macroable, Config, Helper, External, Factory, Whmcs;

    protected static string $identifier = 'yapikredi';
    protected static bool $init;
    protected static \Illuminate\Validation\Factory $validationFactory;

    protected static function init(): void
    {
        if (isset(static::$init)) {
            return;
        }

        WhmcsService::registerMacros();
        static::$validationFactory = static::makeValidationFactory();

        $adminLang = static::getConfig('module.whmcs.admin_lang', 'english');
        WhmcsService::addAdminLangResource(static::getResourcePath('lang/whmcs/admin/' . "{$adminLang}.php"), $adminLang);

        \App::load_function('gateway');
        \App::load_function('invoice');

        static::$init = true;
    }

}