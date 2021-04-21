<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class,
                ['label' => 'libelle', 'attr' => ['placeholder' =>'libelle du produit']])
            ->add('prix', NumberType::class,
                ['label' => 'prix', 'attr' => ['placeholder' =>'prix du produit']])
            ->add('quantite', IntegerType::class,
                ['label' => 'quantité', 'attr' => ['placeholder' =>'quantité du produit']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}

/**
 * @author
 * ASMA Jugurtha
 * BOUDAHBA Hylia
 */
