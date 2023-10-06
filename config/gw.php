<?php

use WHMCS\Module\Gateway\YapiKredi\Posnet;
use Aybarsm\Whmcs\Service\Whmcs as WhmcsService;

return (Posnet::isModuleActive() ? WhmcsService::getGatewaySettings(Posnet::getIdentifier()) : []);