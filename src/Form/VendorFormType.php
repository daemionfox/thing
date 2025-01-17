<?php

namespace App\Form;

use App\Entity\Vendor;
use App\Enumerations\TableTypeEnumeration;
use App\Enumerations\VendorAreaEnumeration;
use App\Enumerations\VendorRatingEnumeration;
use App\Enumerations\VendorStatusEnumeration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-horizontal form-control'],
                'label_attr' => ['class' => 'col-form-label col-3']
            ])
//            ->add('registrationdate')
            ->add('taxid', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-horizontal form-control'],
                'label_attr' => ['class' => 'col-form-label col-3']
            ])
            ->add('productsAndServices')
            ->add('rating', ChoiceType::class, [
                    'attr' => [
                        'class' => 'form-control', ],
                    'choices' => VendorRatingEnumeration::getList(),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false
                ]
            )
            ->add('MatureDealersSection')
            ->add('hasEndcap')
            ->add('website')
            ->add('twitter')
            ->add('tableRequestType', ChoiceType::class, [
                    'attr' => [
                        'class' => 'form-control', ],
                    'choices' => TableTypeEnumeration::getList(),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false
                ]

            )
            ->add('area', ChoiceType::class, [
                'attr' => [ 'class' => 'form-control', ],
                'choices' => VendorAreaEnumeration::getList(),
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'empty_data' => VendorAreaEnumeration::AREA_GENERAL
            ])
            ->add('seatingRequests')
            ->add('neighborRequests')
            ->add('otherRequests')
            ->add('ImageBlock')
            ->add('NumAssistants', IntegerType::class, [
                'attr' => ['class' => 'form-control',                 'min' => 0,
                    'max' => 6],

            ])
            ->add('status', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-control', ],
                'choices' => VendorStatusEnumeration::getList(),
                'expanded' => false,
                'multiple' => false,
                'required' => false
            ]

            )
            ->add('vendorContact', VendorContactType::class, [
            ])
            ->add('vendorCategories', CollectionType::class, [
                'entry_type' => VendorCategoryFormType::class,
                'entry_options' => ['label' => false],
            ])
//            ->add('vendorAddress')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vendor::class,
        ]);
    }
}
