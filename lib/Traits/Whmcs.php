<?php

namespace WHMCS\Module\Gateway\YapiKredi\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Whmcs
{
    protected static function standardiseTransactionHistory(?\WHMCS\Billing\Payment\Transaction\History $model): array
    {
        if (! $model){
            return [];
        }

        $model = $model->toArray();
        // Too long array index
        $info = Arr::pull($model, 'additional_information');
        $info = array_merge($info, ['metadata' => Arr::undot(Arr::get($info, 'metadata', []))]);

        return array_merge($model, ['info' => $info]);
    }
    protected static function getTransactionHistory(array $where): array
    {
        return static::standardiseTransactionHistory(\WHMCS\Billing\Payment\Transaction\History::where($where)->first());
    }
    public static function getTransactionInformationByParamsViaHistory(array $params): \WHMCS\Billing\Payment\Transaction\Information
    {
        static::init();

        $transactionId = Arr::get($params, 'transactionId');

        $hist = static::getTransactionHistory([
            'gateway' => static::getIdentifier(),
            'transaction_id' => Arr::get($params, 'transactionId'),
        ]);

        $info = (new \WHMCS\Billing\Payment\Transaction\Information())->setTransactionId($transactionId);

        if (empty($hist)){
            $info->setAdditionalDatum('moduleError','Related transaction history not found!');
            return $info;
        }

        $replacer = static::makeReplacer(['hist' => $hist]);
        $data = $replacer->apply(static::getConfig('module.transaction_information'));

        foreach($data as $methodName => $parameters){
            if (Str::lower($methodName) == 'transactionid'){
                continue;
            }

            $method = Str::start($methodName, 'set');

            if (!method_exists($info, $method)){
                continue;
            }

            if (in_array($methodName, ['Created','AvailableOn'])){
                try{
                    $parameters = \WHMCS\Carbon::parse($parameters);
                }catch (\Exception $e){
                    continue;
                }

                if ($method == 'AvailableOn'){
                    $parameters->setTimezone('UTC');
                }
            }elseif ($methodName === 'AdditionalDatum' && is_array($parameters)){
                foreach($parameters as $datum){
                    if (($datum[0] ?? '') === 'adminInfo' && ! Arr::has($hist, 'info.process.admin')){
                        continue;
                    }
                    $info->{$method}(...$datum);
                }
                continue;
            }

            $parameters = Arr::wrap($parameters);

            $info->{$method}(...$parameters);
        }

        return $info;
    }
}