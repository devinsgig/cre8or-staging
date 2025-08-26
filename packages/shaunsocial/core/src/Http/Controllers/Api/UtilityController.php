<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Utility\AccessCodeValidate;
use Packages\ShaunSocial\Core\Http\Requests\Utility\ShareEmailValidate;
use Packages\ShaunSocial\Core\Http\Requests\Utility\StoreContactValidate;
use Packages\ShaunSocial\Core\Http\Requests\Utility\StoreFcmTokenValidate;
use Packages\ShaunSocial\Core\Http\Requests\Utility\UnsubscribeEmailValidate;
use Packages\ShaunSocial\Core\Repositories\Api\UtilityRepository;

class UtilityController extends ApiController
{
    protected $utilityRepository;

    public function getWhitelistForceLogin()
    {
        return [
            'unsubscribe_email',
            'check_access_code',
            'store_contact'
        ];
    }

    public function __construct(UtilityRepository $utilityRepository)
    {
        $this->utilityRepository = $utilityRepository;
        parent::__construct();
    }

    public function share_email(ShareEmailValidate $request)
    {
        $request->mergeIfMissing([
            'message' => ''
        ]);
        
        $this->utilityRepository->share_email($request->all(), $request->user());
        
        return $this->successResponse();
    }

    public function unsubscribe_email(UnsubscribeEmailValidate $request)
    {
        $this->utilityRepository->unsubscribe_email($request->email);
    
        return $this->successResponse();
    }

    public function check_access_code(AccessCodeValidate $request)
    {
        return $this->successResponse();
    }

    public function store_contact(StoreContactValidate $request)
    {
        $this->utilityRepository->store_contact($request->all());  
        
        return $this->successResponse();
    }

    public function store_fcm_token(StoreFcmTokenValidate $request)
    {
        $this->utilityRepository->store_fcm_token($request->all(), $request->user());

        return $this->successResponse();
    }
}
