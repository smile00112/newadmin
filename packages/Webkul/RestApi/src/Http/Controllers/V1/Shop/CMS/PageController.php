<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\CMS;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Webkul\CMS\Repositories\PageRepository;

class PageController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PageRepository $pageRepository
    ) {}

    /**
     * Get HTML content of a CMS page by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getHtmlContent(int $id): Response
    {
        $page = $this->pageRepository->find($id);

        if (! $page) {
            return response('', 404);
        }

        // Check if page is available for current channel
        $currentChannel = core()->getCurrentChannel();
        if ($currentChannel && ! $page->channels->contains($currentChannel->id)) {
            return response('', 404);
        }

        return response($page->html_content ?? '', 200)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
}
