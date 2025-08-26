<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\StorageFile;

trait HasAvatar
{
    public function getAvatarDefault()
    {
        return property_exists($this, 'avatarDefault') ? $this->avatarDefault : 'images/default/avatar.png';
    }

    public function initializeHasAvatar()
    {
        $this->fillable[] = 'avatar_file_id';
    }

    public function getAvatar($thumb = '')
    {
        $defaultAvatar = $this->getAvatarDefault();
        if (! $this->avatar_file_id) {
            return asset($defaultAvatar);
        }

        $storageFile = StorageFile::findByField('id', $this->avatar_file_id);

        if (! $storageFile) {
            return asset($defaultAvatar);
        }

        if (! $thumb) {
            return $storageFile->getUrl();
        }

        return $storageFile->getChildUrl($thumb);
    }
}
