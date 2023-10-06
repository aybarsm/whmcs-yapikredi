<?php

namespace WHMCS\Module\Gateway\YapiKredi\Traits\Context;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Task
{
    protected function getTaskConfigKey($key = ''): string
    {
        return trim("context_{$this->context}.tasks.{$this->task}.{$key}", '. ');
    }
    protected function setTask(string $task, array $whmcsParams = []): void
    {
        $this->validator = null;
        $this->context = Str::lower($this->context);
        $this->task = Str::lower($task);
        $this->timestamp = \Carbon\Carbon::now('UTC');

        if (! isset($this->replacer)){
            // Fundamental replacements
            $this->replacer = parent::makeReplacer(array_merge(
                Arr::dot(static::getConfig('module.replacer.static_replacements', [])),
                Arr::wrapKeys(static::getConfig('gw'), 'gw.'),
                [
                    'gw.identifier' => static::getIdentifier(),
                ],
            ));
        }

        // Renew task dependent replacements
        $this->replacer->addReplacements([
            'module.context' => $this->context,
            'module.task' => $this->task,
            'module.timestamp' => $this->timestamp->timestamp,
            'module.timestamp.iso8601zulu' => $this->timestamp->toIso8601ZuluString(),
            'module.timestamp.safe.iso8601zulu' => $this->timestamp->format('Ymd\THis\Z'),
            'module.current.status' => $this->getTaskStatus(),
            'module.noiframescript' => parent::get3dSecureIframeScript(),
            'process.payment.available.at' => \Carbon\Carbon::parse($this->timestamp->timestamp, 'UTC')
                ->addDays(parent::getSetting('PAYMENT_BLOCKED_DAYS', 0))
                ->toIso8601ZuluString(),
        ]);

        if (! empty($whmcsParams)){
            $this->params = array_replace_recursive($this->params ?? [], $whmcsParams);
            $whmcsReplacements = [];

            foreach(parent::getConfig('module.replacer.whmcs_params_map', []) as $source => $target){
                $whmcsReplacements[$target] = $this->getParam($source, '');
            }

            $whmcsReplacements = array_merge($whmcsReplacements, [
                'process.fee.rated' => $this->getProcessFee($this->getParam('amount'), false),
                'process.fee.full' => $this->getProcessFee($this->getParam('amount')),
                'bank.hmac.static' => $this->getBankHmacString(parent::getConfig('module.bank.hmac_static')),
                'bank.xid' => parent::getBankXid($this->getParam('invoiceid')),
                'bank.process.amount' => parent::getBankProcessAmount($this->getParam('amount')),
                'bank.process.currency.code' => parent::getBankProcessCurrencyCode($this->getParam('currency')),
                'bank.lang' => parent::getBankLang($this->getParam('clientdetails.language'), $this->getParam('clientdetails.countrycode')),
                'bank.payment.card.expDate' => $this->hasParam('cardexp') ? parent::getBankCardExp($this->getParam('cardexp')) : '',
                'process.payment.transaction.id' => $this->getParam('transid', ''),
            ]);

            $this->replacer->addReplacements($whmcsReplacements);
        }

        if (isset($_SESSION['adminid'])){
            $admin = \WHMCS\User\Admin::find($_SESSION['adminid'], ['id', 'username', 'email', 'firstname', 'lastname'])->toarray();
            $admin['ip'] = \WHMCS\Utility\Environment\CurrentRequest::getIP();
            $this->setParam('admin', $admin);
        }

        // Renew dynamic dependents
        $this->replacer->addReplacements([
            'transaction.id' => $this->getTransactionId(),
            'bank.correlation.id' => $this->getBankCorrelationId(),
        ]);

        if ($this->getTaskType() == 'inboundRequest' && $_SERVER['REQUEST_METHOD'] == 'POST' && ! empty($_POST)){
            $this->replacer->addReplacements(Arr::wrapKeys(Arr::dot($_POST), "task.{$this->task}.post."));
        }

        $this->runTaskStatusCallbacks('Initialised');
    }
    protected function getTaskConfig(string $key, $default = [], bool $applyReplacers = true, string $moduleDefaultKey = '')
    {
        $taskConfig = parent::getConfig($this->getTaskConfigKey($key), $default);

        if (is_array($taskConfig)){
            $moduleDefaultKey = empty($moduleDefaultKey) ? $key : $moduleDefaultKey;
            $moduleDefaultsAllKey = trim("module.tasks.all.{$moduleDefaultKey}", '. ');
            $moduleDefaultsAll = parent::getConfig($moduleDefaultsAllKey, []);
            $moduleDefaultsTaskTypeKey = trim("module.tasks.{$this->getTaskType()}.{$moduleDefaultKey}", '. ');
            $moduleDefaultsTaskType = parent::getConfig($moduleDefaultsTaskTypeKey, []);
            $taskConfig = array_replace_recursive($moduleDefaultsAll, $moduleDefaultsTaskType, $taskConfig);
        }elseif (is_callable($taskConfig)){
            return $taskConfig($this);
        }

        return $applyReplacers ? $this->replacer->apply($taskConfig) : $taskConfig;
    }

    protected function getTaskStatus(bool $forWhmcs = false, ?\Illuminate\Validation\Validator $validator = null): string
    {
        $useValidator = $validator ?? $this->validator ?? null;
        if (! $useValidator){
            return 'Initialised';
        }

        return ($useValidator->passes() ? ($forWhmcs ? 'success' : 'Successful') : ($forWhmcs ? 'error' : 'Failed'));
    }

    protected function runTaskStatusCallbacks(string $status): void
    {
        $status = Str::lower($status);

        foreach($this->getTaskConfig("$status.callbacks", [], false) as $callback){
            if (is_callable($callback)){
                $callback($this);
            }
        }
    }

    protected function getTaskTermination(string $status)
    {
        $status = Str::lower($status);
        return $this->getTaskConfig("$status.terminate", null, false);
    }

    protected function getTaskType()
    {
        return $this->getTaskConfig('type', 'Unknown', false);
    }
}