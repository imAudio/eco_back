<?php

namespace App\Form;

use App\Entity\Card;
use App\Entity\Hand;
use App\Entity\Party;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('card', EntityType::class, [
                'class' => Card::class,
                'choice_label' => 'id',
            ])
            ->add('party', EntityType::class, [
                'class' => Party::class,
                'choice_label' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hand::class,
            'csrf_protection' => false,  // Désactive la protection CSRF pour les requêtes JSON
            'allow_extra_fields' => true // Permet les champs supplémentaires
        ]);
    }
}
