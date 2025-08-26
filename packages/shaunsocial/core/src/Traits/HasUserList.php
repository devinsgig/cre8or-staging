<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\User;

trait HasUserList
{
    public function filterUserList($lists, $viewer, $userField = 'user_id', $checks = [])
    {
        return $lists->filter(function ($value, $key) use ($viewer, $userField, $checks) {
            return $this->fitlerUser($value->{$userField}, $viewer, $checks);            
        });
    }

    public function filterSubjectList($lists, $viewer, $userField = 'user_id', $checks = [])
    {
        return $lists->filter(function ($value, $key) use ($viewer, $userField, $checks) {
            $subject = $value->getSubject();
            if (! $subject) {
                return false;
            }

            if (method_exists($subject, 'supportSource')) {
                if ($subject->has_source) {
                    $viewerId = $viewer ? $viewer->id : 0;
                    return $subject->checkShowWithSource($viewerId);
                }
            }

            return $this->fitlerUser($subject->{$userField}, $viewer, $checks);            
        });
    }

    public function fitlerUser($userId, $viewer, $checks = []) 
    {
        //check delete user
        $user = User::findByField('id', $userId);

        if ($viewer && $viewer->isModerator()) {
            if (! $user) {
                return false;
            }
            return true;
        }
        //check block user
        if ($viewer && $viewer->checkBlock($userId)) {
            return false;
        }
        
        if (! $user) {
            return false;
        }
    
        foreach ($checks as $check) {
            switch ($check) {
                case 'privacy':
                case 'active':                  
                    $viewerId = $viewer ? $viewer->id : 0;
                    if ($check == 'privacy' && ! $user->checkPrivacy($viewerId)) {
                        return false;
                    }

                    if ($check == 'active' && ! $user->is_active) {
                        return false;
                    }

                    break;
            }
        }
        return true;
    }
}
