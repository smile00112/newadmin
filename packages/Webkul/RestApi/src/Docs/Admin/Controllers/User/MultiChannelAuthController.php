<?php

namespace Webkul\RestApi\Docs\Admin\Controllers\User;

/**
 * @OA\Tag(
 *     name="Admin Multi-Channel Authentication",
 *     description="API endpoints for admin SMS, WhatsApp, and Telegram authentication"
 * )
 */
class MultiChannelAuthController
{
    /**
     * @OA\Post(
     *      path="/api/v1/admin/auth/sms/initiate",
     *      operationId="adminInitiateSmsAuth",
     *      tags={"Admin Multi-Channel Authentication"},
     *      summary="Initiate SMS authentication for admin",
     *      description="Send verification code via SMS to admin's phone number",
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
     *                      example="Admin Desktop",
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
     *          description="No admin account found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="No admin account found with this phone number.")
     *          )
     *      )
     * )
     */
    public function initiateSmsAuth() {}

    /**
     * @OA\Post(
     *      path="/api/v1/admin/auth/whatsapp/initiate",
     *      operationId="adminInitiateWhatsAppAuth",
     *      tags={"Admin Multi-Channel Authentication"},
     *      summary="Initiate WhatsApp authentication for admin",
     *      description="Send verification code via WhatsApp to admin's phone number",
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
     *                      example="Admin Desktop",
     *                      description="Device name for token creation"
     *                  ),
     *                  @OA\Property(
     *                      property="whatsapp_id",
     *                      type="string",
     *                      example="admin_whatsapp_123",
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
     *      path="/api/v1/admin/auth/telegram/initiate",
     *      operationId="adminInitiateTelegramAuth",
     *      tags={"Admin Multi-Channel Authentication"},
     *      summary="Initiate Telegram authentication for admin",
     *      description="Send verification code via Telegram to admin's Telegram ID",
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
     *                      example="admin_user",
     *                      description="Telegram username (optional)"
     *                  ),
     *                  @OA\Property(
     *                      property="first_name",
     *                      type="string",
     *                      example="Admin",
     *                      description="First name (optional)"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      example="User",
     *                      description="Last name (optional)"
     *                  ),
     *                  @OA\Property(
     *                      property="device_name",
     *                      type="string",
     *                      example="Admin Desktop",
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
     *      path="/api/v1/admin/auth/verify",
     *      operationId="adminVerifyAndAuthenticate",
     *      tags={"Admin Multi-Channel Authentication"},
     *      summary="Verify code and authenticate admin",
     *      description="Verify the received code and authenticate the admin user",
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
     *                      example="Admin Desktop",
     *                      description="Device name for token creation (optional)"
     *                  ),
     *                  required={"verification_code", "verification_token"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Admin authentication successful",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", ref="#/components/schemas/User"),
     *              @OA\Property(property="message", type="string", example="Admin authentication successful."),
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
     *      path="/api/v1/admin/auth/reset-token",
     *      operationId="adminResetToken",
     *      tags={"Admin Multi-Channel Authentication"},
     *      summary="Reset admin authentication token",
     *      description="Send verification code to reset admin authentication token via different channels",
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
     *                      example="admin@example.com",
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
     *      path="/api/v1/admin/auth/verify-reset",
     *      operationId="adminVerifyResetAndGenerateToken",
     *      tags={"Admin Multi-Channel Authentication"},
     *      summary="Verify reset code and generate new admin token",
     *      description="Verify the reset code and generate a new admin authentication token",
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
     *          description="Admin token reset successful",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", ref="#/components/schemas/User"),
     *              @OA\Property(property="message", type="string", example="Admin token reset successful."),
     *              @OA\Property(property="token", type="string", example="1|abc123def456...")
     *          )
     *      )
     * )
     */
    public function verifyResetAndGenerateToken() {}
}
