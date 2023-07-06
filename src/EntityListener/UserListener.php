<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener{

    private UserPasswordHasherInterface $hasher;
    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }
    public function prePersit(User $user){
        $this->encodePassword($user);
    }

    public function preUpdate(User $user){
        $this->encodePassword($user);
    }

    /** Encore password based on plain password
     * @param User $user
     * @return void
     */
    public function encodePassword(User $user){
        if ($user->getPlainPassword() === null){
            return;
        }

        $user->setPassword(
            $this->hasher->hashPassword(
                $user,
                $user->getPlainPassword()
            )
        );
    }
}