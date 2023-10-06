<?php

use WHMCS\Module\Gateway\YapiKredi\Posnet;

return [
    'replacer' => [
        'leftDelimiter' => '{{',
        'rightDelimiter' => '}}',
        'modifierDelimiter' => '|',
        'keys' => [
            'pattern' => '/[^a-zA-Z0-9]/',
            'replace' => '_',
        ],
        'modifier_class_map' => [
            'Posnet' => \WHMCS\Module\Gateway\YapiKredi\Posnet::class,
            'WhmcsService' => \Aybarsm\Whmcs\Service\Whmcs::class,
            'Str' => \Illuminate\Support\Str::class,
        ],
        // Static replacements can be defined here. Anything with dot notation will be converted to snake case
        // Anything declared can be overwritten to avoid module's fundamental replacements
        // {{ }} Replacement variables will not be used during compiling
        // Replacement key => value structure. Can be nested, it will be converted to dot notation
        'static_replacements' => [
            'gw.identifier' => Posnet::getIdentifier(),
        ],
        'whmcs_params_map' => [
            'invoiceid' => 'invoice.id',
            'amount' => 'process.amount',
            'currency' => 'process.currency.code',
            'currencyId' => 'process.currency.id',
            'returnurl' => 'invoice.returnUrl',
            'langpaynow' => 'lang.payNow',
            'clientdetails.id' => 'client.id',
            'clientdetails.fullname' => 'client.name.full',
            'clientdetails.email' => 'client.email',
            'clientdetails.countrycode' => 'client.country.code',
            'clientdetails.language' => 'client.lang',
            'cardnum' => 'payment.card.number',
            'cccvv' => 'payment.card.ccv',
            'cardexp' => 'payment.cc.expDate',
        ],
    ],
    'bank' => [
        'test_dependent_settings' => ['ENCKEY', 'OOS_TDS_SERVICE_URL', 'XML_SERVICE_URL', 'XID_PREFIX'],
        'xid' => function ($context): string {
            $potentialXid = $context->getSetting('XID_PREFIX') . $context->getParam('invoiceid');
            return $context->getSetting('XID_PREFIX') . str_repeat('0', 20 - strlen($potentialXid)) . $context->getParam('invoiceid');
        },
        'hmac_static' => '{{ GW_ENCKEY }};{{ GW_TERMINAL_ID }}',
        'correlation_id' => '{{ INVOICE_ID }}|{{ Str::title|MODULE_CONTEXT }}|{{ Str::title|MODULE_TASK }}|{{ MODULE_TIMESTAMP_SAFE_ISO8601ZULU }}',
    ],
    'validation' => [
        'lang' => 'en',
    ],
    'whmcs' => [
        'admin_lang' => 'english',
        'transaction_id' => '{{ BANK_XID }}|{{ Str::title|MODULE_CONTEXT }}|{{ MODULE_TIMESTAMP_ISO8601ZULU }}',
    ],
    'transaction_information' => [
        'TransactionId' => '{{ HIST_TRANSACTION_ID }}',
        'Amount' => '{{ HIST_AMOUNT }}',
        'Currency' => '{{ HIST_INFO_PROCESS_CURRENCY_CODE }}',
        'Type' => '{{ HIST_INFO_BANK_TRANSACTION_TYPE }}',
        'AvailableOn' => '{{ HIST_INFO_PROCESS_PAYMENT_AVAILABLE_AT }}',
        'Created' => '{{ HIST_CREATED_AT }}',
        'Description' => '{{ HIST_DESCRIPTION }}',
        'Fee' => '{{ HIST_INFO_PROCESS_FEE_FULL }}',
        'Status' => '{{ HIST_REMOTE_STATUS }}',
        'AdditionalDatum' => [
            ['moduleInfo', '<b>Context:</b> {{ Str::title|HIST_INFO_MODULE_CONTEXT }}<br><b>Task:</b> {{ Str::title|HIST_INFO_MODULE_TASK }}<br><b>Timestamp:</b> {{ HIST_INFO_MODULE_TIMESTAMP_ISO8601ZULU }} (UTC)'],
            ['bankInfo', '<b>Currency Code:</b> {{ Str::upper|HIST_INFO_BANK_PROCESS_CURRENCY_CODE }}<br><b>Payment Blockage:</b> {{ HIST_INFO_BANK_BLOCKED_DAYS }} Days<br><b>Process Fee (Static):</b> {{ HIST_INFO_BANK_FEE_STATIC }} {{ HIST_INFO_PROCESS_CURRENCY_CODE }}<br><b>Process Fee (Rate):</b> {{ HIST_INFO_BANK_FEE_RATE }}%'],
            ['bankData', '<b>HostLogKey:</b> {{ WhmcsService::decryptPassword|HIST_INFO_BANK_DATA_HOSTLOGKEY }} (Decrypted)<br><b>AuthCode:</b> {{ WhmcsService::decryptPassword|HIST_INFO_BANK_DATA_AUTHCODE }} (Decrypted)'],
            ['adminInfo', '<b>ID:</b> {{ HIST_INFO_PROCESS_ADMIN_ID }}<br><b>Username:</b> {{ HIST_INFO_PROCESS_ADMIN_USERNAME }}<br><b>Full Name:</b> {{ HIST_INFO_PROCESS_ADMIN_FULLNAME }}<br><b>IP:</b> {{ HIST_INFO_PROCESS_ADMIN_IP }}'],
        ],
    ],
    'log_result_exclude' => [
        'password' => [
            'result.request.body.oosRequestData.ccno', 'result.request.body.oosRequestData.ccno', 'result.request.body.oosRequestData.ccno',
        ],
        'aes' => [
            'result.request.body', 'result.request.compiled',
        ],
    ],
    'no_iframe_script' => "<script>$(document).ready((function(){window.noAutoSubmit=1,jQuery('form#YKB3DS').attr('target','_top').submit()}));</script>",
    'tasks' => [
        'all' => [
            'transaction_history' => [
                'query' => [
                    'invoice_id' => '{{ INVOICE_ID }}',
                    'gateway' => '{{ GW_IDENTIFIER }}',
                    'remote_status' => '{{ Str::title|MODULE_CONTEXT }}',
                ],
                'attributes' => [
                    'invoice_id' => '{{ INVOICE_ID }}',
                    'gateway' => '{{ GW_IDENTIFIER }}',
                    'remote_status' => '{{ Str::title|MODULE_CONTEXT }}',
                    'transaction_id' => '{{ TRANSACTION_ID }}',
                    'amount' => '{{ PROCESS_AMOUNT }}',
                    'currency_id' => '{{ PROCESS_CURRENCY_ID }}',
                    'additional_information' => [
                        'timestamp' => '{{ intval|MODULE_TIMESTAMP }}',
                        'module' => [
                            'context' => '{{ Str::lower|MODULE_CONTEXT }}',
                            'task' => '{{ Str::lower|MODULE_TASK }}',
                            'timestamp' => [
                                'iso8601zulu' => '{{ MODULE_TIMESTAMP_ISO8601ZULU }}',
                            ]
                        ],
                        'process' => [
                            'amount' => '{{ PROCESS_AMOUNT }}',
                            'fee' => [
                                'full' => '{{ PROCESS_FEE_FULL }}',
                                'rated' => '{{ PROCESS_FEE_RATED }}',
                            ],
                            'currency' => [
                                'id' => '{{ PROCESS_CURRENCY_ID }}',
                                'code' => '{{ PROCESS_CURRENCY_CODE }}',
                            ],
                        ],
                        'bank' => [
                            'blocked_days' => '{{ GW_PAYMENT_BLOCKED_DAYS }}',
                            'process' => [
                                'amount' => '{{ BANK_PROCESS_AMOUNT }}',
                                'currency' => [
                                    'code' => '{{ BANK_PROCESS_CURRENCY_CODE }}',
                                ],
                            ],
                            'fee' => [
                                'static' => '{{ GW_PROCESS_FEE_STATIC }}',
                                'rate' => '{{ GW_PROCESS_FEE_RATE }}',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'posnetRequest' => [
            'data' => [
                'mid' => '{{ GW_MERCHANT_ID }}',
                'tid' => '{{ GW_TERMINAL_ID }}',
            ],
            'http_options' => [
                'http_errors' => false,
                'base_uri' => '{{ XML_SERVICE_URL }}',
                'query' => [
                    'xmldata' => '{{ BANK_XML_DATA_STRING }}',
                ],
                'headers' => [
                    // Bank asks for form-url-encoded POST:
                    // <%XML_SERVICE_URL%>e Content-Type=application/x-www-form-urlencoded ile POST edilir.
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
                    /*
                     * Bank asks the following headers present in the requests:
                     * Yapılacak servis entegrasyonunda her Request Header’ına X-MERCHANT-ID, X-TERMINAL-ID,
                     * X-POSNET-ID, X-CORRELATION-ID bilgileri eklenmelidir.
                     */
                    'X-MERCHANT-ID' => '{{ GW_MERCHANT_ID }}',
                    'X-TERMINAL-ID' => '{{ GW_TERMINAL_ID }}',
                    'X-POSNET-ID' => '{{ GW_POSNET_ID }}',
                    'X-CORRELATION-ID' => '{{ BANK_CORRELATION_ID }}',
                    'User-Agent' => '{{ GW_REQUEST_AGENT }}',
                ],
            ],
            'validation' => [
                'rules' => [
                    'approved' => 'required|integer|size:1',
                    'respCode' => 'required_if:approved,0',
                    'respText' => 'required_if:approved,0',
                ],
                'messages' => [
                    'approved.size' => 'Bank error.',
                ]
            ],
            'html_form' => [
                'prefix' => '{{ MODULE_NOIFRAMESCRIPT }}',
                'form' => [
                    'name' => 'YKB3DS',
                    'id' => 'YKB3DS',
                    'method' => 'POST',
                    'action' => '{{ GW_OOS_TDS_SERVICE_URL }}',
                ],
            ],
        ],
    ],
];