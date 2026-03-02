<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Catalog;

class NomenclatureController
{
    /**
     * @OA\Get(
     *      path="/api/v1/nomenclature",
     *      operationId="getNomenclature",
     *      tags={"Catalog"},
     *      summary="Get nomenclature (products and ingredients)",
     *      description="Returns products and ingredients in a single cached response. Channel and locale are determined by request headers. Response is streamed as JSON.",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="products",
     *                  type="array",
     *
     *                  @OA\Items(ref="#/components/schemas/NomenclatureProduct")
     *              ),
     *
     *              @OA\Property(
     *                  property="ingredients",
     *                  type="array",
     *
     *                  @OA\Items(ref="#/components/schemas/NomenclatureIngredient")
     *              )
     *          )
     *      )
     * )
     */
    public function index() {}
}
