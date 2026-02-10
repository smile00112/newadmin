<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationError extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'application_errors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message',
        'code',
        'file',
        'line',
        'trace',
        'context',
        'source',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
        'line'    => 'integer',
    ];
}
