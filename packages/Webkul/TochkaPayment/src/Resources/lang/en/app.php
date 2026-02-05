<?php

return [
    'admin' => [
        'menu' => [
            'tochka-payment' => 'Tochka Payment',
            'payment-history' => 'Payment History',
        ],
        'payment-history' => [
            'index' => [
                'title' => 'Tochka Payment History',
                'id' => 'ID',
                'order_id' => 'Order ID',
                'amount' => 'Amount',
                'client' => 'Client',
                'status' => 'Status',
                'created_at' => 'Created',
                'actions' => 'Actions',
                'view' => 'View',
                'empty' => 'No payments found',
            ],
            'show' => [
                'title' => 'Payment #:id',
                'payment-info' => 'Payment Information',
                'client-info' => 'Client Information',
                'webhook-info' => 'Webhook Information',
                'payment-id' => 'Payment ID',
                'order-id' => 'Order ID',
                'external-order-id' => 'External Order ID',
                'transaction-id' => 'Transaction ID',
                'amount' => 'Amount',
                'status' => 'Status',
                'created-at' => 'Created At',
                'updated-at' => 'Updated At',
                'client-name' => 'Client Name',
                'client-email' => 'Client Email',
                'client-phone' => 'Client Phone',
                'payment-url' => 'Payment URL',
                'webhook-sent' => 'Webhook Sent',
                'webhook-attempts' => 'Webhook Attempts',
                'webhook-response' => 'Webhook Response',
                'request-data' => 'Request Data',
                'callback-data' => 'Callback Data',
            ],
            'status' => [
                'pending' => 'Pending',
                'paid' => 'Paid',
                'failed' => 'Failed',
                'cancelled' => 'Cancelled',
            ],
        ],
    ],
];
