<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', TextType::class,
                ['label' => 'login', 'attr' => ['placeholder' =>'login']])

            ->add('motDePasse', TextType::class,
                ['label' => 'mot de passe', 'attr' => ['placeholder' =>'mot de passe']])

            ->add('nom', TextType::class,
                ['label' => 'nom', 'attr' => ['placeholder' =>'nom']])

            ->add('prenom', TextType::class,
                ['label' => 'prenom', 'attr' => ['placeholder' =>'prenom']])

            ->add('dateDeNaissance', DateType::class,
                ['label' => 'date de naissance', 'attr' => ['placeholder' =>'date de naissance']])

            //->add('status', CheckboxType::class,
                //['label' => 'status');
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
