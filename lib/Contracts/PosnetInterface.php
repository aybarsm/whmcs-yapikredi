<?php

namespace WHMCS\Module\Gateway\YapiKredi\Contracts;

interface PosnetInterface
{
    public static function isModuleActive(): bool;
    public static function isInitialised(): bool;
    public static function getIdentifier(): string;
    public static function getMetaData(): array;
    public static function getSettings(): array;
    public static function isTestMode(): bool;
    public static function isRefundEnabled(): bool;
    public static function paymentAttempt(array $params): string;
    public static function paymentCallback(): void;
    public static function refund(array $params): array;

    public static function getInvoiceIdByXid(?string $xid): ?int;
    public static function getBankAmountByAmount(string $amount): int;

}