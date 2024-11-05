<?php

namespace App\Form;

use App\Entity\Card;
use App\Entity\Combo;
use App\Entity\Related;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('combo', EntityType::class, [
                'class' => Combo::class,
                'choice_label' => 'id',
            ])
            ->add('card', EntityType::class, [
                'class' => Card::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Related::class,
        ]);
    }
}
