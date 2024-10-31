<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('size', TextType::class, [
                'label' => 'Taille :',
                'attr' => ['readonly' => true, 'class' => 'form-control size-field'], 
            ])
            ->add('quantity', IntegerType::class, [
                'required' => false,
                'label' => 'Quantité',
                'attr' => ['min' => 0, 'class' => 'form-control quantity-field'], 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
