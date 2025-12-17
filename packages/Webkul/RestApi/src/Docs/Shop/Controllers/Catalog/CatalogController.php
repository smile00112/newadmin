<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Catalog;

class CatalogController
{
    /**
     * @OA\Get(
     *      path="/api/v1/catalog",
     *      operationId="getCatalog",
     *      tags={"Catalog"},
     *      summary="Get categories with products",
     *      description="Returns categories with their products, if you want to retrieve all at once pass pagination=0 otherwise ignore this parameter",
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Category ID",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="sort",
     *          description="Sort column",
     *          example="id",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="order",
     *          description="Sort order",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              enum={"desc", "asc"}
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="limit",
     *          description="Limit",
     *          in="query",
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
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(ref="#/components/schemas/CatalogCategory")
     *              ),
     *
     *              @OA\Property(
     *                  property="meta",
     *                  ref="#/components/schemas/Pagination"
     *              )
     *          )
     *      )
     * )
     */
    public function list() {}

    /**
     * @OA\Get(
     *      path="/api/v1/catalog/{id}",
     *      operationId="getCatalogCategory",
     *      tags={"Catalog"},
     *      summary="Get category with products by id",
     *      description="Returns category with its products by id",
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Category id",
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
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/CatalogCategory"
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    public function get() {}
}

