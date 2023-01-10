<?php

namespace App\Form;

use App\Entity\Vendor;
use App\Enumerations\MessageIconEnumeration;
use App\Enumerations\VendorStatusEnumeration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateVendorStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'attr' => ['class' => 'form-select', ],
                'choices' => VendorStatusEnumeration::getList(),
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'label' => false
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-outline-secondary'],
                'label' => '<i class="fas fa-check-square"></i>',
                'label_html'=> true,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vendor::class,
        ]);
    }
}
