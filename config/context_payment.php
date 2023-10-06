<?php

use Aybarsm\Whmcs\Service\Whmcs as WhmcsService;

return [
    'tasks' => [
        '3ds' => [
            'type' => 'posnetRequest',
            'failed' => [
                'callbacks' => [
                    fn ($context) => logTransaction($context->getIdentifier(), $context->result, $context->getLogResultString()),
//                    fn ($context) => localAPI('SendEmail', ['messagename' => 'Credit Card Payment Failed', 'id' => $context->getParam('invoiceid'), 'customtype' => 'invoice', 'customvars' => base64_encode(serialize($context->params))]),
//                    fn ($context) => localAPI('SendAdminEmail', ['messagename' => 'Credit Card Payment Failed', 'mergefields' => ['params' => $context->params, 'result' => $context->result]]),
                ],
                'terminate' => fn ($context) => callback3DSecureRedirect($context->getParam('invoiceid'), $context->validator->passes()),
            ],
            'successful' => [
                'callbacks' => [
//                    fn ($context) => logTransaction($context->getIdentifier(), $context->result, $context->getLogResultString()),
                    fn ($context) => $context->setParam('task.3ds.data.3DForm', $context->makeHtmlForm($context->getTaskConfig('html_form'))),
                ],
                'terminate' => fn ($context) => $context->getParam('task.3ds.data.3DForm'),
            ],
            'data' => [
                'oosRequestData' => [
                    'posnetid' => '{{ GW_POSNET_ID }}',
                    'XID' => '{{ BANK_XID }}',
                    'amount' => '{{ BANK_PROCESS_AMOUNT }}',
                    'currencyCode' => '{{ BANK_PROCESS_CURRENCY_CODE }}',
                    'installment' => '00',
                    'tranType' => '{{ GW_TRANSACTION_TYPE }}',
                    'cardHolderName' => '{{ CLIENT_NAME_FULL }}',
                    'ccno' => '{{ PAYMENT_CARD_NUMBER }}',
                    'expDate' => '{{ BANK_PAYMENT_CARD_EXPDATE }}',
                    'cvc' => '{{ PAYMENT_CARD_CCV }}',
                ],
            ],
            'validation' => [
                'rules' => [
                    'oosRequestDataResponse' => 'required_if:approved,1|array',
                    'oosRequestDataResponse.data1' => 'required_if:approved,1|string',
                    'oosRequestDataResponse.data2' => 'required_if:approved,1|string',
                    'oosRequestDataResponse.sign' => 'required_if:approved,1|string',
                ]
            ],
            'html_form' => [
                'inputs' => [
                    'hidden:mid' => '{{ GW_MERCHANT_ID }}',
                    'hidden:posnetID' => '{{ GW_POSNET_ID }}',
                    'hidden:posnetData' => '{{ TASK_3DS_XML_RESPONSE_OOSREQUESTDATARESPONSE_DATA1 }}',
                    'hidden:posnetData2' => '{{ TASK_3DS_XML_RESPONSE_OOSREQUESTDATARESPONSE_DATA2 }}',
                    'hidden:digest' => '{{ TASK_3DS_XML_RESPONSE_OOSREQUESTDATARESPONSE_SIGN }}',
                    'hidden:merchantReturnURL' => '{{ GW_MERCHANT_RETURN_URL }}',
                    'hidden:lang' => '{{ BANK_LANG }}',
                    'hidden:url' => '{{ INVOICE_RETURNURL }}',
                    'hidden:openANewWindow' => '0',
                    'submit:payNow' => '{{ LANG_PAYNOW }}',
                ],
            ],
        ],
        'init' => [
            'type' => 'inboundRequest',
            'failed' => [
                'callbacks' => [
                    fn ($context) => logTransaction($context->getIdentifier(), $context->result, $context->getLogResultString()),
//                    fn ($context) => localAPI('SendEmail', ['messagename' => 'Credit Card Payment Failed', 'id' => $context->getParam('invoiceid'), 'customtype' => 'invoice', 'customvars' => base64_encode(serialize($context->params))]),
//                    fn ($context) => localAPI('SendAdminEmail', ['messagename' => 'Credit Card Payment Failed', 'mergefields' => ['params' => $context->params, 'result' => $context->result]]),
                ],
                'terminate' => fn () => WhmcsService::redirectSystemUrl(),
            ],
            'successful' => [
                'callbacks' => [
//                    fn ($context) => logTransaction($context->getIdentifier(), $context->result, $context->getLogResultString()),
                ],
            ],
            'data' => [
                'Xid' => '{{ TASK_INIT_POST_XID }}',
                'Amount' => '{{ TASK_INIT_POST_AMOUNT }}',
                'TranType' => '{{ TASK_INIT_POST_TRANTYPE }}',
                'MerchantPacket' => '{{ TASK_INIT_POST_MERCHANTPACKET }}',
                'BankPacket' => '{{ TASK_INIT_POST_BANKPACKET }}',
                'CCPrefix' => '{{ TASK_INIT_POST_CCPREFIX }}',
                'MerchantId' => '{{ TASK_INIT_POST_MERCHANTID }}',
                'Sign' => '{{ TASK_INIT_POST_SIGN }}',
            ],
            'validation' => [
                'rules' => [
                    'Xid' => 'required|string|size:20|starts_with:{{ GW_XID_PREFIX }}|valid_xid',
                    'TranType' => 'required|string|exactly:{{ GW_TRANSACTION_TYPE }}',
                    'MerchantPacket' => 'required|string',
                    'BankPacket' => 'required|string',
                    'MerchantId' => 'required|integer|digits:10|size:{{ GW_MERCHANT_ID }}',
                    'Sign' => 'required|string',
                ]
            ],
        ],
        'confirm' => [
            'type' => 'posnetRequest',
            'failed' => [
                'callbacks' => [
                    fn ($context) => logTransaction($context->getIdentifier(), $context->result, $context->getLogResultString()),
//                    fn ($context) => localAPI('SendEmail', ['messagename' => 'Credit Card Payment Failed', 'id' => $context->getParam('invoiceid'), 'customtype' => 'invoice', 'customvars' => base64_encode(serialize($context->params))]),
//                    fn ($context) => localAPI('SendAdminEmail', ['messagename' => 'Credit Card Payment Failed', 'mergefields' => ['params' => $context->params, 'result' => $context->result]]),
                ],
                'terminate' => fn ($context) => callback3DSecureRedirect($context->getParam('invoiceid'), $context->validator->passes()),
            ],
            'successful' => [
                'callbacks' => [
//                    fn ($context) => logTransaction($context->getIdentifier(), $context->result, $context->getLogResultString()),
                ],
            ],
            'hmac' => [
                'request' => '{{ BANK_XID }};{{ BANK_PROCESS_AMOUNT }};{{ BANK_PROCESS_CURRENCY_CODE }};{{ GW_MERCHANT_ID }};{{ BANK_HMAC_STATIC }}',
                'response' => '{{ TASK_CONFIRM_XML_RESPONSE_OOSRESOLVEMERCHANTDATARESPONSE_MDSTATUS }};{{ BANK_XID }};{{ BANK_PROCESS_AMOUNT }};{{ BANK_PROCESS_CURRENCY_CODE }};{{ GW_MERCHANT_ID }};{{ BANK_HMAC_STATIC }}',
            ],
            'data' => [
                'oosResolveMerchantData' => [
                    'bankData' => '{{ TASK_INIT_POST_BANKPACKET }}',
                    'merchantData' => '{{ TASK_INIT_POST_MERCHANTPACKET }}',
                    'sign' => '{{ TASK_INIT_POST_SIGN }}',
                    'mac' => '{{ TASK_CONFIRM_HMAC_REQUEST }}',
                ]
            ],
            'validation' => [
                'rules' => [
                    'oosResolveMerchantDataResponse' => 'required_if:approved,1|array',
                    'oosResolveMerchantDataResponse.xid' => 'required_if:approved,1|string|exactly:{{ BANK_XID }}',
                    'oosResolveMerchantDataResponse.amount' => 'required_if:approved,1|integer|size:{{ BANK_PROCESS_AMOUNT }}',
                    'oosResolveMerchantDataResponse.currency' => 'required_if:approved,1|string|exactly:{{ BANK_PROCESS_CURRENCY_CODE }}',
                    'oosResolveMerchantDataResponse.txStatus' => 'required_if:approved,1|string|exactly:Y',
                    'oosResolveMerchantDataResponse.mdStatus' => 'required_if:approved,1|integer|size:1',
                    'oosResolveMerchantDataResponse.mdErrorMessage' => 'required_if:approved,1|string',
                    'oosResolveMerchantDataResponse.mac' => 'required_if:approved,1|string|valid_mac:{{ TASK_CONFIRM_HMAC_RESPONSE }}',
                ],
            ],
        ],
        'finalise' => [
            'type' => 'posnetRequest',
            'failed' => [
                'callbacks' => [
                    fn ($context) => logTransaction($context->getIdentifier(), $context->result, $context->getLogResultString()),
//                    fn ($context) => localAPI('SendEmail', ['messagename' => 'Credit Card Payment Failed', 'id' => $context->getParam('invoiceid'), 'customtype' => 'invoice', 'customvars' => base64_encode(serialize($context->params))]),
//                    fn ($context) => localAPI('SendAdminEmail', ['messagename' => 'Credit Card Payment Failed', 'mergefields' => ['params' => $context->params, 'result' => $context->result]]),
                ],
                'terminate' => fn ($context) => callback3DSecureRedirect($context->getParam('invoiceid'), $context->validator->passes()),
            ],
            'successful' => [
                'callbacks' => [
//                    fn ($context) => logTransaction($context->getIdentifier(), $context->result, $context->getLogResultString()),
                    fn ($context) => addInvoicePayment(
                        $context->getParam('invoiceid'),
                        $context->getTransactionId(),
                        floatval($context->getParam('amount')),
                        $context->getProcessFee($context->getParam('amount')),
                        $context->getIdentifier()
                    ),
                    fn ($context) => $context->createTransactionHistory(),
//                    fn ($context) => localAPI('SendEmail', ['messagename' => 'Credit Card Payment Confirmation', 'id' => $context->getParam('invoiceid'), 'customtype' => 'invoice', 'customvars' => base64_encode(serialize($context->params))]),
                ],
                'terminate' => fn ($context) => callback3DSecureRedirect($context->getParam('invoiceid'), $context->validator->passes()),
            ],
            'hmac' => [
                'response' => '{{ TASK_FINALISE_XML_RESPONSE_HOSTLOGKEY }};{{ BANK_XID }};{{ BANK_PROCESS_AMOUNT }};{{ BANK_PROCESS_CURRENCY_CODE }};{{ GW_MERCHANT_ID }};{{ BANK_HMAC_STATIC }}',
            ],
            'data' => [
                'oosTranData' => [
                    'bankData' => '{{ TASK_INIT_POST_BANKPACKET }}',
                    'wpAmount' => '0',
                    'mac' => '{{ TASK_CONFIRM_HMAC_REQUEST }}',
                ]
            ],
            'validation' => [
                'rules' => [
                    'mac' => 'required_if:approved,1|string|valid_mac:{{ TASK_FINALISE_HMAC_RESPONSE }}',
                    'hostlogkey' => 'required_if:approved,1',
                    'authCode' => 'required_if:approved,1',
                ]
            ],
            'transaction_history' => [
                'attributes' => [
                    'completed' => 1,
                    'description' => 'Payment Completed as {{ Str::studly|TASK_INIT_POST_TRANTYPE }}',
                    'additional_information' => [
                        'process' => [
                            'payment' => [
                                'available_at' => '{{ PROCESS_PAYMENT_AVAILABLE_AT }}',
                            ]
                        ],
                        'metadata' => [
                            'bank.data.hostlogkey' => 'passwordEncrypted',
                            'bank.data.authcode' => 'passwordEncrypted',
                        ],
                        'bank' => [
                            'transaction' => [
                                'type' => '{{ TASK_INIT_POST_TRANTYPE }}',
                            ],
                            'data' => [
                                'xid' => '{{ BANK_XID }}',
                                'hostlogkey' => '{{ WhmcsService::encryptPassword,strval|TASK_FINALISE_XML_RESPONSE_HOSTLOGKEY }}',
                                'authcode' => '{{ WhmcsService::encryptPassword,strval|TASK_FINALISE_XML_RESPONSE_AUTHCODE }}',
                            ]
                        ],
                    ],
                ]
            ],
        ],
    ],
];