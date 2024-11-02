<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom :'
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix :'
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('isFeatured', CheckboxType::class, [
                'required' => false,
                'label' => 'Mettre en avant :',
                'label_attr' => ['class' => 'checkbox-label'], // Classe pour le label
                'attr' => ['class' => 'checkbox-input'],
            ])
            ->add('stocks', CollectionType::class, [
                'entry_type' => StockType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false, 
                'mapped' => true, 
                'by_reference' => false,
                'attr' => ['class' => 'stocks'],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
