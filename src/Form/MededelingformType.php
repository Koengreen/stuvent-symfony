<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Mededeling;
use App\Repository\EventRepository;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;


class MededelingformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today = new \DateTime();
        $builder
            ->add('Title', TextType::class, ['label' => 'Titel'])
            ->add('Text', TextareaType::class, ['label' => 'Beschrijving'])
            ->add('file', FileType::class, [
                'label' => 'Upload file',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['application/*'],
                        'mimeTypesMessage' => 'Please upload a valid file (max 5MB).',
                        'binaryFormat' => false,
                    ]),
                ],
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'query_builder' => function (EventRepository $eventRepository) use ($today) {
                    return $eventRepository->createQueryBuilder('e')
                        ->where('e.enddate > :today')
                        ->andWhere('e.date >= :today')
                        ->setParameter('today', $today)
                        ->orderBy('e.date', 'ASC');
                },



                'choice_label' => function(Event $event) {
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $event->getDate())->format('d m  H:i');
                    $endDate = $event->getEnddate()->format('d m H:i');
                    return $event->getTitle() . ', ' . $date . ' tot ' . $endDate;
                },

                'placeholder' => 'Selecteer een evenement',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mededeling::class,
        ]);
    }
}
