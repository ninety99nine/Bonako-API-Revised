<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;

class OrderRepository extends BaseRepository
{
    protected $requiresConfirmationBeforeDelete = true;
}
