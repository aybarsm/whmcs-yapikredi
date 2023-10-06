<?php

namespace WHMCS\Module\Gateway\YapiKredi\Traits;

use Aybarsm\Replacer\Service\Replacer;
use Illuminate\Support\Arr;

trait Factory
{
    protected static function makeHtmlForm(array $config, bool $pretty = false): string
    {
        return Arr::toForm($config, $pretty);
    }
    protected static function makeReplacer(array $replacements): Replacer
    {
        return new Replacer(
            $replacements,
            static::getConfig('module.replacer.modifier_class_map',[]),
            static::getConfig('module.replacer.leftDelimiter','{{'),
            static::getConfig('module.replacer.rightDelimiter','}}'),
            static::getConfig('module.replacer.modifierDelimiter','|'),
            static::getConfig('module.replacer.keys.pattern','/[^a-zA-Z0-9]/'),
            static::getConfig('module.replacer.keys.replace','_')
        );
    }
    protected static function makeValidationFactory(): \Illuminate\Validation\Factory
    {
        $filesystem = new \Illuminate\Filesystem\Filesystem();
        $fileLoader = new \Illuminate\Translation\FileLoader($filesystem, static::getResourcePath('lang'));
        $translator = new \Illuminate\Translation\Translator($fileLoader, static::getConfig('module.validation.lang', 'en'));
        $translator->setFallback('en');

        $factory = new \Illuminate\Validation\Factory($translator);

        $factory->extend('exactly', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {
            $validator->requireParameterCount(1, $parameters, 'exactly');
            $validator->addReplacer('exactly', fn ($message, $attribute, $rule, $parameters) => str_replace([':value'], [$parameters[0]], $message));
            return $value == $parameters[0];
        });

        $factory->extend('valid_mac', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {
            $validator->requireParameterCount(1, $parameters, 'valid_mac');
            $validator->addReplacer('valid_mac', fn ($message, $attribute, $rule, $parameters) => str_replace([':value'], [$parameters[0]], $message));

            return hash_equals(base64_decode($parameters[0]), base64_decode($value));
        });

        $factory->extend('valid_xid', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {
            $invoiceId = \WHMCS\Module\Gateway\YapiKredi\Posnet::getInvoiceIdByXid($value);

            if (! $invoiceId){
                $validator->addReplacer('valid_xid', fn ($message, $attribute, $rule, $parameters) => str_replace([':reason'], ['invoice ID cannot be resolved'], $message));
                return false;
            }

            $paymentMethod = \WHMCS\Module\Gateway\YapiKredi\Posnet::getIdentifier();
            $invoice = \WHMCS\Billing\Invoice::where('paymentmethod', $paymentMethod)->find($invoiceId, ['id', 'paymentmethod', 'total']);

            if (! $invoice){
                $validator->addReplacer('valid_xid',
                    fn ($message, $attribute, $rule, $parameters) => str_replace([':reason'], ["invoice cannot be resolved with {$invoiceId} ID and {$paymentMethod} payment method"], $message)
                );
                return false;
            }

            return true;
        });

        return $factory;
    }
}