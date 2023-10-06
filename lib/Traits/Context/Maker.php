<?php

namespace WHMCS\Module\Gateway\YapiKredi\Traits\Context;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Maker
{
    protected function makeXml(array $data = []): void
    {
        $data = empty($data) ? $this->getTaskConfig('data') : $data;

        $this->xml = Arr::toXml(
            $data,
            'posnetRequest',
            new \SimpleXMLElement('<?xml version="1.0" encoding="ISO-8859-9"?><posnetRequest/>')
        );

        $this->setParam('bank.xml.data.string', $this->getBankXmlDataString(), false);
    }
    protected function makeHttpClient(array $options = []): void
    {
        $this->http = new \GuzzleHttp\Client($options);
    }

    protected function makeHttpRequest(string $method, $uri = '', array $options = []): void
    {
        $stats = null;
        $options = array_merge($options, [
                // Avoid decoupling of instances
                'on_stats' => function (\GuzzleHttp\TransferStats $transferStats) use(&$stats) {
                    $stats = $transferStats;
                }
            ]
        );

        $this->http->request($method, $uri, $options);
        $this->stats = $stats;
        $this->response = $stats->hasResponse() ? $stats->getResponse() : null;

        $this->setParam("task.{$this->task}.xml.response", $this->getXmlResponse());
    }

    protected function makeTaskHmacString(string $source): void
    {
        $raw = $this->getTaskConfig("hmac.{$source}", '');

        $hmacString = $this->getBankHmacString($raw);

        $this->setParam("task.{$this->task}.hmac.{$source}", $hmacString);
    }

    protected function makeValidator(array $data = [], array $rules = [], array $messages = [], array $attributes = []): void
    {
        $data = empty($data) ? $this->getTaskConfig('data') : $data;
        $rules = empty($rules) ? $this->getTaskConfig('validation.rules') : $rules;
        $messages = empty($messages) ? $this->getTaskConfig('validation.messages') : $messages;
        $attributes = empty($attributes) ? $this->getTaskConfig('validation.attributes') : $attributes;

        $validator = parent::$validationFactory->make($data, $rules, $messages, $attributes);

        $this->setParam("task.{$this->task}.validator", [
            'passes' => intval($validator->passes()),
            'fails' => intval($validator->fails()),
        ]);

        $this->validator = $validator;
    }

    protected function createTransactionHistory(): void
    {
        $attributes = $this->getTaskConfig('transaction_history.attributes');
        if (empty($attributes)){
            return;
        }

        $history = new \WHMCS\Billing\Payment\Transaction\History();

        $history->mergeFillable(array_keys($attributes));
        $history->fill($attributes);
        $history->save();
    }

    protected function makeResult(): void
    {
        $result = [
            'invoice.id' => $this->getParam('invoiceid', 'NotAvailable'),
            'client.id' => $this->getParam('clientdetails.id', 'NotAvailable'),
            'transaction.id' => $this->getTransactionId(),
            'context' => Str::title($this->context),
            'task' => Str::title($this->task),
            'status' => $this->getTaskStatus(),
            'timestamp' => $this->timestamp->toIso8601ZuluString(),
        ];

        if (isset($this->validator)) {
            $result = array_merge($result, [
                'validation' => [
                    'successful' => intval($this->validator->passes()),
                    'failed' => intval($this->validator->fails()),
                    'errors' => ($this->validator->fails() ? $this->validator->messages()->toArray() : []),
                    'data' => $this->validator->getData(),
                    'rules' => $this->validator->getRules(),
                ]
            ]);
        }

        if ($this->getTaskType() == 'posnetRequest' && isset($this->xml)){
            $result = array_merge($result, [
                'request' => [
                    'body' => [
                        'raw' => Str::squish($this->xml->asXML()),
                        'compiled' => Arr::fromXml($this->xml->asXML()),
                    ],
                ],
            ]);
        }

        if ($this->getTaskType() == 'posnetRequest' && isset($this->response)){
            $result = array_merge($result, [
                'response' => [
                    'statusCode' => $this->response->getStatusCode(),
                    'reasonPhrase' => $this->response->getReasonPhrase(),
                    'body' => [
                        'raw' => mb_convert_encoding($this->response->getBody()->__toString(), 'UTF-8', 'ISO-8859-9'),
                        'compiled' => $this->getXmlResponse(),
                    ],
                    'headers' => $this->response->getHeaders(),
                ],
            ]);
        }

        if ($this->getTaskType() == 'posnetRequest' && isset($this->stats)){
            $result = array_merge($result, [
                'hasResponse' => intval($this->stats->hasResponse()),
                'stats' => $this->stats->getHandlerStats(),
            ]);
        }

        if ($this->getTaskType() == 'posnetRequest' && isset($this->http)){
            $result = array_merge($result, [
                'http' => [
                    'config' => Arr::except($this->http->getConfig(), ['handler']),
                ],
            ]);
        }

        $this->result = $result;
    }
}