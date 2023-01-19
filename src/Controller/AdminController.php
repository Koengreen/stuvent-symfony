<?php

namespace App\Controller;

use App\Entity\UserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\Query\AST\OrderByItem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Test\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function adminEvents(EventRepository $eventRepository)
    { $em = $this->getDoctrine()->getManager();
        $usereventRepository = $em->getRepository(UserEvents::class);
        $qb = $usereventRepository->createQueryBuilder('ue');
        $qb->select('count(ue.id)')
            ->where('ue.accepted = false');
        $notAccepted = $qb->getQuery()->getSingleScalarResult();
        // Get all events sorted by date and time
        $today = new \DateTime();
        $qb = $eventRepository->createQueryBuilder('e');
        $evt = $qb->where($qb->expr()->gte('e.enddate', ':enddate'))
            ->setParameter('enddate', $today)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
        return $this->render('admin/index.html.twig', [
            'evt' => $evt, 'notAccepted' => $notAccepted
        ]);
    }

    #[Route('/admin/pastevents', name: 'pastEvents')]
    public function pastEvents(EventRepository $eventRepository)
    { $em = $this->getDoctrine()->getManager();
        $usereventRepository = $em->getRepository(UserEvents::class);
        $qb = $usereventRepository->createQueryBuilder('ue');
        $qb->select('count(ue.id)')
            ->where('ue.accepted = false');
        $notAccepted = $qb->getQuery()->getSingleScalarResult();
        // Get all events sorted by date and time
        $today = new \DateTime();
        $qb = $eventRepository->createQueryBuilder('e');
        $evt = $qb->where($qb->expr()->lt('e.enddate', ':enddate'))
            ->setParameter('enddate', $today)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
        return $this->render('admin/pastevents.html.twig', [
            'evt' => $evt, 'notAccepted' => $notAccepted
        ]);
    }


}





