<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Stock;
use App\Form\StockType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
                'label_attr' => ['class' => 'checkbox-label'], 
                'attr' => ['class' => 'checkbox-input'],
            ])
            ->add('stocks', CollectionType::class, [
                'entry_type' => StockType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true, 
                'allow_delete' => true,
                'prototype' => true,
                'label' => false,
                'attr' => ['class' => 'stocks2'],
            ]);
            
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $product = $event->getData();
        
            if ($product) {
                // Définir les tailles disponibles
                $sizes = ['XS', 'S', 'M', 'L', 'XL'];
                foreach ($sizes as $size) {
                    // Vérifie si un stock existe déjà pour cette taille
                    $existingStock = $product->getStocks()->filter(function (Stock $stock) use ($size) {
                        return $stock->getSize() === $size;
                    })->first();
        
                    if (!$existingStock) {
                        // Si aucun stock n'existe, créer un nouveau
                        $stock = new Stock();
                        $stock->setSize($size); 
                        $stock->setQuantity(0); 
                        $stock->setProduct($product);
                        $product->getStocks()->add($stock);
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
