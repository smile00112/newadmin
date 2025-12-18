<?php

namespace Webkul\MobileApp\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\MobileApp\Contracts\MobileAppSetting as MobileAppSettingContract;

class MobileAppSetting extends Model implements MobileAppSettingContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'mobile_app_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
        'channel_code',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'value' => 'array',
    ];
}

