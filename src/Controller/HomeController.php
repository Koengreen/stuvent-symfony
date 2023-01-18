<?php

namespace App\Controller;

use App\Entity\About;
use App\Entity\Klas;
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
    /**
     * This method retrieves all the students from the database and renders them in the studentoverview template
     */
    public function showallstudents(UserRepository $userRepository)
    {
        $user = $userRepository->findAll();
        return $this->render('home/studentoverview.html.twig', [
            'user' => $user
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

    #[Route('/users/klas/{naam}', name: 'usersByKlas')]
    /**
     * This method filters the users by class name and returns the filtered users
     */
    public function filterUsersByKlasAction(Klas $naam, UserRepository $userRepository)
    {
        $klas = $naam->getNaam();

        // Retrieve the class by name
        if (!$klas) {
            throw $this->createNotFoundException('No class found with name: ' . $klas);
        }
        // Retrieve the users that belong to the class
        $usersByKlas = $userRepository->findBy(['klas' => $klas]);



        if (!$usersByKlas) {
            throw $this->createNotFoundException('No users found for class: ' . $klas);
        }
        // Render the filterbyklas template
        return $this->render('home/filterbyklas.html.twig', [
            'usersByKlas' => $usersByKlas,
            'klas' => $klas,
        ]);
    }




    #[Route('/beheerder/addadmin', name: 'add_admin')]
    /**
     * This method allows a user to add an admin user
     */
    public function addadmin(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager
    ): Response
    {
        $user = new User();
        // Set the role of the user to admin
        $user->setRoles(["ROLE_ADMIN"]);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            ($form['image']->getData());
            // Handle the image upload

            $uploadedFile = $form['image']->getData();
            $destination = $this->getParameter('kernel.project_dir') . '/public/img/profile-img';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/profile-img/" . Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' .
                $uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $user->setImage($newFilename);
            // Persist the new user and flush the changes to the database
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('add_admin');
        }

        return $this->render('beheerder/addadmins.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/', name: 'blog_list')]
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
        // Get all events
        $evt = $eventRepository->findAll();
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
    /**
     * This method updates the accepted status of a user event to true
     * @param Request $request
     * @param int $id
     * @param UserEventsRepository $userEventsRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function updateIsAttending(Request $request, int $id, UserEventsRepository $userEventsRepository, EntitiesManagerInterface $entityManager): Response
    {
        // Find the user event
        $userEvent = $userEventsRepository->find($id);
        if (!$userEvent) {
            throw $this->createNotFoundException('User event not found');
        }

        // Set the accepted status to true
        $userEvent->setAccepted(true);
        // Flush the changes to the database
        $entityManager->flush();
        // Add a flash message
        $this->addFlash(
            'success',
            'Evenement geaccepteerd.');

        // Redirect to the notaccepted route
        return $this->redirectToRoute('shownotaccepted');
    }


    #[Route('/deleteevent/{id}', name: 'eventdelete')]
    /**
     * This method deletes a user event based on the provided id
     */
    public function deleteUserEvent(Request $request, int $id, UserEventsRepository $userEventsRepository, EntitiesManagerInterface $entityManager): Response
    {
        // Find the user event
        $userEvent = $userEventsRepository->find($id);
        if (!$userEvent) {
            throw $this->createNotFoundException('User event not found');
        }
        // Remove the user event from the database
        $entityManager->remove($userEvent);
        // Flush the changes to the database
        $entityManager->flush();
        // Add a flash message
        $this->addFlash(
            'danger',
            'Evenement geweigerd.'
        );
        // Redirect to the shownotaccepted route
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
            $destination = $this->getParameter('kernel.project_dir').'/public/img/profile-img';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/profile-img/".  Urlizer::urlize( $originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
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
    public function addevents(Request $request, EntitiesManagerInterface $entityManager): Response
    {
# Create a new event object
        $event = new Event ();
# Create a form using the EventFormType class and the event object
        $form = $this->createForm(EventFormType::class, $event);
# Handle the request data
        $form->handleRequest($request);
        # Check if the form has been submitted and is valid
        if ($form->isSubmitted() && $form->isValid()) {

            # Get the uploaded file from the form
            $uploadedFile = $form['image']->getData();
            # Set the destination folder for the image
            $destination = $this->getParameter('kernel.project_dir') . '/public/img/event-img';
            # Get the original file name
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            # Create a new file name for the image
            $newFilename = "img/event-img/" . Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            # Move the image to the destination folder
            $uploadedFile->move(
                $destination,
                $newFilename
            );

            # Get the selected opleiding from the form
            $opleiding = $form['opleiding']->getData();
            # Set the opleiding on the event object
            $event->setOpleiding($opleiding);
            # Set the date on the event object
            $event->setDate
            ($form['date']->getData());
            $event->setImage($newFilename);
            $entityManager->persist($event);
            $entityManager->flush();

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

        // Remove the event from the database
        $entityManager->remove($event);
        $entityManager->flush();

        // Redirect to the admin page
        return $this->redirectToRoute('app_admin', [
            'id' => $event->getId()
        ]);
    }


}


