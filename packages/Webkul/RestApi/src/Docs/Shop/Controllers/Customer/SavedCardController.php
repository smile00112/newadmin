<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class SavedCardController
{
    /**
     * @OA\Post(
     *      path="/api/v1/customer/saved-cards",
     *      operationId="createCustomerSavedCard",
     *      tags={"Customers"},
     *      summary="Create or update customer's saved card from SDK",
     *      description="Creates or updates a customer's saved payment card using data provided by a mobile SDK after successful binding with Alfa-Bank.",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"binding_id","card_mask"},
     *              @OA\Property(
     *                  property="binding_id",
     *                  type="string",
     *                  description="Binding identifier returned by Alfa-Bank.",
     *                  example="f3e9c0b1-1234-5678-9abc-def012345678"
     *              ),
     *              @OA\Property(
     *                  property="card_mask",
     *                  type="string",
     *                  description="Masked PAN of the card.",
     *                  example="4111 11** **** 1111"
     *              ),
     *              @OA\Property(
     *                  property="card_type",
     *                  type="string",
     *                  nullable=true,
     *                  description="Payment system / card brand (e.g. VISA, MASTERCARD).",
     *                  example="VISA"
     *              ),
     *              @OA\Property(
     *                  property="client_id",
     *                  type="string",
     *                  nullable=true,
     *                  description="Client identifier used when binding the card in Alfa-Bank.",
     *                  example="9c0b1f3e-5678-1234-9abc-def012345678"
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Saved card created or updated successfully.",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/SavedCard"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation error"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store() {}

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
