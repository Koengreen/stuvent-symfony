<?php

namespace App\Controller;

use App\Entity\About;
use App\Entity\Klas;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Intl\Intl;
use App\Repository\KlasRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;
use App\Form\Aboutpageeditorform;
use App\Repository\UserEventsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\UserEvents;
use App\Form\EventFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Gedmo\Sluggable\Util\Urlizer;
use phpDocumentor\Reflection\Types\Array_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{


    private $myClass;

    public function __construct(UserEventsRepository $myClass)
    {
        $this->myClass = $myClass;
    }

    #[Route('/studentoverview', name: 'studentoverview')]
    public function showallstudents(KlasRepository $klasRepository, UserRepository $userRepository, UserEventsRepository $userEventsRepository, EventRepository $eventrepository)
    {
        $klas = $klasRepository->findAll();
        $users = $userRepository->findAll();
        $event = $eventrepository->findAll();


        // Create an empty array to store the total work hours for each student
        $totalWorkHours = [];

        // Iterate over all the students
        foreach ($users as $user) {
            // Retrieve all the UserEvents entities that belong to the current student
            $userEvents = $userEventsRepository->findBy(['user' => $user]);

            // Initialize a variable to store the total work hours for the current student
            $total = 0;

            // Iterate over the UserEvents entities
            foreach ($userEvents as $userEvent) {
                // Add the "aantalUur" property of the current UserEvents entity to the total
                $total += $userEvent->getEvent()->getAantalUur();
            }

            // Add the total work hours for the current student to the $totalWorkHours array
            $totalWorkHours[$user->getId()] = $total;
        }

        return $this->render('home/studentoverview.html.twig', [
            'users' => $users, 'klas' => $klas, 'totalWorkHours' => $totalWorkHours
        ]);
    }

    #[Route('/studentinfo/{id}', name: 'studentinfo')]
    /**
     * This method retrieves information about a specific student from the database by ID
     */
    public function showstudentinfo(ManagerRegistry $doctrine, int $id, UserEventsRepository $userEventsRepository): Response
    {
        $i = 0;
        // Retrieve the student profile by ID
        $profile = $doctrine->getRepository(User::class)->find($id);
        // Retrieve all events
        $event = $doctrine->getRepository(Event::class)->findAll();
        if (!$profile) {
            // Throw an exception if the student profile cannot be found
            throw $this->createNotFoundException(
                'no information found for id ' . $id
            );
        }
        // Retrieve the events that the student has registered for and have been accepted
        $evt = $userEventsRepository->findBy(['user' => $id, 'accepted' => true]);
        // Render the studentinfo template
        return $this->render('home/studentinfo.hmtl.twig', [
            'profile' => $profile, 'event' => $event, 'evt' => $evt,
        ]);

    }

    #[Route('/users/klas/{id}', name: 'usersByKlas')]
    /**
     * This method filters the users by class name and returns the filtered users
     */
    public function filterUsersByKlasAction(int $id,Klas $naam, UserRepository $userRepository, EventRepository $eventRepository, UserEventsRepository $userEventsRepository)
    {
        $klas = $naam->getNaam();
        // Retrieve the class by name
        if (!$klas) {
            throw $this->createNotFoundException('No class found with name: ' . $klas);
        }
        // Retrieve the users that belong to the class
        $usersByKlas = $userRepository->findBy(['klas' => $id]);
        // Render the filterbyklas template
        $totalWorkHours = [];

        // Iterate over all the students
        foreach ($usersByKlas as $user) {
            // Retrieve all the UserEvents entities that belong to the current student
            $userEvents = $userEventsRepository->findBy(['user' => $user]);

            // Initialize a variable to store the total work hours for the current student
            $total = 0;

            // Iterate over the UserEvents entities
            foreach ($userEvents as $userEvent) {
                // Add the "aantalUur" property of the current UserEvents entity to the total
                $total += $userEvent->getEvent()->getAantalUur();
            }
            // Add the total work hours for the current student to the $totalWorkHours array
            $totalWorkHours[$user->getId()] = $total;
        }
        return $this->render('home/filterbyklas.html.twig', [
            'usersByKlas' => $usersByKlas,
            'klas' => $klas,
            'totalWorkHours' => $totalWorkHours
        ]);
    }


    #[Route('/beheerder/addadmin', name: 'add_admin')]
    public function addadmin(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager
    ): Response
    {
        $user = new User();
        $user->setRoles(["ROLE_ADMIN"]);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            ($form['image']->getData());
            $uploadedFile = $form['image']->getData();
            $destination = $this->getParameter('kernel.project_dir') . '/public/img/profile-img';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/profile-img/" . Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $user->setImage($newFilename);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('add_admin');
        }

        return $this->render('beheerder/addadmins.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/pastevents', name: 'pastEvents')]
    public function pastEvents(EventRepository $eventRepository)
    {
        $em = $this->getDoctrine()->getManager();
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


    #[Route('/', name: 'blog_list')]
    /**
     * This method shows all events and the total number of attendees for each event
     */
        /**
         * This method shows all events and the total number of attendees for each event
         */
    public function show(EventRepository $eventRepository)
    {
        // Initialize an array to store the total number of attendees for each event
        $eventtotal = [];
        // Get the Doctrine Manager
        $em = $this->getDoctrine()->getManager();
        // Get the repository for the UserEvents entity
        $repoArticles = $em->getRepository(UserEvents::class);
        $today = new \DateTime();
        $qb = $eventRepository->createQueryBuilder('e');
        $evt = $qb->where($qb->expr()->gte('e.enddate', ':enddate'))
            ->setParameter('enddate', $today)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
        // Loop through each event
        foreach ($evt as $data) {
            // Get the event id
            $eventid = $data->getId();
            // Get the total number of attendees for the event
            $totalAttendees = $repoArticles->createQueryBuilder('a')
                ->andWhere('a.event = :searchTerm')
                ->setParameter('searchTerm', $eventid)
                ->getQuery()
                ->execute();
            // Count the total number of attendees
            $total = count($totalAttendees);
            // Add the event id and total number of attendees to the array
            $eventtotal[$eventid] = $total;
        }
        // Render the index template and pass the events and total attendees array
        return $this->render('home/index.html.twig', [
            'evt' => $evt, 'totalAttendees' => $eventtotal,
        ]);
    }


    #[Route('/notaccepted', name: 'shownotaccepted')]
    /**
     * This method shows all events that have not been accepted by the admin
     */
    public function userEventsNotAttending()
    {
        // Get all events that have not been accepted
        $userEventsNotAttending = $this->myClass->getUserEventsNotAttending();

        // Render the notattending template and pass the not accepted events
        return $this->render('home/notattending.html.twig', [
            'queu_events' => $userEventsNotAttending,
        ]);
    }


    #[Route('/acceptevent/{id}', name: 'eventupdateaccepted',)]
    public function updateIsAttending(Request $request, int $id, UserEventsRepository $userEventsRepository, EntityManagerInterface $entityManager): Response
    {
        $userEvent = $userEventsRepository->find($id);
        if (!$userEvent) {
            throw $this->createNotFoundException('User event not found');
        }

        $userEvent->setAccepted(true);
        $entityManager->flush();
        $this->addFlash(
            'success',
            'Evenement geaccepteerd.');

        return $this->redirectToRoute('shownotaccepted');
    }


    #[Route('/deleteevent/{id}', name: 'eventdelete')]
    public function deleteUserEvent(Request $request, int $id, UserEventsRepository $userEventsRepository, EntityManagerInterface $entityManager): Response
    {
        $userEvent = $userEventsRepository->find($id);
        if (!$userEvent) {
            throw $this->createNotFoundException('User event not found');
        }
        $entityManager->remove($userEvent);
        $entityManager->flush();
        $this->addFlash(
            'danger',
            'Evenement geweigerd.'
        );


        return $this->redirectToRoute('shownotaccepted');
    }


    #[Route('/myprofile/{id}', name: 'myProfile')]
    public function myProfile(ManagerRegistry $doctrine, int $id, UserEventsRepository $userEventsRepository, #[CurrentUser] $user = null): Response
    {
        $i = 0;
        try {
            if (!$user) {
                throw new Exception("User not found.");
            }
            $profile = $doctrine->getRepository(User::class)->find($id);
            $event = $doctrine->getRepository(Event::class)->findAll();
            if (!$profile) {
                throw new Exception("Profile not found.");
            }
            $userid = $user->getId();
            $evt = $userEventsRepository->findBy(['user' => $userid, 'accepted' => true]);
            return $this->render('home/myprofile.html.twig', [
                'profile' => $profile, 'event' => $event, 'evt' => $evt,
                'i' => $i,
            ]);
        } catch (Exception $e) {
            return $this->redirectToRoute('app_login');
        }
    }


    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render("home/about.html.twig");
    }


    #[Route('/about/edit', name: 'aboutedit')]
    public function editabout(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {

        $about = new About();
        $form = $this->createForm(Aboutpageeditorform::class, $about);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($form['images']->getData());
            $uploadedFile = $form['images']->getData();
            $destination = $this->getParameter('kernel.project_dir') . '/public/img/aboutpage';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/event-img/" . Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $about->setImages($newFilename);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('about');
        }

        return $this->render('admin/editabout.html.twig', [
            'aboutform' => $form->createView(),
        ]);
    }


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setRoles(["ROLE_USER"]);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            ($form['image']->getData());
            $uploadedFile = $form['image']->getData();
            $destination = $this->getParameter('kernel.project_dir') . '/public/img/profile-img';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/profile-img/" . Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $user->setImage($newFilename);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('blog_list');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('{id}', name: 'more_info')]
    public function showInfo(ManagerRegistry $doctrine, int $id): Response
    {
# fetch the event by id from the repository
        $evt = $doctrine->getRepository(Event::class)->find($id);
#if event not found, throw exception
        if (!$evt) {
            throw $this->createNotFoundException(
                'no information found for id ' . $id
            );
        }
#render the info page with the event data
        return $this->render('home/info.html.twig', [
            'evt' => $evt,
        ]);
    }

    #[Route('/enroll/{id}', name: 'enroll')]
    public function enroll(ManagerRegistry $doctrine, Event $event, #[CurrentUser] $user, UserEventsRepository $userEvents, int $id): Response
    {
        #check if the event is duedate
        #check if the user is already enrolled in the event
        $existingEnrollment = $userEvents->findOneBy(['event' => $event, 'user' => $user]);
        if ($existingEnrollment) {
            $this->addFlash('error', 'U heeft zich al ingeschreven');
            return $this->redirectToRoute('blog_list');
        }
        #check if the event is full
        $message = 'Evenement is vol.';
        $totalAttendees = $event->getAttendees();
        $eventId = $event->getId();
        $inschrijvingen = $userEvents->findby(['event' => $eventId]);
        #if there is space in the event, enroll user
        if (count($inschrijvingen) < $totalAttendees) {
            $entityManager = $doctrine->getManager();
            $userevent = new UserEvents();
            $userevent->setEvent($event);
            $userevent->setUser($user);
            $userevent->setAccepted(false);
            $entityManager->persist($userevent);
            $entityManager->flush();
            $this->addFlash('success', 'inschrijving succesvol');
        } else {
            $this->addFlash('error', $message);
        }
        #redirect to the blog list
        return $this->redirectToRoute('blog_list');
    }

    #[Route('/admin/add', name: 'add_events')]
    public function addevents(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event ();
        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($form['image']->getData());
            $uploadedFile = $form['image']->getData();
            $destination = $this->getParameter('kernel.project_dir') . '/public/img/event-img';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/event-img/" . Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $opleiding = $form['opleiding']->getData();
            $event->setOpleiding($opleiding);
            $event->setDate($form['date']->getData());
            $event->setImage($newFilename);
            $entityManager->persist($event);
            $entityManager->flush();


            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/addevents.html.twig', [
            'eventform' => $form->createView(),
        ]);
    }

    #[Route('/admin/{id}/remove/', name: 'event_delete')]
    public function Remove(ManagerRegistry $doctrine, int $id): Response
    {
        // Get the entity manager to handle database operations
        $entityManager = $doctrine->getManager();

        // Find the event with the given id
        $event = $entityManager->getRepository(Event::class)->find($id);

        // Find all the user events that reference this event
        $userEvents = $entityManager->getRepository(UserEvents::class)->findBy(['event' => $event]);

// Remove all the dependent user events
        foreach ($userEvents as $userEvent) {
            $entityManager->remove($userEvent);
        }

        // Remove the event from the database
        $entityManager->remove($event);
        $entityManager->flush();

        // Redirect to the admin page
        return $this->redirectToRoute('app_admin', [
            'id' => $event->getId()
        ]);
    }


}


