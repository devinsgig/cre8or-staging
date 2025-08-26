<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'name',
        'alias',
        'support_icon',
        'support_child'
    ];

    protected $casts = [
        'support_icon' => 'boolean',
        'support_child' => 'boolean',
    ];
}
