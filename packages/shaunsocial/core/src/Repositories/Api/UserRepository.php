<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;
use Packages\ShaunSocial\Core\Http\Resources\User\UserAvatarResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserBlockResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserCoverResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserDownloadResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserEditProfileResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserMeResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserProfileResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\MailUnsubscribe;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserBlock;
use Packages\ShaunSocial\Core\Models\UserDailyEmail;
use Packages\ShaunSocial\Core\Models\UserDownload;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Support\Facades\Mail;
use Packages\ShaunSocial\Core\Traits\CacheSearchPagination;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Core\Traits\Utility;

class UserRepository
{
    use HasUserList, CacheSearchPagination, Utility;

    public function me($viewer)
    {
        return new UserMeResource($viewer ?? getUserGuest());
    }

    public function search($query, $data, $viewer)
    {       
        $key = 'user_search_user_name';
        if (!empty($data['only_user'])) {
            $key .= '_only_user';
        }
        if (!empty($data['not_me'])) {
            $key .= '_not_me';
        }
        if (!empty($data['not_parent'])) {
            $key .= '_not_parent';
        }
        $key .= '_'.$query;
        $users = Cache::remember($key, config('shaun_core.cache.time.short'), function () use ($query, $data, $viewer) {
            $builder = User::where(function($model) use ($query) {
                $model->orWhere('user_name', 'LIKE', '%'.$query.'%');
                $model->orWhere('name', 'LIKE', '%'.$query.'%');
            })->where('is_active', true)->orderBy(DB::raw("LOCATE('".$query."', user_name)"))->limit(setting('feature.item_per_page'));
            if (!empty($data['only_user'])) {
                $builder->where('is_page', false);
            }
            if (!empty($data['not_me']) && $viewer) {
                $builder->where('id', '!=', $viewer->id);
            }
            if (!empty($data['not_parent']) && $viewer && $viewer->isPage()) {
                $builder->where('id', '!=', $viewer->getParent()->id);
            }
            return $builder->get();
        });

        return UserResource::collection($this->filterUserList($users, $viewer, 'id'));
    }

    public function search_name($query, $viewer)
    {        
        $users = Cache::remember('user_search_name_'.$query, config('shaun_core.cache.time.short'), function () use ($query) {
            return User::where('name', 'LIKE', '%'.$query.'%')->where('is_active', true)->orderBy(DB::raw("LOCATE('".$query."', name)"))->limit(setting('feature.item_per_page'))->get();
        });

        return UserResource::collection($this->filterUserList($users, $viewer, 'id'));
    }

    public function addConditionUserFollow($builder, $viewer)
    {        
        if ($viewer->following_count) {
            if ($viewer->following_count > config('shaun_core.follow.user.max_query_join')) {
                $builder->whereNotIn('id', function($select) use ($viewer) {
                   $select->from('user_follows')
                    ->select('follower_id')
                    ->where('user_id', $viewer->id);
                });
            } else {
                $userFollowers = $viewer->getFollows()->pluck('follower_id')->toArray();
                $builder->whereNotIn('id',$userFollowers);
            }
        }

        return $builder;
    }

    public function suggest_signup($viewer, $query)
    {
        $builder = User::orderBy('follower_count', 'DESC')->where('is_page', false)->orderBy('id', 'DESC')->where('id','<>', $viewer->id)->limit(setting('feature.item_per_page'))->where('is_active', true);

        if ($query) {
            $builder->where('name','LIKE', '%'.$query.'%')->orderBy(DB::raw("LOCATE('".$query."', name)"));            
            $users = Cache::remember('user_suggest_signup_'.md5($viewer->id.$query), config('shaun_core.cache.time.short'), function () use ($builder) {
                return $builder->get();
            });

            return UserResource::collection($this->filterUserList($users, $viewer, 'id'));
        } else {
            $result = $this->trending($viewer);
            if (! count($result)) {
                if ($this->checkSuggest($viewer)) {
                    $builder = $this->getBuilderSuggest($viewer);                
                    $builder->limit(config('shaun_core.core.number_item_random'));
                } else {
                    $builder = $this->getBuilderSuggest($viewer, true);                
                } 

                $users = Cache::remember('user_suggest_signup_'.$viewer->id, config('shaun_core.cache.time.short'), function () use ($builder) {
                    return $builder->get();
                });

                $users = $this->filterUserList($users, $viewer, 'id');
                if (count($users) > setting('feature.item_per_page')) {
                    $users = $users->random(setting('feature.item_per_page'));
                }

                return UserResource::collection($this->filterUserList($users, $viewer, 'id'));
            }

            return $result;
        }
    }

