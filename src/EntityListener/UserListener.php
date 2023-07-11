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


    /** Encore password based on plain password
     * @param User $user
     * @return void
     */

    public function encodePassword(User $user){

        $plainPassword = $user->getPlainPassword();

        if ($plainPassword === null) {
            return;
        }

        $hashed = $this->hasher->hashPassword(
            $user,
            $plainPassword
        );

        $user->setPassword($hashed);
    }
}