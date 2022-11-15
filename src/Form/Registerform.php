<?php

namespace App\Form;

use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use function Sodium\add;

class Registerform extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class , ['label' => 'Email'])
            ->add('student_number', IntegerType::class, ['label' => 'Studentnummer'])
            ->add('first_name', TextType::class, ['label' => 'Voornaam'])
            ->add('last_name', TextType::class, ['label' => 'Achternaam'])
            ->add('password', TextType::class, ['label' => 'Wachtwoord'])
            ->add('submit', SubmitType::class, ['label' => 'submit']) ;
        return $this->render("home/register.html.twig");
    }


}