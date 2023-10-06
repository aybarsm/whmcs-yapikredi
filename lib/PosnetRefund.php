<?php

namespace WHMCS\Module\Gateway\YapiKredi;

use WHMCS\Module\Gateway\YapiKredi\Abstracts\AbstractPosnetContext;
use WHMCS\Module\Gateway\YapiKredi\Contracts\PosnetRefundInterface;
use WHMCS\Module\Gateway\YapiKredi\Traits\Context\Helper;
use WHMCS\Module\Gateway\YapiKredi\Traits\Context\Maker;
use WHMCS\Module\Gateway\YapiKredi\Traits\Context\Task;

class PosnetRefund extends AbstractPosnetContext implements PosnetRefundInterface
{
    use Helper, Maker, Task;
    protected string $context = 'Refund';

    public function __invoke(array $params)
    {
        parent::init();

        $this->setTask('init', $params);
        $this->makeValidator();
        if (! is_null($terminate = $this->handlePostValidationStage())) return $terminate;

        $this->setParam('context.hist', $this->validator->getData());

        // If the refund is full and the payment is intraday the next task should be reverse (cancel) otherwise return (refund)
        $this->setParam('process.refund.type', floatval($this->getParam('amount')) < floatval($this->getParam('context.hist.amount')) ? 'Partial' : 'Full');
        $isPaymentIntraday = $this->isProcessBankIntraday($this->getParam('context.hist.info.module.timestamp.iso8601zulu'));
        $this->setParam('process.refund.bank.type', ($isPaymentIntraday && $this->getParam('process.refund.type') === 'Full' ? 'reverse' : 'return'));

        $this->setTask('exec');
        $this->handlePosnetRequest();
        if (! is_null($terminate = $this->handlePostValidationStage())) return $terminate;
    }
}