<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountWarmingParticipant extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_account_warming_participants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_warming_id',
        'whatsapp_instance_id',
        'messages_sent',
        'messages_received',
        'last_message_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'messages_sent' => 'integer',
        'messages_received' => 'integer',
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the account warming that owns the participant.
     */
    public function accountWarming(): BelongsTo
    {
        return $this->belongsTo(AccountWarming::class);
    }

    /**
     * Get the whatsapp instance for the participant.
     */
    public function whatsappInstance(): BelongsTo
    {
        return $this->belongsTo(VacapInstance::class, 'whatsapp_instance_id');
    }
}


