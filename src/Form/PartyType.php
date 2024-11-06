<?php

namespace App\Form;

use App\Entity\Party;
use App\Entity\user;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('turn')
            ->add('code')
            ->add('winner', EntityType::class, [
                'class' => User::class,
                'choice_value' => 'id',
                'choice_label' => 'id', // affiche l'ID comme étiquette si nécessaire
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Party::class,
            'csrf_protection' => false,  // Désactive la protection CSRF pour les requêtes JSON
            'allow_extra_fields' => true // Permet les champs supplémentaires
        ]);
    }
}
