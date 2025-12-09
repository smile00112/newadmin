<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

/**
 * @OA\Tag(
 *     name="Multi-Channel Authentication",
 *     description="API endpoints for SMS, WhatsApp, and Telegram authentication"
 * )
 */
class MultiChannelAuthController
{
    /**
     * @OA\Post(
     *      path="/api/v1/customer/auth/sms/initiate",
     *      operationId="initiateSmsAuth",
     *      tags={"Multi-Channel Authentication"},
     *      summary="Initiate SMS authentication",
     *      description="Send verification code via SMS to customer's phone number",
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="phone_number",
     *                      type="string",
     *                      example="1234567890",
     *                      description="Phone number without country code"
     *                  ),
     *                  @OA\Property(
     *                      property="country_code",
     *                      type="string",
     *                      example="+1",
     *                      description="Country code (2 characters)"
     *                  ),
     *                  @OA\Property(
     *                      property="device_name",
     *                      type="string",
     *                      example="iPhone 13",
     *                      description="Device name for token creation"
     *                  ),
     *                  required={"phone_number", "country_code", "device_name"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Verification code sent successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Verification code sent to your phone."),
     *              @OA\Property(property="verification_token", type="string", example="abc123def456"),
     *              @OA\Property(property="expires_in", type="integer", example=600)
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Invalid phone number format",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid phone number format.")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="No account found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="No account found with this phone number.")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Failed to send verification code",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Failed to send verification code.")
     *          )
     *      )
     * )
     */
    public function initiateSmsAuth() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/auth/whatsapp/initiate",
     *      operationId="initiateWhatsAppAuth",
     *      tags={"Multi-Channel Authentication"},
     *      summary="Initiate WhatsApp authentication",
     *      description="Send verification code via WhatsApp to customer's phone number",
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="phone_number",
     *                      type="string",
     *                      example="1234567890",
     *                      description="Phone number without country code"
     *                  ),
     *                  @OA\Property(
     *                      property="country_code",
     *                      type="string",
     *                      example="+1",
     *                      description="Country code (2 characters)"
     *                  ),
     *                  @OA\Property(
     *                      property="device_name",
     *                      type="string",
     *                      example="iPhone 13",
     *                      description="Device name for token creation"
     *                  ),
     *                  @OA\Property(
     *                      property="whatsapp_id",
     *                      type="string",
     *                      example="whatsapp_user_123",
     *                      description="WhatsApp user ID (optional)"
     *                  ),
     *                  required={"phone_number", "country_code", "device_name"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Verification code sent successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Verification code sent to your WhatsApp."),
     *              @OA\Property(property="verification_token", type="string", example="abc123def456"),
     *              @OA\Property(property="expires_in", type="integer", example=600)
     *          )
     *      )
     * )
     */
    public function initiateWhatsAppAuth() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/auth/telegram/initiate",
     *      operationId="initiateTelegramAuth",
     *      tags={"Multi-Channel Authentication"},
     *      summary="Initiate Telegram authentication",
     *      description="Send verification code via Telegram to customer's Telegram ID",
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="telegram_id",
     *                      type="string",
     *                      example="123456789",
     *                      description="Telegram user ID"
     *                  ),
     *                  @OA\Property(
     *                      property="username",
     *                      type="string",
     *                      example="john_doe",
     *                      description="Telegram username (optional)"
     *                  ),
     *                  @OA\Property(
     *                      property="first_name",
     *                      type="string",
     *                      example="John",
     *                      description="First name (optional)"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      example="Doe",
     *                      description="Last name (optional)"
     *                  ),
     *                  @OA\Property(
     *                      property="device_name",
     *                      type="string",
     *                      example="iPhone 13",
     *                      description="Device name for token creation"
     *                  ),
     *                  required={"telegram_id", "device_name"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Verification code sent successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Verification code sent to your Telegram."),
     *              @OA\Property(property="verification_token", type="string", example="abc123def456"),
     *              @OA\Property(property="expires_in", type="integer", example=600)
     *          )
     *      )
     * )
     */
    public function initiateTelegramAuth() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/auth/verify",
     *      operationId="verifyAndAuthenticate",
     *      tags={"Multi-Channel Authentication"},
     *      summary="Verify code and authenticate",
     *      description="Verify the received code and authenticate the user",
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="verification_code",
     *                      type="string",
     *                      example="123456",
     *                      description="6-digit verification code"
     *                  ),
     *                  @OA\Property(
     *                      property="verification_token",
     *                      type="string",
     *                      example="abc123def456",
     *                      description="Verification token from initiate request"
     *                  ),
     *                  @OA\Property(
     *                      property="device_name",
     *                      type="string",
     *                      example="iPhone 13",
     *                      description="Device name for token creation (optional)"
     *                  ),
     *                  required={"verification_code", "verification_token"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Authentication successful",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", ref="#/components/schemas/Customer"),
     *              @OA\Property(property="message", type="string", example="Authentication successful."),
     *              @OA\Property(property="token", type="string", example="1|abc123def456...")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Invalid or expired verification code",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid or expired verification code.")
     *          )
     *      )
     * )
     */
    public function verifyAndAuthenticate() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/auth/reset-token",
     *      operationId="resetToken",
     *      tags={"Multi-Channel Authentication"},
     *      summary="Reset authentication token",
     *      description="Send verification code to reset authentication token via different channels",
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="reset_method",
     *                      type="string",
     *                      enum={"sms", "whatsapp", "telegram", "email"},
     *                      example="sms",
     *                      description="Method to send verification code"
     *                  ),
     *                  @OA\Property(
     *                      property="phone_number",
     *                      type="string",
     *                      example="+11234567890",
     *                      description="Phone number (required for SMS/WhatsApp)"
     *                  ),
     *                  @OA\Property(
     *                      property="telegram_id",
     *                      type="string",
     *                      example="123456789",
     *                      description="Telegram ID (required for Telegram)"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      example="user@example.com",
     *                      description="Email address (required for email)"
     *                  ),
     *                  required={"reset_method"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Verification code sent successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Verification code sent via sms."),
     *              @OA\Property(property="verification_token", type="string", example="abc123def456"),
     *              @OA\Property(property="expires_in", type="integer", example=600)
     *          )
     *      )
     * )
     */
    public function resetToken() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/auth/verify-reset",
     *      operationId="verifyResetAndGenerateToken",
     *      tags={"Multi-Channel Authentication"},
     *      summary="Verify reset code and generate new token",
     *      description="Verify the reset code and generate a new authentication token",
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="verification_code",
     *                      type="string",
     *                      example="123456",
     *                      description="6-digit verification code"
     *                  ),
     *                  @OA\Property(
     *                      property="verification_token",
     *                      type="string",
     *                      example="abc123def456",
     *                      description="Verification token from reset request"
     *                  ),
     *                  required={"verification_code", "verification_token"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Token reset successful",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", ref="#/components/schemas/Customer"),
     *              @OA\Property(property="message", type="string", example="Token reset successful."),
     *              @OA\Property(property="token", type="string", example="1|abc123def456...")
     *          )
     *      )
     * )
     */
    public function verifyResetAndGenerateToken() {}
}
