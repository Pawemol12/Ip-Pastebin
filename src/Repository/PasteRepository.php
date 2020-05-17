<?php

namespace App\Repository;

use App\Entity\Paste;
use Doctrine\Persistence\ManagerRegistry;

class PasteRepository extends CoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paste::class);
    }

}
