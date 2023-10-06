<?php

namespace WHMCS\Module\Gateway\YapiKredi\Traits;

use Illuminate\Support\Str;
use WHMCS\Module\Gateway\YapiKredi\PosnetPayment;
use WHMCS\Module\Gateway\YapiKredi\PosnetRefund;
use Aybarsm\Whmcs\Service\Whmcs as WhmcsService;

trait External
{

    public static function isModuleActive(): bool
    {
        return WhmcsService::isGatewayActive(static::getIdentifier());
    }
    public static function isInitialised(): bool
    {
        return isset(static::$init);
    }

    public static function getIdentifier(): string
    {
        return static::$identifier;
    }

    public static function getInvoiceIdByXid(?string $xid): ?int
    {
        if (empty($xid)){
            return null;
        }

        $invoiceId = preg_replace('/\D/', '', $xid);

        return ! empty($invoiceId) && is_numeric($invoiceId) ? intval($invoiceId) : null;
    }

    public static function getBankAmountByAmount(string $amount): int
    {
        return intval(Str::of($amount)->replace(['.', ','], '')->__toString());
    }

    public static function getBankOrderDateByTimestamp($timestamp): string
    {
        $transaction = \Carbon\Carbon::parse($timestamp, 'UTC')->setTimezone('Europe/Istanbul');

        return $transaction->format('Ymd');
    }

    public static function getMetaData(): array
    {
        return static::getConfig('metadata', []);
    }

    public static function getSettings(): array
    {
        return static::getConfig('settings', []);
    }
    public static function isTestMode(): bool
    {
        return static::isModuleActive() && static::getSetting('TEST_MODE', 'NO') === 'YES';
    }
    public static function isRefundEnabled(): bool
    {
        return static::isModuleActive() && static::getSetting('REFUND_ENABLED', 'NO') === 'YES';
    }

    public static function paymentAttempt(array $params): string
    {
        return (new PosnetPayment())->attempt($params);
    }

    public static function paymentCallback(): void
    {
        (new PosnetPayment())->callback();
    }

    public static function refund(array $params): array
    {
        return (new PosnetRefund())($params);
    }
}