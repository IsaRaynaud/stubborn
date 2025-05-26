<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Nom d'utilisateur : ",
            ])
            ->add('email', TextType::class, [
                'label' => 'Adresse e-mail : ',
            ])
            ->add('delivery_address', TextType::class, [
                'label' => "Adresse de livraison : ",
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe : ',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Tapez un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => "Votre mot de passe doit être d'au moins {{ limit }} caractères.",
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Confirmer le mot de passe : ',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez confirmer votre mot de passe',
                    ]),
                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'label' => "J'accepte les conditions générales d'utilisation : ",
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez accepter les conditions générales d'utilisation pour vous inscrire.",
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
