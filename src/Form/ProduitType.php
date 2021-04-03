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
                ['label' => 'libelle du produit', 'attr' => ['placeholder' =>'libelle']])
            ->add('prix', NumberType::class,
                ['label' => 'prix du produit', 'attr' => ['placeholder' =>'prix']])
            ->add('quantite', IntegerType::class,
                ['label' => 'quantité du produit', 'attr' => ['placeholder' =>'quantité']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
