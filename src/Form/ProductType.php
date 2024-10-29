<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix'
            ])
            ->add('stocks', CollectionType::class, [
                'entry_type' => StockType::class,
                'required' => false,
                'allow_add' => true,
                'by_reference' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (Fichier)',
                'required' => false,
                'mapped' => false, // Ne pas mapper directement à l'entité
            ])
            ->add('isFeatured', CheckboxType::class, [
                'required' => false, // Le champ est optionnel
                'label' => 'Mettre en avant',
            ]);

        // Ajouter des stocks pour chaque taille par défaut
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $product = $event->getData();
            $form = $event->getForm();

            // Vérifie si le produit est nouveau et n'a pas encore de stocks associés
            if ($product && $product->getStocks()->isEmpty()) {
                $sizes = ['XS', 'S', 'M', 'L', 'XL'];
                foreach ($sizes as $size) {
                    $stock = new Stock();
                    $stock->setSize($size);
                    $product->getStocks()->add($stock);
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
