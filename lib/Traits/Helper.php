<?php

namespace WHMCS\Module\Gateway\YapiKredi\Traits;

use Illuminate\Support\Str;
use Aybarsm\Whmcs\Service\Whmcs as WhmcsService;

trait Helper
{
    public static function getPath(string $path = ''): string
    {
        return WhmcsService::getGatewayPath(static::getIdentifier(), $path);
    }

    public static function getResourcePath(string $path = ''): string
    {
        return static::getPath('resources/' . trim($path, '/\\ '));
    }

    protected static function getSetting(string $key, $default = null)
    {
        return static::getConfig("gw.{$key}", $default);
    }

    protected static function get3dSecureIframeScript(): string
    {
        return static::getSetting('3DSECURE_IFRAME', 'NO') !== 'YES' ? static::getConfig('module.no_iframe_script') : '';
    }

    protected static function getBankXid($invoiceId): string
    {
        $potentialXid = static::getSetting('XID_PREFIX') . $invoiceId;

        return static::getSetting('XID_PREFIX') . str_repeat('0', 20 - strlen($potentialXid)) . $invoiceId;
    }

    protected static function getBankProcessAmount($amount): int
    {
        return intval(Str::of($amount)->replace(['.', ','], '')->__toString());
    }

    protected static function getBankProcessCurrencyCode(string $currencyCode): string
    {
        if (static::getSetting('CURRENCY_CODE', 'DYNAMIC') !== 'DYNAMIC'){
            return static::getSetting('CURRENCY_CODE');
        }

        $currencyCode = Str::upper($currencyCode);

        return in_array($currencyCode, ['TL', 'TRY', '₺']) ? 'TL' : (in_array($currencyCode, ['US', 'USD', '$']) ? 'US' :
            (in_array($currencyCode, ['EU', 'EUR', 'EURO', '€']) ? 'EU' : static::getSetting('CURRENCY_CODE_FALLBACK')));
    }

    protected static function getBankLang(string $clientLang, string $clientCountryCode): string
    {
        if (($selected = static::getSetting('BANK_LANG', 'DYNAMIC')) !== 'DYNAMIC'){
            return Str::lower($selected);
        }

        [$clientLang, $clientCountryCode] = [Str::lower($clientLang), Str::upper($clientCountryCode)];

        return $clientLang == 'turkish' || $clientCountryCode == 'TR' ? 'tr' : 'en';
    }

    protected static function getBankCardExp($cardExp): string
    {
        /*
         * The bank asks exp date as ym:
         * Kredi kartı son kullanım tarihi – Formatı yıl ay olacak şekilde YYAA
         */

        return \Carbon\Carbon::createFromFormat('my', $cardExp)->format('ym');
    }

    protected static function getParamsByInvoiceId($invoiceId): array
    {
        return getGatewayVariables(static::getIdentifier(), $invoiceId);
    }
    protected static function getParamsByXid(string $xid): array
    {
        return static::getParamsByInvoiceId(static::getInvoiceIdByXid($xid));
    }
    protected static function isProcessBankIntraday(string $timestamp): bool
    {
        $transaction = \Carbon\Carbon::parse($timestamp, 'UTC')->setTimezone('Europe/Istanbul');

        return \Carbon\Carbon::now('Europe/Istanbul')->lessThan($transaction->endOfDay());
    }

}