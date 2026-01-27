<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\JsonResponse;

class BonusController extends CustomerController
{
    /**
     * Returns bonus information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = [
            'points_balance' => 450,
            'spent_sum' => 0,
            'percent_max' => 10,
            'show_levels_info' => true,
            'level' => 2,
            'type' => 'orderscount',
            'levels_info' => [
                [
                    'id' => 1,
                    'name' => '1 грейд',
                    'cashback_percent' => 2,
                    'description_top' => 'Возвращаем 2% заказа.',
                    'description_bottom' => '0-10 заказов',
                ],
                [
                    'id' => 2,
                    'level_up_name' => '3 грейд',
                    'cashback_percent' => 5,
                    'description_top' => 'Возвращаем 5% заказа.',
                    'description_bottom' => '10-15 заказов',
                ],
                [
                    'id' => 3,
                    'level_up_name' => '3 грейд',
                    'rule_to_level' => 8,
                    'cashback_percent' => 2,
                    'description_top' => 'Возвращаем 8% заказа.',
                    'description_bottom' => '15-20 заказов',
                ],
                [
                    'id' => 4,
                    'level_up_name' => '4 грейд',
                    'rule_to_level' => 10,
                    'cashback_percent' => 2,
                    'description_top' => 'Возвращаем 10% заказа.',
                    'description_bottom' => '20-25 заказов',
                ],
                [
                    'id' => 5,
                    'level_up_name' => '5 грейд',
                    'rule_to_level' => 12,
                    'cashback_percent' => 2,
                    'description_top' => 'Возвращаем 12% заказа.',
                    'description_bottom' => '25+',
                ],
            ],
            'next_level_info' => [
                'text1' => 'Вы сделалли 12 заказов',
                'text2' => 'Осталось 2 закзаза до  до повышения',
            ],
            'bonus_history' => [],
        ];

        return response()->json($data);
    }
}
