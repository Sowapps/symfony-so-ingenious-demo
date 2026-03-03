<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractUser;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends AbstractUser {
    /**
     * In case of using a proxy, we force this user class for comparison
     * Required to work with Symfony authentication and equals()
     */
    public static function getClass(): string {
        return self::class;
    }
}
