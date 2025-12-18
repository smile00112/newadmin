<?php

namespace Webkul\RestApi\Docs\Shop\Controllers;

class MobileSettingsController
{
    /**
     * @OA\Get(
     *      path="/api/v1/mobile-settings",
     *      operationId="getMobileAppSettings",
     *      tags={"Mobile App"},
     *      summary="Get mobile app settings",
     *      description="Returns mobile app configuration settings including app info, filters, and custom data",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="channel",
     *          description="Channel code (optional, defaults to default channel)",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              example="default"
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
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="app_name",
     *                      type="string",
     *                      description="Application name",
     *                      example="My Store App"
     *                  ),
     *                  @OA\Property(
     *                      property="app_version",
     *                      type="string",
     *                      description="Current app version",
     *                      example="1.0.0"
     *                  ),
     *                  @OA\Property(
     *                      property="min_app_version",
     *                      type="string",
     *                      description="Minimum required app version",
     *                      example="1.0.0"
     *                  ),
     *                  @OA\Property(
     *                      property="force_update",
     *                      type="boolean",
     *                      description="Whether to force app update",
     *                      example=false
     *                  ),
     *                  @OA\Property(
     *                      property="maintenance_mode",
     *                      type="boolean",
     *                      description="Whether app is in maintenance mode",
     *                      example=false
     *                  ),
     *                  @OA\Property(
     *                      property="custom_data",
     *                      type="string",
     *                      description="Custom JSON data",
     *                      example="{}"
     *                  ),
     *                  @OA\Property(
     *                      property="home_filters",
     *                      type="array",
     *                      description="Filters for home screen with attribute options",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(
     *                              property="code",
     *                              type="string",
     *                              description="Attribute code",
     *                              example="color"
     *                          ),
     *                          @OA\Property(
     *                              property="name",
     *                              type="string",
     *                              description="Attribute name",
     *                              example="Color"
     *                          ),
     *                          @OA\Property(
     *                              property="type",
     *                              type="string",
     *                              description="Attribute type",
     *                              example="select"
     *                          ),
     *                          @OA\Property(
     *                              property="options",
     *                              type="array",
     *                              description="Available options for this attribute",
     *                              @OA\Items(
     *                                  type="object",
     *                                  @OA\Property(property="id", type="integer", example=1),
     *                                  @OA\Property(property="code", type="string", example="red"),
     *                                  @OA\Property(property="label", type="string", example="Red")
     *                              )
     *                          )
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="featured_categories",
     *                      type="array",
     *                      description="Featured category IDs",
     *                      @OA\Items(type="integer", example=1)
     *                  ),
     *                  @OA\Property(
     *                      property="featured_products",
     *                      type="array",
     *                      description="Featured product IDs",
     *                      @OA\Items(type="integer", example=1)
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function index() {}
}

