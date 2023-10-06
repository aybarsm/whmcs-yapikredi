<?php

return [
    'tasks' => [
        'init' => [
            'type' => 'internalCheck',
            'failed' => [
                'callbacks' => [
//                    fn ($context) => localAPI('SendAdminEmail', ['messagename' => 'Credit Card Refund Failed', 'mergefields' => ['params' => $context->params, 'result' => $context->result]]),
                ],
                'terminate' => fn ($context): array => ['status' => 'error', 'rawdata' => $context->result],
            ],
            'successful' => [
                'callbacks' => [
//                    fn ($context) => logTransaction($context->getIdentifier(), $context->result, $context->getLogResultString()),
                ],
            ],
            'transaction_history' => [
                'query' => [
                    'transaction_id' => '{{ PROCESS_PAYMENT_TRANSACTION_ID }}',
                    'remote_status' => 'Payment',
                    'completed' => 1,
                ]
            ],
            'data' => fn ($context): array => $context->getTransactionHistory($context->getTaskConfig('transaction_history.query')),
            'validation' => [
                'rules' => [
                    'id' => 'required|integer',
                    'invoice_id' => 'required|integer|size:{{ INVOICE_ID }}',
                    'gateway' => 'required|string|exactly:{{ GW_IDENTIFIER }}',
                    'info.module' => 'required|array',
                    'info.module.timestamp' => 'required|array',
                    'info.module.timestamp.iso8601zulu' => 'required|string',
                    'info.bank.transaction' => 'required|array',
                    'info.bank.transaction.type' => 'required|string',
                    'info.bank.data' => 'required|array',
                    'info.bank.data.xid' => 'required|string',
                ],
                'messages' => [
                    'id.required' => 'Payment transaction history not found.',
                ]
            ],
        ],
        'exec' => [
            'type' => 'posnetRequest',
            'failed' => [
                'callbacks' => [
//                    fn ($context) => localAPI('SendAdminEmail', ['messagename' => 'Credit Card Refund Failed', 'mergefields' => ['params' => $context->params, 'result' => $context->result]]),
                ],
                'terminate' => fn ($context): array => ['status' => 'error', 'rawdata' => $context->result],
            ],
            'successful' => [
                'callbacks' => [
                    fn ($context) => $context->createTransactionHistory(),
                ],
                'terminate' => fn ($context): array => [
                    'status' => 'success',
                    'rawdata' => $context->result,
                    'transid' => $context->getTransactionId(),
                    'fees' => $context->getProcessFee($context->getParam('amount'), $context->getParam('process.refund.type') === 'Full'),
                ],
            ],
            'data_reverse' => [
                'reverse' => [
                    'transaction' => '{{ Str::lower|CONTEXT_HIST_INFO_BANK_TRANSACTION_TYPE }}',
                    'hostLogKey' => '{{ WhmcsService::decryptPassword|CONTEXT_HIST_INFO_BANK_DATA_HOSTLOGKEY }}',
                ]
            ],
            'data_return' => [
                'tranDateRequired' => '1',
                'return' => [
                    'amount' => '{{ BANK_PROCESS_AMOUNT }}',
                    'currencyCode' => '{{ Str::upper|CONTEXT_HIST_INFO_BANK_PROCESS_CURRENCY_CODE }}',
                    'orderID' => 'TDS_{{ CONTEXT_HIST_INFO_BANK_DATA_XID }}',
                    'orderDate' => '{{ Posnet::getBankOrderDateByTimestamp|CONTEXT_HIST_INFO_MODULE_TIMESTAMP_ISO8601ZULU }}',
                ]
            ],
            'data' => fn ($context): array => $context->getTaskConfig("data_{$context->getParam('process.refund.bank.type')}", [], true, 'data'),
            'validation' => [
                'rules' => [
                    'hostlogkey' => 'required_if:approved,1',
                    'authCode' => 'required_if:approved,1',
                ],
            ],
            'transaction_history' => [
                'attributes' => [
                    'completed' => 1,
                    'description' => 'Refund Completed as {{ Str::studly|PROCESS_REFUND_BANK_TYPE }} ({{ PROCESS_REFUND_TYPE }})',
                    'additional_information' => [
                        'metadata' => [
                            'bank.data.hostlogkey' => 'passwordEncrypted',
                            'bank.data.authcode' => 'passwordEncrypted',
                        ],
                        'process' => [
                            'payment' => [
                                'transaction_history_id' => '{{ CONTEXT_HIST_ID }}',
                            ],
                            'admin' => [
                                'id' => '{{ ADMIN_ID }}',
                                'username' => '{{ ADMIN_USERNAME }}',
                                'fullname' => '{{ ADMIN_FULLNAME }}',
                                'ip' => '{{ ADMIN_IP }}',
                            ],
                        ],
                        'bank' => [
                            'transaction' => [
                                'type' => '{{ CONTEXT_HIST_INFO_BANK_TRANSACTION_TYPE }}',
                            ],
                            'data' => [
                                'xid' => '{{ BANK_XID }}',
                                'hostlogkey' => '{{ WhmcsService::encryptPassword,strval|TASK_EXEC_XML_RESPONSE_HOSTLOGKEY }}',
                                'authcode' => '{{ WhmcsService::encryptPassword,strval|TASK_EXEC_XML_RESPONSE_AUTHCODE }}',
                            ]
                        ]
                    ],
                ],
            ]
        ],
    ],
];