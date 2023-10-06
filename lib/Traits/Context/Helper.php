<?php

namespace WHMCS\Module\Gateway\YapiKredi\Traits\Context;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Helper
{
    protected function handlePosnetRequest(): void
    {
        if ($this->getTaskConfig('hmac.request', null, false) !== null){
            $this->makeTaskHmacString('request');
        }

        $this->makeXml($this->getTaskConfig('data'));
        $this->makeHttpClient($this->getTaskConfig('http_options'));

        $this->makeHttpRequest('POST');

        if ($this->getTaskConfig('hmac.response', null, false) !== null){
            $this->makeTaskHmacString('response');
        }

        $this->makeValidator($this->getXmlResponse());
    }

    protected function handlePostValidationStage()
    {
        $this->makeResult();
        $this->runTaskStatusCallbacks($this->getTaskStatus());

        $termination = $this->getTaskTermination($this->getTaskStatus());
        return is_callable($termination) ? $termination($this) : $termination;
    }

    protected function getProcessFee($amount, bool $withStatic = true): float
    {
        $fee = 0;
        $feeStatic = $this->getParam('context.hist.info.bank.fee.static', static::getSetting('PROCESS_FEE_STATIC', '0'));
        $feeRate = $this->getParam('context.hist.info.bank.fee.rate', static::getSetting('PROCESS_FEE_RATE', '0'));

        if ($withStatic){
            $static = is_numeric($feeStatic) ? floatval($feeStatic) : 0;
            $fee += round($static, 2);
        }

        $rate = is_numeric($feeRate) ? floatval($feeRate) : 0;
        $fee += round((floatval($amount) / 100) * $rate, 2);

        return round($fee, 2);
    }

    protected function getBankHmacString(string $subject): string
    {
        return base64_encode(hash('sha256', $this->replacer->apply($subject),true));
    }

    protected function getBankXmlDataString(): string
    {
        return Str::of($this->xml->asXML())->after( '?>')->trim()->__toString();
    }

    protected function getParam(string $key, $default = null)
    {
        if (! isset($this->params)){
            return $default;
        }

        return Arr::get($this->params, $key, $default);
    }

    protected function setParam(string $key, $data, bool $save = true, bool $addReplacements = true)
    {
        if ($addReplacements){
            $this->replacer->addReplacements([$key => $data]);
        }

        if ($save){
            if (! isset($this->params)){
                $this->params = [];
            }
            Arr::set($this->params, $key, $data);
        }
    }

    protected function hasParam($key): bool
    {
        return isset($this->params) && Arr::has($this->params, $key);
    }

    public function getXmlResponse(): array
    {
        return isset($this->response) ? Arr::fromXml($this->response->getBody()->__toString()) : [];
    }

    protected function getLogResultString(): string
    {
        [$context, $task, $status] = [Str::title($this->context), Str::title($this->task), $this->getTaskStatus()];

        return "{$context}:{$task}:{$status}";
    }
    protected function getBankCorrelationId(): ?string
    {
        return $this->replacer->apply(parent::getConfig('module.bank.correlation_id'));
    }

    protected function getTransactionId(): ?string
    {
        return $this->replacer->apply(parent::getConfig('module.whmcs.transaction_id'));
    }
}