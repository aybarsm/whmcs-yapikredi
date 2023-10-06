<?php

use Aybarsm\Whmcs\Gateway\Yapikredi\Posnet;
use Aybarsm\Whmcs\Service\Whmcs as WhmcsService;

return (Posnet::isModuleActive() ? WhmcsService::getGatewaySettings(Posnet::getIdentifier()) : []);