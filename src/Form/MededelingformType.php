<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Mededeling;
use App\Repository\EventRepository;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class MededelingformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today = new \DateTime();
        $builder
            ->add('Title')
            ->add('Text')
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
