<?php

namespace Webkul\Newsletters\Repositories;

use Webkul\Core\Eloquent\Repository;

class ContactImportMappingRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model()
    {
        return 'Webkul\Newsletters\Models\ContactImportMapping';
    }
}


