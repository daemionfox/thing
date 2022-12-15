<?php

namespace App\Form;

use App\Entity\VoteItem;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteVendorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $attr = [
            'class' => 'form-control col-1 vendor-vote',
            'min' => 1,
            'default' => 0
        ];
        /**
         * @var VoteItem $voteItem
         */
        $voteItem = $builder->getData();
        $max = $voteItem->getMaxVotes();

        if (!empty($max)) {
            $attr['max'] = $max;
        }


        $builder
            ->add('Votes', IntegerType::class, [
                'attr' => $attr,
                'label_attr' => ['class' => 'col-1 vendor-vote-label']
            ])
            ->add('isSkip', HiddenType::class, [
                    'attr'=> ['default' => false]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VoteItem::class,
        ]);
    }
}
