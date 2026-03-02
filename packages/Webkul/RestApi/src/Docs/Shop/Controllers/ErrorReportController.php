<?php

namespace Webkul\RestApi\Docs\Shop\Controllers;

class ErrorReportController
{
    /**
     * @OA\Post(
     *      path="/api/v1/errors",
     *      operationId="reportApplicationError",
     *      tags={"Application Errors"},
     *      summary="Report application error",
     *      description="Submit an error report from the application (e.g. mobile, web). No authentication required. The error is stored and visible in the admin panel.",
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
     *                      property="message",
     *                      type="string",
     *                      description="Error message (required)",
     *                      example="Undefined array key 'id'"
     *                  ),
     *                  @OA\Property(
     *                      property="code",
     *                      type="string",
     *                      nullable=true,
     *                      description="Error code or exception class",
     *                      example="ErrorException"
     *                  ),
     *                  @OA\Property(
     *                      property="file",
     *                      type="string",
     *                      nullable=true,
     *                      description="File path where error occurred",
     *                      example="/app/Http/Controllers/ExampleController.php"
     *                  ),
     *                  @OA\Property(
     *                      property="line",
     *                      type="integer",
     *                      nullable=true,
     *                      description="Line number",
     *                      example=42
     *                  ),
     *                  @OA\Property(
     *                      property="trace",
     *                      type="string",
     *                      nullable=true,
     *                      description="Stack trace (string or JSON string)"
     *                  ),
     *                  @OA\Property(
     *                      property="context",
     *                      type="object",
     *                      nullable=true,
     *                      description="Additional context (user_id, device, url, etc.)"
     *                  ),
     *                  @OA\Property(
     *                      property="source",
     *                      type="string",
     *                      nullable=true,
     *                      description="Source identifier (e.g. mobile, web, api)",
     *                      example="mobile"
     *                  ),
     *                  required={"message"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Error report saved successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Error report has been saved successfully."
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      type="integer",
     *                      description="Created error record ID",
     *                      example=1
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="The message field is required."
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  description="Validation errors by field"
     *              )
     *          )
     *      )
     * )
     */
    public function store() {}
}