    public function trending($viewer, $limit = null)
    {
        if (! $limit) {
            $limit = setting('feature.item_per_page');
        }

        $builder = User::orderBy('follower_count', 'DESC')->where('is_page', false)->where('follower_count', '>', 0)->orderBy('id', 'DESC')->limit(config('shaun_core.core.number_item_random'))->where('is_active', true);

        $users = Cache::remember('user_trending_'.$limit, config('shaun_core.cache.time.trending'), function () use ($builder, $viewer, $limit) {            
            $users = $builder->get();
            $users = $this->filterUserList($users, $viewer, 'id');
            
            if (count($users) > $limit) {
                $users = $users->random($limit);
            }

            return $users;
        });        

        return UserResource::collection($users);
    }

    public function checkSuggest($viewer)
    {
        if (! $viewer->hashtag_follow_count) {
            return false;
        }

        $builder = User::where('id','<>', $viewer->id)->where('is_page', false)->where('is_active', true);
        $hashtagFollowers = $viewer->getHashtagFollows()->pluck('hashtag_id')->join(' ');
        $builder->whereFullText('hashtags',$hashtagFollowers);
        $builder = $this->addConditionUserFollow($builder, $viewer);

        $count = Cache::remember('user_suggest_count_'.$viewer->id, config('shaun_core.cache.time.suggest_check'), function () use ($builder) {
            return $builder->count();
        });

        if ($count > config('shaun_core.core.user_suggest_check_limit')) {
            return true;
        }

        return false;
    }

    public function filterUserSuggestList($users, $viewer = null)
    {
        return $users->filter(function ($value, $key) use ($viewer) {
            return $viewer ? !$viewer->getFollow($value->id) && !$viewer->checkBlock($value->id): true;
        })->values();
    }

    public function getBuilderSuggest($viewer, $popular = false)
    {
        $builder = User::orderBy('follower_count', 'DESC')->where('is_page', false)->orderBy('id', 'DESC')->where('id','<>', $viewer->id)->where('is_active', true);

        if (! $popular) {
            $hashtagFollowers = $viewer->getHashtagFollows()->pluck('hashtag_id')->join(' ');
            $builder->whereFullText('hashtags',$hashtagFollowers);
            $builder = $this->addConditionUserFollow($builder, $viewer);
        }

        return $builder;
    }

    public function suggest($viewer, $limit = null)
    {
        if (! $viewer) {
            return collect();
        }
        if (!$limit) {
            $limit =  setting('feature.item_per_page');
        }
        
        if ($this->checkSuggest($viewer)) {

            $builder = $this->getBuilderSuggest($viewer);
            $builder->limit(config('shaun_core.core.number_item_random'));
            $users = Cache::remember('user_suggest_'.$viewer->id, config('shaun_core.cache.time.trending'), function () use ($builder, $viewer) {            
                return $this->filterUserList($builder->get(), $viewer, 'id');
            });
            
        } else {
            $builder = $this->getBuilderSuggest($viewer, true);
            $builder->limit(config('shaun_core.core.number_item_random'));
            $users = Cache::remember('user_suggest_popular_'.$viewer->id, config('shaun_core.cache.time.trending'), function () use ($builder, $viewer) {            
                return $this->filterUserList($builder->get(), $viewer, 'id');
            });
        }     

        $users = $this->filterUserSuggestList($users, $viewer);
        if (count($users) > $limit) {
            $users = $users->random($limit);
        }

        return UserResource::collection($users);
    }

    public function profile($userName, $viewer)
    {
        $user = User::findByField('user_name', $userName);
        
        return new UserProfileResource($user);
    }

    public function store_block($data, $viewer)
    {
        switch ($data['action']) {
            case 'add':
                $user = User::findByField('id', $data['id']);
                $viewer->addBlock($data['id'], $user->isPage());

                //Remove follow
                $follow = $viewer->getFollow($data['id']);
                if ($follow) {
                    $follow->delete();
                }

                $follow = $user->getFollow($viewer->id);
                if ($follow) {
                    $follow->delete();
                }

                //Remove chat request
                $room = ChatRoom::getRoomTwoUser($viewer->id, $data['id']);
                if ($room) {
                    $members = $room->getMembers();
                    foreach ($members as $member) {
                        if ($member->status == 'sent') {
                            $member->update([
                                'status' => 'cancelled'
                            ]);
                        }

                        if ($member->status == 'accepted' && $member->message_count) {
                            $user = $member->getUser();
                            $count = $member->getUserChatCount();
                            $user->update([
                                'chat_count' => $count
                            ]);
                            
                            $member->update([
                                'message_count' => 0
                            ]);
                        }
                    }
                }

                break;
            case 'remove':
                $block = $viewer->getBlock($data['id']);
                $block->delete();
                break;
        }
    }

