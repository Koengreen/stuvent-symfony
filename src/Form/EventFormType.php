<?php

namespace App\Form;
use App\Entity\Opleiding;
use App\Entity\User;
use phpDocumentor\Reflection\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Form\RegistrationFormType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\Query\AST\OrderByItem;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class EventFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description', TextareaType::class)
            ->add('company')
            ->add('hourstype')
            ->add('eventtype', EntityType::class, [
                'class' => Opleiding::class,
                'choice_label'  =>function (Opleiding $opleiding=null){
                    return $opleiding->getName();
                }

//                    'Maybe' => null,
//                    'Yes' => true,
//                    'No' => false,
            ])
            ->add('date')
            ->add('time')
            ->add('aantalUur')
            ->add('image', FileType::class, [
                'mapped' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Event toevoegen',
                'attr'  => [
                    'class' => 'btn btn-success'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
