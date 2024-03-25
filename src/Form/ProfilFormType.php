<?php

namespace App\Form;

use App\Entity\Collaborateur;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password')
            ->add('nom')
            ->add('prenom')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'USER' => 'ROLE_USER',
                    'GERANT' => 'ROLE_GERANT',
                    'LIVREUR' => 'ROLE_LIVREUR',
                    'PIZZAIOLO' => 'ROLE_PIZZAIOLO',
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'block_prefix' => 'custom_roles_widget',
            ])
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Masculin' => 'Masculin',
                    'Féminin' => 'Féminin',
                    'Autre' => 'Autre',
                ],
                'placeholder' => 'Sélectionnez votre sexe',
            ])
            ->add('ajouter', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Collaborateur::class,
        ]);
    }
}
