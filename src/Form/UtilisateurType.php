<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', TextType::class,
                ['label' => 'login'])

            ->add('motDePasse', PasswordType::class,
                ['label' => 'mot de passe'])

            ->add('nom', TextType::class,
                ['label' => 'nom'])

            ->add('prenom', TextType::class,
                ['label' => 'prenom'])

            ->add('dateDeNaissance', BirthdayType::class,
                ['label' => 'date de naissance'])

            ->add('status', CheckboxType::class,
                ['label' => 'status']);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}

/**
 * @author
 * ASMA Jugurtha
 * BOUDAHBA Hylia
 */
