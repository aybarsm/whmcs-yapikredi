<?php

namespace WHMCS\Module\Gateway\YapiKredi\Contracts;

interface PosnetRefundInterface
{
    public function __invoke(array $params);
}