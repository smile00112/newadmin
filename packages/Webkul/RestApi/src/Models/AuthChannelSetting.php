<?php

namespace Webkul\RestApi\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\RestApi\Contracts\AuthChannelSetting as AuthChannelSettingContract;

class AuthChannelSetting extends Model implements AuthChannelSettingContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'auth_channel_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'channel',
        'key',
        'value',
        'channel_code',
    ];
}
