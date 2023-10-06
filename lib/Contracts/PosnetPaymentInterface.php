<?php

namespace WHMCS\Module\Gateway\YapiKredi\Contracts;

interface PosnetPaymentInterface
{
    public function attempt(array $params);
    public function callback();

}