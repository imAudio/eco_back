<?php

namespace App\Form;

use App\Entity\Card;
use App\Entity\Party;
use App\Entity\River;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('party', EntityType::class, [
                'class' => Party::class,
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
            'data_class' => River::class,
            'csrf_protection' => false,  // Désactive la protection CSRF pour les requêtes JSON
            'allow_extra_fields' => true // Permet les champs supplémentaires
        ]);
    }
}