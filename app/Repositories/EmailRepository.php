<?php

namespace App\Repositories;

use App\Models\Email;

class EmailRepository
{
    /**
     * @param array $data
     * @return void
     */
    public function create(array $data): void
    {
         Email::create($data);
    }


}
