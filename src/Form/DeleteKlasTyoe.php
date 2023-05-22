<?php

namespace App\Form;

use App\Entity\Klas;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteKlasTyoe extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('klas_id', ChoiceType::class, [
                'label' => 'Kies een klas:',
                'choices' => $options['klasList'],
                'choice_label' => 'naam',
                'choice_value' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('klasList');
        $resolver->setAllowedTypes('klasList', 'array');
    }

}