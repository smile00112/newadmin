<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class AccountController
{
    /**
     * @OA\Get(
     *      path="/api/v1/customer/get",
     *      operationId="getCustomer",
     *      tags={"Customers"},
     *      summary="Get logged in customer details",
     *      description="Get logged in customer details",
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
     *                  type="object",
     *                  ref="#/components/schemas/Customer"
     *              )
     *          )
     *       ),
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
    public function get() {}

    /**
     * @OA\Put(
     *      path="/api/v1/customer/profile",
     *      operationId="updateCustomer",
     *      tags={"Customers"},
     *      summary="Update customer profile",
     *      description="Update customer profile. All fields are optional, but at least one field must be provided. Only provided fields will be updated.",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="first_name",
     *                      type="string",
     *                      example="John",
     *                      description="Customer's first name"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      example="Doe",
     *                      description="Customer's last name"
     *                  ),
     *                  @OA\Property(
     *                      property="gender",
     *                      type="string",
     *                      enum={"Male", "Female", "Other"},
     *                      example="Male",
     *                      description="Customer's gender"
     *                  ),
     *                  @OA\Property(
     *                      property="date_of_birth",
     *                      type="string",
     *                      format="date",
     *                      example="2002-02-19",
     *                      description="Customer's date of birth (must be before today)"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      example="1234567899",
     *                      description="Customer's phone number (must be unique)"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      format="email",
     *                      example="example@example.com",
     *                      description="Customer's email address (must be unique)"
     *                  ),
     *                  @OA\Property(
     *                      property="current_password",
     *                      type="string",
     *                      format="password",
     *                      example="admin123",
     *                      description="Current password (required if changing password)"
     *                  ),
     *                  @OA\Property(
     *                      property="new_password",
     *                      type="string",
     *                      format="password",
     *                      example="admin123",
     *                      description="New password (min 6 characters, required with current_password)"
     *                  ),
     *                  @OA\Property(
     *                      property="new_password_confirmation",
     *                      type="string",
     *                      format="password",
     *                      example="admin123",
     *                      description="New password confirmation (required with new_password)"
     *                  ),
     *                  @OA\Property(
     *                      property="subscribed_to_news_letter",
     *                      type="boolean",
     *                      example=true,
     *                      description="Subscribe to newsletter"
     *                  )
     *              )
     *          ),
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="first_name",
     *                      type="string",
     *                      example="John"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      example="Doe"
     *                  ),
     *                  @OA\Property(
     *                      property="gender",
     *                      type="string",
     *                      enum={"Male", "Female", "Other"},
     *                      example="Male"
     *                  ),
     *                  @OA\Property(
     *                      property="date_of_birth",
     *                      type="string",
     *                      format="date",
     *                      example="2002-02-19"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      example="1234567899"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      format="email",
     *                      example="example@example.com"
     *                  ),
     *                  @OA\Property(
     *                      property="current_password",
     *                      type="string",
     *                      format="password",
     *                      example="admin123"
     *                  ),
     *                  @OA\Property(
     *                      property="new_password",
     *                      type="string",
     *                      format="password",
     *                      example="admin123"
     *                  ),
     *                  @OA\Property(
     *                      property="new_password_confirmation",
     *                      type="string",
     *                      format="password",
     *                      example="admin123"
     *                  ),
     *                  @OA\Property(
     *                      property="image[]",
     *                      type="string",
     *                      format="binary",
     *                      description="Customer Profile Image"
     *                  ),
     *                  @OA\Property(
     *                      property="subscribed_to_news_letter",
     *                      type="boolean"
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="Your account has been updated  successfully."),
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/Customer")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=422,
     *          description="Error: Unprocessable Content",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Examples(
     *                  example="validation_error",
     *                  value={
     *                      "message": "The given data was invalid.",
     *                      "errors": {
     *                          "email": {"The email has already been taken."}
     *                      }
     *                  },
     *                  summary="Validation error"
     *              ),
     *              @OA\Examples(
     *                  example="no_fields",
     *                  value={
     *                      "message": "At least one field must be provided for update.",
     *                      "errors": {
     *                          "general": {"At least one field must be provided for update."}
     *                      }
     *                  },
     *                  summary="No fields provided"
     *              )
     *          )
     *      )
     * )
     */
    public function update() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/logout",
     *      operationId="logoutCustomer",
     *      tags={"Customers"},
     *      summary="Logout customer",
     *      description="Logout customer",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="Logged out successfully.")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      )
     * )
     */
    public function logout() {}
}
