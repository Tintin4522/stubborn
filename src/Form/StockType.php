<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantityXS', IntegerType::class, [
                'required' => false,
                'label' => 'Quantité XS :',
                'attr' => ['min' => 0, 'class' => 'form-control quantity-field']
            ])
            ->add('quantityS', IntegerType::class, [
                'required' => false,
                'label' => 'Quantité S :',
                'attr' => ['min' => 0, 'class' => 'form-control quantity-field']
            ])
            ->add('quantityM', IntegerType::class, [
                'required' => false,
                'label' => 'Quantité M :',
                'attr' => ['min' => 0, 'class' => 'form-control quantity-field']
            ])
            ->add('quantityL', IntegerType::class, [
                'required' => false,
                'label' => 'Quantité L :',
                'attr' => ['min' => 0, 'class' => 'form-control quantity-field']
            ])
            ->add('quantityXL', IntegerType::class, [
                'required' => false,
                'label' => 'Quantité XL :',
                'attr' => ['min' => 0, 'class' => 'form-control quantity-field']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
