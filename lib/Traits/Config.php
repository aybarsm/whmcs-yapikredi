<?php

namespace WHMCS\Module\Gateway\YapiKredi\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Config
{
    protected static array $config = [];

    protected static function getConfig(string $key, $default = null)
    {
        $base = Str::of($key)->lower()->before('.')->__toString();

        // Cache only when needed.
        if (! Arr::exists(self::$config, $base) && file_exists($configFilePath = static::getPath("config/{$base}.php"))){
            $config = include $configFilePath;

            // Since dotenv not exists, take the ancient way
            if ($base == 'module' && file_exists($devConfigFilePath = static::getPath('config/module.dev.php'))){
                $devConfig = include $devConfigFilePath;
                $config = array_replace_recursive($config, $devConfig);
            }elseif ($base === 'gw' && Arr::get($config, 'TEST_MODE', 'NO') === 'YES'){
                $dependentKeys = array_values(static::getConfig('module.bank.test_dependent_settings'));
                $testVars = Arr::only($config, array_map(fn ($val) => Str::start($val, 'TEST_'), $dependentKeys));
                $testVars = array_combine(array_map(fn ($val) => Str::replaceFirst('TEST_', '', $val), array_keys($testVars)), array_values($testVars));
                $config = Arr::except(array_merge($config, $testVars), ['type', 'visible']);
            }

            static::$config[$base] = $config;
        }

        return Arr::get(static::$config, $key, $default);
    }
    protected static function hasConfig($keys): bool
    {
        return Arr::has(static::$config, $keys);
    }
}