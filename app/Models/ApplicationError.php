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
        'level',
        'platform',
        'is_read',
        'assigned_to',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
        'line'    => 'integer',
        'is_read' => 'boolean',
    ];

    /**
     * Classify error meta data (e.g. who should handle it).
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function classifyError(array $attributes): array
    {
        $platform = $attributes['platform'] ?? null;
        $code     = $attributes['code'] ?? null;
        $source   = $attributes['source'] ?? null;

        $isMobilePlatform = in_array($platform, ['ios', 'android'], true);

        $isFlutterRelated = str_contains(strtolower((string) $code), 'flutter')
            || str_contains(strtolower((string) $source), 'flutter');

        $assignedTo = ($isMobilePlatform || $isFlutterRelated)
            ? 'developer'
            : 'manager';

        $attributes['assigned_to'] = $assignedTo;

        return $attributes;
    }
}
