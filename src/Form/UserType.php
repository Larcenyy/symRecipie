<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class,
                [
                    'attr' => [
                        'class' => 'form-control',
                        'minlenght' => 3,
                        'maxlenght' => 30
                    ],
                    'label' => 'Nom / Prénom',
                    'label_attr' => [
                        'class' => 'form-label'
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(['min' => 3, 'max' => 30])
                    ]
                ])

            ->add('pseudo', TextType::class,
                [
                    'attr' => [
                        'class' => 'form-control',
                        'minlenght' => 3,
                        'maxlenght' => 30
                    ],
                    'required' => false,
                    'label' => 'Pseudo (Facultatif)',
                    'label_attr' => [
                        'class' => 'form_label'
                    ],
                    'constraints' => [
                        new Assert\Length(['min' => 3, 'max' => 30])
                    ]
                ])
            ->add('plainPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Mot de passe',
                'label_attr' => [
                    'class' => 'form_label'
                ],
            ])
            ->add('submit', SubmitType::class, ['attr' => ["class" => 'btn btn-primary mt-4']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
