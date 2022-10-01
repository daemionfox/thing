<?php

namespace App\Form;

use App\Entity\User;
use App\Enumerations\RoleEnumeration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditStaffFormType extends RegistrationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-horizontal form-control'],
                'label_attr' => ['class' => 'col-form-label col-3']
            ])
            ->add('name', TextType::class, ['required' => true])
            ->add('title')
            ->add('roles', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-control', ],
                'choices' => RoleEnumeration::getList(),
                'expanded' => true,
                'multiple' => true,
                'required' => false
            ]);
        ;
    }

}
