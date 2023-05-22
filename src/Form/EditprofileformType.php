<?php

namespace App\Form;

use App\Entity\Klas;
use App\Entity\Opleiding;
use App\Entity\User;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditprofileformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('opleiding', EntityType::class, [
                'class' => Opleiding::class,
                'choice_label'  =>function (Opleiding $opleiding){
                    return $opleiding->getName();
                }

//                    'Maybe' => null,
//                    'Yes' => true,
//                    'No' => false,
            ])
            ->add('email')
            ->add('telefoonnummer')
            ->add('studentNumber')
            ->add('klas', EntityType::class, [
                'class' => Klas::class,
                'choice_label'  =>function (Klas $klas){
                    return $klas->getNaam();
                }
            ])
            ->add('firstName')
            ->add('lastName');


    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
