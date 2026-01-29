<?php

namespace Webkul\Bonus\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Bonus\Contracts\BonusSetting as BonusSettingContract;

class BonusSetting extends Model implements BonusSettingContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'bonus_settings';

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
