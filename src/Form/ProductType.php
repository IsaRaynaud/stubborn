<?php

namespace App\Form;

use App\Entity\Product;
use App\Form\ProductVariantType;
use App\Form\DataTransformer\CentimeToEuroTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit : ',
            ])
            ->add('price', TextType::class, [
                'label' => 'Prix (en euros) : ',

            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit : ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, WebP) de 2Mo maximum.',
                    ])

                ],
            ])
            ->add('isFeatured', checkboxType::class, [
                'label' => 'Mettre en avant ? ',
                'required' => false,
            ])
            ->add('stockXS', IntegerType::class, [
                'mapped' => false,
                'label' => 'Stock XS : ',
                'required' => false,
            ])
            ->add('stockS', IntegerType::class, [
                'mapped' => false,
                'label' => 'Stock S : ',
                'required' => false,
            ])
            ->add('stockM', IntegerType::class, [
                'mapped' => false,
                'label' => 'Stock M : ',
                'required' => false,
            ])
            ->add('stockL', IntegerType::class, [
                'mapped' => false,
                'label' => 'Stock L : ',
                'required' => false,
            ])
            ->add('stockXL', IntegerType::class, [
                'mapped' => false,
                'label' => 'Stock XL : ',
                'required' => false,
            ]);

        $builder->get('price')
            ->addModelTransformer(new CentimeToEuroTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'product', 
        ]);
    }
}
