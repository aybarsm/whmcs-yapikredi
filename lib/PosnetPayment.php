<?php

namespace WHMCS\Module\Gateway\YapiKredi;


use Illuminate\Support\Arr;
use WHMCS\Module\Gateway\YapiKredi\Abstracts\AbstractPosnetContext;
use WHMCS\Module\Gateway\YapiKredi\Contracts\PosnetPaymentInterface;
use Aybarsm\Whmcs\Service\Whmcs as WhmcsService;
use WHMCS\Module\Gateway\YapiKredi\Traits\Context\Helper;
use WHMCS\Module\Gateway\YapiKredi\Traits\Context\Maker;
use WHMCS\Module\Gateway\YapiKredi\Traits\Context\Task;

class PosnetPayment extends AbstractPosnetContext implements PosnetPaymentInterface
{
    use Helper, Maker, Task;

    protected string $context = 'Payment';

    public function attempt(array $params)
    {
        parent::init();

        $this->setTask('3ds', $params);
        $this->handlePosnetRequest();
        if (! is_null($terminate = $this->handlePostValidationStage())) return $terminate;
    }

    public function callback()
    {
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            WhmcsService::redirectSystemUrl();
        }

        parent::init();

        $this->setTask('init');
        $this->makeValidator();
        if (! is_null($terminate = $this->handlePostValidationStage())) return $terminate;

        // Ready to load WHMCS params
        $this->setTask('confirm', parent::getParamsByXid(Arr::get($this->validator->validated(), 'Xid')));
        $this->handlePosnetRequest();
        if (! is_null($terminate = $this->handlePostValidationStage())) return $terminate;

        $this->setTask('finalise');
        $this->handlePosnetRequest();
        if (! is_null($terminate = $this->handlePostValidationStage())) return $terminate;
    }

}