<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\CMS;

class PageController
{
    /**
     * @OA\Get(
     *      path="/api/v1/cms/{id}/html",
     *      operationId="getCmsPageHtml",
     *      tags={"CMS Pages"},
     *      summary="Get CMS page HTML content",
     *      description="Returns HTML content of a CMS page by ID. The page must be available for the current channel.",
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="CMS Page ID",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer",
     *              example=1
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\MediaType(
     *              mediaType="text/html",
     *              @OA\Schema(
     *                  type="string",
     *                  description="HTML content of the CMS page"
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Page not found or not available for current channel"
     *      )
     * )
     */
    public function getHtmlContent() {}
}
