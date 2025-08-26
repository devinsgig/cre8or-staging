<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Search\SearchValidate;
use Packages\ShaunSocial\Core\Http\Requests\Search\SuggestSearchValidate;
use Packages\ShaunSocial\Core\Repositories\Api\SearchRepository;

class SearchController extends ApiController
{
    protected $searchRepository;

    public function __construct(SearchRepository $searchRepository)
    {
        $this->searchRepository = $searchRepository;
        parent::__construct();
    }

    public function suggest(SuggestSearchValidate $request)
    {
        $result = $this->searchRepository->search($request->query('query'),$request->user());
    
        return $this->successResponse($result);
    }

    public function text(SearchValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->searchRepository->get($request->query('query'), $request->query('type'), $page, $request->user(), getUniqueFromRequest($request));
    
        return $this->successResponse($result);
    }

    public function hashtag(SearchValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->searchRepository->get($request->query('query'), $request->query('type'), $page, $request->user(), getUniqueFromRequest($request), true);
    
        return $this->successResponse($result);
    }
}
