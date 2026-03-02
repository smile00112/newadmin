<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class SavedCardController
{
    /**
     * @OA\Get(
     *      path="/api/v1/customer/saved-cards",
     *      operationId="getCustomerSavedCards",
     *      tags={"Customers"},
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

    /**
     * @OA\Delete(
     *      path="/api/v1/customer/saved-cards/{id}",
     *      operationId="deleteCustomerSavedCard",
     *      tags={"Customers"},
     *      summary="Delete customer's saved card",
     *      description="Unbinds the card at Alfa-Bank and removes it from the saved cards list",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Saved card ID",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Card removed successfully."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Saved card not found",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Saved card not found."
     *              )
     *          )
     *      )
     * )
     */
    public function destroy() {}
}
