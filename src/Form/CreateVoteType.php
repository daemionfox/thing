<?php

namespace App\Form;

use App\Entity\VoteEvent;
use App\Enumerations\TableCategoryEnumeration;
use Doctrine\DBAL\Types\DateTimeTzType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateVoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, [
                'help' => 'Example: 2023 Round 1 Vote'
            ])
            ->add('StaffVotes', NumberType::class, [
                'help' => 'Number of Votes assigned to each staff member'
            ])
            ->add('MaxVendorVotes', NumberType::class, [
                'help' => 'Maximum number of votes a staff member can give to a single vendor.  Leave blank for no max'
            ])
            ->add ('TableCategory', ChoiceType::class, [
                'help' => 'Only vote for vendors within a specific table category',
                'attr' => ['class' => 'form-select', ],
                'choices' => TableCategoryEnumeration::getList(),
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'label' => false
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VoteEvent::class,
        ]);
    }
}