    public function block($viewer, $type ,$page)
    {
        $condition = UserBlock::where('user_id', $viewer->id)->orderBy('id', 'DESC');

        $blocks = [];
        $hasNextPage = false;
        switch ($type)
        {
            case 'all':
                $blocks = UserBlock::getCachePagination('block_'.$viewer->id, $condition, $page);
                $hasNextPage = checkNextPage($viewer->block_count, count($blocks), $page);
                break;
            case 'user':
                $condition->where('is_page', false);
                $blocks = UserBlock::getCachePagination('block_user_'.$viewer->id, $condition, $page);
                $blocksNextPage = UserBlock::getCachePagination('block_user_'.$viewer->id, $condition, $page + 1);
                $hasNextPage = count($blocksNextPage) ? true : false;
                break;
            case 'page':
                $condition->where('is_page', true);
                $blocks = UserBlock::getCachePagination('block_page_'.$viewer->id, $condition, $page);
                $blocksNextPage = UserBlock::getCachePagination('block_page_'.$viewer->id, $condition, $page + 1);
                $hasNextPage = count($blocksNextPage) ? true : false;
                break;
        }
        

        return [
            'items' => UserBlockResource::collection($blocks),
            'has_next_page' => $hasNextPage
        ];
    }

    public function notification_setting($viewer)
    {
        return array_merge(['enable_notify' => $viewer->enable_notify ? true : false], ['post' => $viewer->getPostNotifySetting(), 'system' => $viewer->getSystemNotifySetting(),'follow' => $viewer->getFollowNotifySetting(), 'chat' => $viewer->getChatNotifySetting(), 'story' => $viewer->getStoryNotifySetting(), 'paid_content' => $viewer->getPaidContentNotifySetting()]);
    }

    public function store_notification_setting($data, $viewer)
    {
        $notifySettingKeys = array_keys($viewer->getNotifySetting());
        $notifySettings = [];
        foreach ($notifySettingKeys as $key) {
            $notifySettings[$key] = $data[$key];
        }
        $viewer->update(['enable_notify' => $data['enable_notify'],'notify_setting' => json_encode($notifySettings)]);
    }

    public function privacy_setting($viewer)
    {
        return array_merge(['privacy' => $viewer->privacy, 'chat_privacy' => $viewer->chat_privacy], ['settings' => $viewer->getPrivacySetting()]);
    }

    public function store_privacy_setting($data, $viewer)
    {
        $privacySettingKeys = array_keys($viewer->getPrivacySetting());
        $privacySettings = [];
        foreach ($privacySettingKeys as $key) {
            $privacySettings[$key] = $data[$key];
        }
        $viewer->update(['privacy' => $data['privacy'], 'chat_privacy' => $data['chat_privacy'] ,'privacy_setting' => json_encode($privacySettings)]);
    }

    public function email_setting($viewer)
    {
        return array_merge(['email_enable' => $viewer->getMailUnsubscribe() ? false : true,'daily_email_enable' => $viewer->daily_email_enable ? true : false], ['settings' => $viewer->getEmailSetting()]);
    }

    public function store_email_setting($data, $viewer)
    {
        $mailUnsubscribe = $viewer->getMailUnsubscribe();
        if ($data['email_enable']) {
            if ($mailUnsubscribe) {
                $mailUnsubscribe->delete();
            }
        } else {
            if (! $mailUnsubscribe) {
                MailUnsubscribe::create([
                    'email' => $viewer->email
                ]);
            }
        }

        if ($viewer->daily_email_enable != $data['daily_email_enable'] && !$data['daily_email_enable']) {
            $dailyEmail = UserDailyEmail::findByField('user_id', $viewer->id);
            if ($dailyEmail) {
                $dailyEmail->delete();
            }
        }

        $viewer->update([
            'daily_email_enable' => $data['daily_email_enable']
        ]);
    }

    public function ping($viewer)
    {
        $viewer->setOnline();
        
        return [
            'notify_count' => $viewer->notify_count,
            'chat_count' => $viewer->chat_count,
            'wallet_balance' => $viewer->getCurrentBalance(),
        ];
    }

