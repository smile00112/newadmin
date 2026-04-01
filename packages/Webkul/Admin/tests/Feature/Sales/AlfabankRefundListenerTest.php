<?php

use Webkul\AlfabankPayment\Listeners\RefundOrderListener;
use Webkul\AlfabankPayment\Services\AlfabankApiService;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\RefundRepository;

afterEach(function () {
    \Mockery::close();
});

it('skips refund call for non-alfabank payments', function () {
    $apiService = \Mockery::mock(AlfabankApiService::class);
    $orderRepository = \Mockery::mock(OrderRepository::class);
    $refundRepository = \Mockery::mock(RefundRepository::class);

    $order = (object) [
        'id' => 10,
        'payment' => (object) [
            'method' => 'cashondelivery',
            'additional' => [],
        ],
        'additional' => [],
        'order_currency_code' => 'BYN',
    ];

    $orderRepository->shouldReceive('find')
        ->once()
        ->with(10)
        ->andReturn($order);

    $apiService->shouldNotReceive('refundOrder');
    $refundRepository->shouldNotReceive('getOrderItemsRefundSummary');

    $listener = new RefundOrderListener($apiService, $orderRepository, $refundRepository);

    $listener->handle([
        'order_id' => 10,
        'refund' => ['items' => []],
    ]);

    expect(true)->toBeTrue();
});

it('throws when alfabank gateway returns refund error', function () {
    $apiService = \Mockery::mock(AlfabankApiService::class);
    $orderRepository = \Mockery::mock(OrderRepository::class);
    $refundRepository = \Mockery::mock(RefundRepository::class);

    $order = (object) [
        'id' => 77,
        'payment' => (object) [
            'method' => 'alfabank',
            'additional' => ['alfabank_order_id' => 'bank-order-777'],
        ],
        'additional' => [],
        'order_currency_code' => 'BYN',
    ];

    $orderRepository->shouldReceive('find')
        ->once()
        ->with(77)
        ->andReturn($order);

    $refundRepository->shouldReceive('getOrderItemsRefundSummary')
        ->once()
        ->andReturn([
            'grand_total' => ['price' => 12.34],
        ]);

    $apiService->shouldReceive('refundOrder')
        ->once()
        ->andReturn([
            'errorCode' => '5',
            'errorMessage' => 'Gateway rejected refund',
        ]);

    $listener = new RefundOrderListener($apiService, $orderRepository, $refundRepository);

    $listener->handle([
        'order_id' => 77,
        'refund' => ['items' => [], 'shipping' => 0, 'adjustment_refund' => 0, 'adjustment_fee' => 0],
    ]);
})->throws(\RuntimeException::class, 'Alfabank refund error: Gateway rejected refund');
