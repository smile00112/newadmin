<?php

namespace Webkul\IikoIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\IikoIntegration\Contracts\IikoSetting as IikoSettingContract;

class IikoSetting extends Model implements IikoSettingContract
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

    /**
     * The channel name for iiko settings.
     */
    public const CHANNEL = 'iiko';
}