    public function store_login_first($viewer)
    {
        if (!$viewer->already_setup_login) {
            $viewer->update([
                'already_setup_login' => true
            ]);
        }
    }
    
    public function store_darkmode($viewer, $darkmode)
    {
        if ($viewer->darkmode != $darkmode) {
            $viewer->update([
                'darkmode' => $darkmode
            ]);
        }
    }

    public function store_video_auto_play($viewer, $enable)
    {
        if ($viewer->video_auto_play != $enable) {
            $viewer->update([
                'video_auto_play' => $enable
            ]);
        }
    }

    public function suggest_search($query, $viewer, $page)
    {
        $usersNextPage = [];
        $isSuggest = false;
        if ($this->checkSuggest($viewer)) {
            $isSuggest = true;
            $builder = $this->getBuilderSuggest($viewer);
        } else {
            $builder = $this->getBuilderSuggest($viewer, true);
        }

        if ($query) {
            $builder->where('name', 'LIKE', '%'.$query.'%');
        }

        $users = $this->getCacheSearchPagination('user_suggest_search_'.$viewer->id.$query, $builder, $page);
        $usersNextPage = $this->getCacheSearchPagination('user_suggest_search_'.$viewer->id.$query, $builder, $page + 1);
        $users = $this->filterUserList($users, $viewer, 'id');

        return [
            'items' => UserResource::collection($this->filterUserSuggestList($users, $isSuggest ? $viewer : null)),
            'has_next_page' => count($usersNextPage) ? true : false
        ];
    }

    public function trending_search($query, $viewer, $page)
    {
        $builder = User::orderBy('follower_count', 'DESC')->orderBy('id', 'DESC')->where('is_active', true);

        if ($query) {
            $builder->where('name', 'LIKE', '%'.$query.'%');
        }

        $users = $this->getCacheSearchPagination('user_trending_search_'.$query, $builder, $page);
        $usersNextPage = $this->getCacheSearchPagination('user_trending_search_'.$query, $builder, $page + 1);

        return [
            'items' => UserResource::collection($this->filterUserList($users, $viewer, 'id')),
            'has_next_page' => count($usersNextPage) ? true : false
        ];
    }

    public function upload_cover($file, $viewer)
    {
        $storageFile = File::storePhoto($file, [
            'parent_type' => 'user_cover',
            'user_id' => $viewer->id,
            'parent_id' => $viewer->id
        ]);

        $viewer->update([
            'cover_file_id' => $storageFile->id
        ]);

        return new UserCoverResource($viewer);
    }

    public function upload_avatar($file, $viewer)
    {
        $storageFile = File::storePhoto($file, [
            'parent_type' => 'user_avatar',
            'user_id' => $viewer->id,
            'parent_id' => $viewer->id,
            'resize_size' => [
                'real' => true,
                'width' => config('shaun_core.file.avatar.width'),
                'height' => config('shaun_core.file.avatar.height'),
            ]
        ]);

        $viewer->update([
            'avatar_file_id' => $storageFile->id
        ]);

        if (setting('user_verify.unverify_when') == 2) {
            if ($viewer->verify_status == UserVerifyStatus::OK) {
                $viewer->doUnVerify(false);
            }
        }

        return new UserAvatarResource($viewer);
    }

    public function get_edit_profile($viewer)
    {
        return new UserEditProfileResource($viewer);
    }

    public function store_edit_profile($data, $viewer)
    {
        if (!empty($data['privacyField']) && is_array($data['privacyField'])) {
            $privacyField = $data['privacyField'];
            $privacyFieldValue = $viewer->getPrivacyFieldSetting();
            foreach ($privacyFieldValue as $key => $value) {
                if (isset($privacyField[$key])) {
                    $privacyFieldValue[$key] = $privacyField[$key];
                }
            }
            $data['field_privacy'] = json_encode($privacyFieldValue);
        }
        if (isset($data['name'])) {
            if ($viewer->name != $data['name'] && $viewer->verify_status == UserVerifyStatus::OK) {
                $viewer->doUnVerify(false);
            }
        }
        $viewer->update($data);
    }

    public function send_email_verify($viewer)
    {
        $code = $this->createCodeVerify($viewer->id, 'email_verify', $viewer->email);
        Mail::send('email_verify', $viewer, ['code' => $code]);
    }

    public function check_email_verify($code, $viewer)
    {
        $viewer->update([
            'email_verified' => true
        ]);
    }

