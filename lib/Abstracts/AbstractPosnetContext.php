<?php

namespace WHMCS\Module\Gateway\YapiKredi\Abstracts;

use WHMCS\Module\Gateway\YapiKredi\Traits\Context\Helper;
use WHMCS\Module\Gateway\YapiKredi\Traits\Context\Maker;
use WHMCS\Module\Gateway\YapiKredi\Traits\Context\Task;

abstract class AbstractPosnetContext extends AbstractPosnet
{
    protected string $context;
    protected string $task;
    protected \Carbon\Carbon $timestamp;

    protected array $params;

    protected ?\Aybarsm\Replacer\Service\Replacer $replacer;
    protected ?\SimpleXMLElement $xml;
    protected ?\GuzzleHttp\Client $http;
    protected ?\GuzzleHttp\TransferStats $stats;
    protected ?\Psr\Http\Message\ResponseInterface $response;
    protected ?\Illuminate\Validation\Validator $validator;

    protected array $result;

    abstract protected function handlePosnetRequest(): void;
    abstract protected function handlePostValidationStage();
    abstract protected function getProcessFee($amount, bool $withStatic = true): float;
    abstract protected function getBankHmacString(string $subject): string;
    abstract protected function getBankXmlDataString(): string;
    abstract protected function getParam(string $key, $default = null);
    abstract protected function setParam(string $key, $data, bool $save = true, bool $addReplacements = true);
    abstract protected function hasParam($key): bool;
    abstract public function getXmlResponse(): array;
    abstract protected function getLogResultString(): string;

    abstract protected function getTransactionId(): ?string;
    abstract protected function getBankCorrelationId(): ?string;
    abstract protected function makeXml(array $data = []): void;
    abstract protected function makeHttpClient(array $options = []): void;
    abstract protected function makeHttpRequest(string $method, $uri = '', array $options = []): void;
    abstract protected function makeTaskHmacString(string $source): void;
    abstract protected function makeValidator(array $data = [], array $rules = [], array $messages = [], array $attributes = []): void;
    abstract protected function createTransactionHistory(): void;
    abstract protected function makeResult(): void;
    abstract protected function getTaskConfigKey($key = ''): string;
    abstract protected function setTask(string $task, array $whmcsParams = []): void;
    abstract protected function getTaskConfig(string $key, $default = [], bool $applyReplacers = true, string $moduleDefaultKey = '');
    abstract protected function getTaskStatus(bool $forWhmcs = false, ?\Illuminate\Validation\Validator $validator = null): string;
    abstract protected function runTaskStatusCallbacks(string $status): void;
    abstract protected function getTaskTermination(string $status);
    abstract protected function getTaskType();




}