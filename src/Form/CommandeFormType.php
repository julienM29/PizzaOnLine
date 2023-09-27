<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Etat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentDateTime = new \DateTime();

        $builder
//            ->add('dateHeureLivraison', DateTimeType::class, array(
//                'widget' => 'single_text',
//                'attr' => ['min' => $currentDateTime->format('Y-m-d\TH:i')],
//            ))
//            ->add('dateHeurePreparation', DateTimeType::class, array(
//                'widget' => 'single_text',
//                'attr' => ['min' => $currentDateTime->format('Y-m-d\TH:i')],
//            ))
//            ->add('etat', EntityType::class, [
//                'class' => Etat::class,
//                'choice_label' => 'libelle'
//            ])
            ->add('adresse')
            ->add('ajouter', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
