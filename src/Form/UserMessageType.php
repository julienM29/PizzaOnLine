<?php

namespace App\Form;

use App\Entity\UserMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sujet')
            ->add('message', TextareaType::class,
            [
                'label' => 'Votre message',
                'required' => true,
                'attr' => ['rows' => 10],
            ])
            ->add('Envoyer', SubmitType::class)
//            ->add('prenom')
//            ->add('nom')
//            ->add('checked')
//            ->add('userId')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserMessage::class,
        ]);
    }
}
