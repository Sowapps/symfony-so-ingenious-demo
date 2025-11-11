<?php

namespace App\Entity;

use App\Repository\LocalizedUnitRepository;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: LocalizedUnitRepository::class)]
class LocalizedUnit extends AbstractEntity
{
}