    public function change_password($password, $viewer)
    {
        $viewer->update([
            'password' => Hash::make($password)
        ]);
    }

    public function send_code_forgot_password($email)
    {
        $user = User::findByField('email', $email);
        $code = $this->createCodeVerify($user->id, 'forgot_password');

        Mail::send('forgot_password_code', $user, [
            'code' => $code
        ]);
    }

    public function store_forgot_password($email, $password)
    {
        $user = User::findByField('email', $email);
        $this->change_password($password, $user);
    }

    public function store_account($data, $viewer)
    {
        $viewer->update($data);
    }

    public function delete($viewer)
    {
        $viewer->tokens()->delete();
        $viewer->deleteFcmTokens();
        $viewer->delete();
    }

    public function store_language($key, $viewer)
    {
        $viewer->update([
            'language' => $key
        ]);
    }

    public function do_active($user)
    {
        Mail::send('active_user', $user,  [
            'link' => route('web.user.login')
        ]);
    }

    public function do_inactive($user)
    {
        Mail::send('inactive_user', $user, [
            'link' => route('web.contact.create')
        ]);
    }

    public function store($data, $isAdminCreate = false)
    {
        $user = User::create($data);
        if ($isAdminCreate) {
            $this->send_email_after_register($user, $data['password_real']);
        } else {
            $this->send_email_after_register($user);
        }

        return $user;
    }

    public function send_email_after_register($user, $password = null)
    {
        if ($user->email_verified) {
            if ($password) {
                Mail::send('welcome_user_with_password', $user, ['login_link' => route('web.user.login'), 'password' => $password]);
            } else {
                Mail::send('welcome_user', $user, ['login_link' => route('web.user.login')]);
            }
            
        } else {
            $code = $this->createCodeVerify($user->id, 'email_verify', $user->email);
            Mail::send('welcome_user_verify', $user, ['code' => $code]);
        }

    }

    public function get_download($viewer)
    {
        $userDownload = UserDownload::findByField('user_id', $viewer->id);

        return new UserDownloadResource($userDownload ?? new UserDownload());
    }

    public function store_download($viewer)
    {
        $userDownload = UserDownload::findByField('user_id', $viewer->id);
        if ($userDownload) {
            $userDownload->delete();
        }
        
        UserDownload::create([
            'user_id' => $viewer->id
        ]);
    }

    public function send_add_email_password_verify($viewer, $data)
    {
        $code = $this->createCodeVerify($viewer->id, 'email_verify', $data['email']);
        Mail::send('email_verify', $data['email'], ['code' => $code, 'recipient_name' => $viewer->getName()]);
    }

    public function store_add_email_password_verify($viewer, $data)
    {
        $viewer->update([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'has_email' => true,
        ]);
    }

    public function remove_login_other_device($viewer, $fcmToken = null)
    {
        $tokens = $viewer->tokens();
        $tokenCurrent = $viewer->currentAccessToken();
        $tokens->each(function($token) use ($tokenCurrent) {
            if ($token->token != $tokenCurrent->token) {
                $token->delete();
            }
        });

        $viewer->deleteFcmTokens($fcmToken);
    }

    public function send_phone_verify($viewer)
    {
        $code = $this->createCodeVerify($viewer->id, 'phone_verify', $viewer->phone_number);
        
        return $this->sendPhoneNumber($viewer->id, $viewer->phone_number, __('Code is').': '. $code);
    }

    public function check_phone_verify($viewer)
    {
        $viewer->update([
            'phone_verified' => true
        ]);

        $users = User::where('phone_number', $viewer->phone_number)->where('id', '!=', $viewer->id);
        $users->each(function($user) {
            $user->update([
                'phone_number' => ''
            ]);
        });
    }

    public function change_phone_verify($phoneNumber, $viewer, $update = false)
    {
        $code = $this->createCodeVerify($viewer->id, 'phone_verify', $phoneNumber);
        
        $result = $this->sendPhoneNumber($viewer->id, $phoneNumber, __('Code is').': '. $code);
        if ($result['status'] && $update) {
            $viewer->update([
                'phone_number' => $phoneNumber
            ]);
        }
        return $result;
    }

    public function check_phone_when_edit($phoneNumber, $viewer)
    {
        $viewer->update([
            'phone_number' => $phoneNumber
        ]);

        $users = User::where('phone_number', $phoneNumber)->where('id', '!=', $viewer->id);
        $users->each(function($user) {
            $user->update([
                'phone_number' => ''
            ]);
        });
    }
}