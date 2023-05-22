<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Opleiding;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;


class Eventtype extends AbstractType
{

    public function transform($value)
    {
        if (!$value instanceof \DateTime) {
            return '';
        }

        return $value->format('Y-m-d H:i:s');
    }

    public function reverseTransform($value)
    {
        try {
            $datetime = new \DateTime($value);
        } catch (\Exception $e) {
            throw new TransformationFailedException('Invalid date format');
        }

        return $datetime;
    }


public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description', TextareaType::class)
            ->add('company')
            ->add('opleiding', EntityType::class, [
                'class' => Opleiding::class,
                'choice_label'  =>function (Opleiding $opleiding)
                {
                    return $opleiding->getName();
                }
            ])
            ->add('niveau', ChoiceType::class, [
                'choices' => [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => "Advanced",
                ],
            ])
            ->add('attendees' )
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime'
            ])
            ->add('enddate', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime'
            ])
            ->add('concomitance')
            ->add('aantalUur')
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Update Event',
                'attr'  => [
                    'class' => 'btn btn-success'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }

}