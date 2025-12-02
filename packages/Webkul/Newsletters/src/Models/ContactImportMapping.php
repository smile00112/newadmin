<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;

class ContactImportMapping extends Model
{
    protected $table = 'newsletters_contact_import_mappings';

    protected $fillable = [
        'contact_group_id',
        'model_field',
        'csv_field',
        'csv_index',
    ];

    /**
     * Get the contact group associated with the mapping.
     */
    public function group()
    {
        return $this->belongsTo(NewslettersContactGroup::class, 'contact_group_id');
    }
}


