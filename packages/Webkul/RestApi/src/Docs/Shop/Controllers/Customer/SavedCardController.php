<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class SavedCardController
{
    /**
     * @OA\Get(
     *      path="/api/v1/customer/saved-cards",
     *      operationId="getCustomerSavedCards",
     *      tags={"Saved Cards"},
     *      summary="Get customer's saved cards",
     *      description="Returns customer's saved payment cards from Alfabank, sorted by id in descending order",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(ref="#/components/schemas/SavedCard")
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function index() {}
}
