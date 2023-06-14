<?php

namespace App\Controller;

use App\Entity\Mededeling;
use App\Entity\Opleiding;
use App\Form\AddclassformType;
use App\Form\DeleteKlasTyoe;
use App\Form\EditEventForm;
use App\Form\EditEventImage;
use App\Form\EditprofileformType;
use App\Form\EventDateRangeForm;
use App\Form\EventType;
use App\Form\KlasDeleteFormType;
use App\Form\MededelingformType;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use phpDocumentor\Reflection\Types\Nullable;
use Symfony\Component\Form\FormTypeInterface;
use App\Entity\About;
use App\Entity\Klas;
use App\Entity\User;
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
use App\Form\RegistrationFormType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    private $mailer;

    public function __constructmail(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('koen.green1@gmail.com')
            ->to('koengreen2002@gmail.com')
            ->subject('Test email')
            ->text('Je inschrjving is verwijderd.');

        $this->mailer->send($email);
    }





    private $myClass;

    public function __construct(UserEventsRepository $myClass)
    {
        $this->myClass = $myClass;
    }

    #[Route('/download/{filename}', name: 'download_medelink')]
    public function downloadMedelink(string $filename): BinaryFileResponse
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/img/profile-img/' . $filename;

        return new BinaryFileResponse($filePath, 200, [], true, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    #[Route('/add/Mededeling', name: 'createMededeling')]
    public function createMededeling(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $today = new \DateTime();
        $mededeling = new Mededeling();
        $mededeling->setDate($today);

        $form = $this->createForm(MededelingFormType::class, $mededeling);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile instanceof UploadedFile) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/img/profile-img';
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                try {
                    $uploadedFile->move(
                        $destination,
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle file upload error
                    // You can add appropriate error handling based on your application's needs
                }

                $mededeling->setFile($newFilename);
            }

            $entityManager->persist($mededeling);
            $entityManager->flush();

            // Redirect to the student overview page
            return $this->redirectToRoute('blog_list');
        }

        return $this->render('home/addmededeling.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/enrollklas', name: 'enrollklas')]
    public function enrollklas(Request $request, UserRepository $userRepository, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $userEvents = [];
        $today = new \DateTime();

        // Create a form to select the Klas and Event
        $form = $this->createFormBuilder()
            ->add('klas', EntityType::class, [
                'class' => Klas::class,
                'choice_label' => 'naam',
                'placeholder' => 'Selecteer een klas',
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
                'choice_label' => 'title',
                'placeholder' => 'Selecteer een evenement',
            ])
            ->add('submit', SubmitType::class, ['label' => 'Inschrijven'])
            ->getForm();

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $enrolled_users = [];

            // Get all the UserEvents for the selected event
            $event_id = $data['event']->getId();
            $user_events = $entityManager->getRepository(UserEvents::class)->findBy(['event' => $event_id]);

            // Store the enrolled users in an array
            foreach ($user_events as $user_event) {
                $enrolled_users[] = $user_event->getUser()->getId();
            }

            // Enroll all users from the selected Klas for the selected Event, if they are not already enrolled
            foreach ($data['klas']->getUser() as $user) {
                if (!in_array($user->getId(), $enrolled_users)) {
                    $userEvent = new UserEvents();
                    $userEvent->setUser($user);
                    $userEvent->setEvent($data['event']);
                    $userEvent->setAccepted(true);
                    $userEvent->setPresence(null);
                    $userEvent->setRating('-');
                    $entityManager->persist($userEvent);
                    $userEvents[] = $userEvent;
                }
            }

            $entityManager->flush();

            // Redirect to the student overview page
            return $this->redirectToRoute('studentoverview');
        }

        return $this->render('admin/enrollklas.html.twig', [
            'form' => $form->createView(),
        ]);
    }





    #[Route('/enrollstudents', name: 'enrollstudents')]
    public function enrollstudents(Request $request, UserRepository $userRepository, EventRepository $eventRepository, EntityManagerInterface $entityManager)
    {
        // Create a new UserEvents entity
        $userEvent = new UserEvents();
        $userEvent->setAccepted(true);
        $userEvent->setRating('-');
        $today = new \DateTime();


        // Create a form to select the user and event
        // Create a form to select the user and event
        $form = $this->createFormBuilder($userEvent)
            ->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $userRepository) {
                    return $userRepository->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_USER%');
                },
                'choice_label' => function(User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'placeholder' => 'Selecteer een leerling',
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
            ->add('save', SubmitType::class, ['label' => 'Inschrijven'] )
            ->getForm();


        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userEvent->setPresence(null);
            $entityManager->persist($userEvent);
            $entityManager->flush();

            // Redirect to the student overview page
            return $this->redirectToRoute('studentoverview');
        }

        return $this->render('home/enrollstudents.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/studentoverview', name: 'studentoverview')]
    public function showallstudents(KlasRepository $klasRepository, UserRepository $userRepository, UserEventsRepository $userEventsRepository, EventRepository $eventrepository)
    {
        $klas = $klasRepository->findBy([], ['naam' => 'ASC']);
        $users = $userRepository->findAll();
        $event = $eventrepository->findAll();


        // Create an empty array to store the total work hours for each student
        $totalWorkHours = [];

        // Iterate over all the students
        foreach ($users as $user) {
            // Retrieve all the UserEvents entities that belong to the current student
            $userEvents = $userEventsRepository->findBy(['user' => $user, 'accepted' => true]);
            $presentuserEvents = $userEventsRepository->findBy(['user' => $user, 'accepted' => true, 'presence' => true]);

            // Initialize a variable to store the total work hours for the current student
            $total = 0;

            // Iterate over the UserEvents entities
            foreach ($presentuserEvents as $userEvent) {
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

    #[Route('/studentoverview/delete/{id}', name: 'delete_user', methods: ['POST'])]
    public function deleteUser($id, UserRepository $userRepository, UserEventsRepository $userEventsRepository, EntityManagerInterface $entityManager)
    {
        // Retrieve the user entity with the given id
        $user = $userRepository->find($id);

        // If the user entity does not exist, redirect to the student overview page
        if (!$user) {
            return $this->redirectToRoute('studentoverview');
        }

        // Delete all the UserEvents entities that belong to the user
        $userEvents = $userEventsRepository->findBy(['user' => $user]);
        foreach ($userEvents as $userEvent) {
            $entityManager->remove($userEvent);
        }

        // Delete the user entity
        $entityManager->remove($user);
        $entityManager->flush();

        // Redirect to the student overview page
        return $this->redirectToRoute('studentoverview');
    }


    #[Route('/change-password/{id}', name: 'change_password')]
    public function changePassword(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, int $id): Response
    {
        // Find the user by ID
        $user = $userRepository->find($id);

        // Check if the user exists
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Create a password change form
        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class, [
                'label' => 'New Password',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6]),
                ],
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirm New Password',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6]),
                ],
            ])
            ->getForm();

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the submitted data
            $data = $form->getData();

            // Encode the new password and set it on the user object
            $encodedPassword = $passwordEncoder->encodePassword($user, $data['password']);
            $user->setPassword($encodedPassword);

            // Save the updated user object
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Redirect to a success page
            return $this->redirectToRoute('studentinfo', ['id' => $id]);
        }

        // Render the password change form
        return $this->render('home/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/change-mypassword/{id}', name: 'change_mypassword')]
    public function changemyPassword(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, int $id): Response
    {
        // Find the user by ID
        $user = $userRepository->find($id);

        // Check if the user exists
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Create a password change form
        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class, [
                'label' => 'New Password',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6]),
                ],
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirm New Password',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6]),
                ],
            ])
            ->getForm();

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the submitted data
            $data = $form->getData();

            // Encode the new password and set it on the user object
            $encodedPassword = $passwordEncoder->encodePassword($user, $data['password']);
            $user->setPassword($encodedPassword);

            // Save the updated user object
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Redirect to a success page
            return $this->redirectToRoute('yourprofile', ['id' => $id]);
        }

        // Render the password change form
        return $this->render('home/change_password.html.twig', [
            'form' => $form->createView(),
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
        $userId=$profile->getId();
        $userEvents = $userEventsRepository->findBy(['user' => $userId, 'accepted' => true]);
        $presentuserEvents = $userEventsRepository->findBy(['user' => $userId, 'accepted' => true, 'presence' => true]);
        $totalWorkHours = 0;
        foreach ($presentuserEvents as $userEvent) {
            // Add the "aantalUur" property of the current UserEvents entity to the total
            $totalWorkHours += $userEvent->getEvent()->getAantalUur();
        }
        // Retrieve the events that the student has registered for and have been accepted
        $evt = $userEventsRepository->findBy(['user' => $id, 'accepted' => true]);
        // Render the studentinfo template
        return $this->render('home/studentinfo.hmtl.twig', [
            'profile' => $profile, 'event' => $event, 'evt' => $evt,
            'totalWorkHours' => $totalWorkHours,
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

    #[Route('/admin/unchecked-presences', name: 'unchecked_presences')]
    public function uncheckedPresences(EntityManagerInterface $em, EventRepository $eventRepository)
    {
        $evt = $eventRepository->findAll();

        $userEvents = UserEvents::getUncheckedPresences($em);

        return $this->render('admin/unchecked_presences.html.twig', [
            'userEvents' => $userEvents,
            'evt' => $evt,

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
    public function show(EventRepository $eventRepository, UserRepository $userRepository,  #[CurrentUser] $currentUser = null)
    {
        $eventtotal = [];
            if (!$currentUser || in_array('ROLE_ADMIN', $currentUser->getRoles()) || in_array('ROLE_beheerder', $currentUser->getRoles())) {
                $today = new \DateTime();

                $evt = $eventRepository->createQueryBuilder('e')
                    ->where('e.enddate > :currentDateTime')
                    ->setParameter('currentDateTime', $today)
                    ->orderBy('e.date', 'ASC')
                    ->getQuery()
                    ->getResult();
            $em = $this->getDoctrine()->getManager();
            $repoArticles = $em->getRepository(UserEvents::class);

            $qb = $eventRepository->createQueryBuilder('e');

        }
        else{
        // Get the current user's "Opleiding" value
        $eventopleiding = $currentUser->getOpleiding();
        // Initialize an array to store the total number of attendees for each event

        // Get the Doctrine Manager
        $em = $this->getDoctrine()->getManager();
        // Get the repository for the UserEvents entity
        $repoArticles = $em->getRepository(UserEvents::class);
        $today = new \DateTime();
        $qb = $eventRepository->createQueryBuilder('e');
            $evt = $qb->where($qb->expr()->eq('e.opleiding', ':opleiding_id'))
                ->orWhere($qb->expr()->eq('e.opleiding', ':default_opleiding_id'))
                ->andWhere('e.enddate > :currentDateTime')
                ->setParameter('opleiding_id', $eventopleiding->getId())
                ->setParameter('default_opleiding_id', 11) // <-- set default opleiding id here
                ->setParameter('currentDateTime', $today)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
        }
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
            throw $this->createNotFoundException('Inschrijving niet gevonden!');
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
        $entityManager->remove($userEvent);
        $entityManager->flush();
        $this->addFlash(
            'danger',
            'Evenement geweigerd.'
        );


        return $this->redirectToRoute('shownotaccepted');
    }


    #[Route("/klas/delete", name: "klas_delete")]
    public function deleteKlas(Request $request, EntityManagerInterface $entityManager)
    {
        $klasRepository = $entityManager->getRepository(Klas::class);
        $klasList = $klasRepository->findAll();

        $form = $this->createForm(DeleteKlasTyoe::class, null, [
            'klasList' => $klasList
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $klasId = $form->getData()['klas_id'];
            $klas = $klasRepository->find($klasId);

            if ($klas) {
                // Set the klas property of associated users to null
                $users = $klas->getUser()->toArray();
                foreach ($users as $user) {
                    $user->setKlas(null);
                    $entityManager->persist($user);
                }
                $entityManager->flush();

                // Delete the klas entity
                $entityManager->remove($klas);
                $entityManager->flush();

                $this->addFlash('success', 'Klas "'.$klas->getNaam().'" is verwijderd.');
                return $this->redirectToRoute('studentoverview');
            }
        }

        return $this->render('beheerder/klasdelete.html.twig', [
            'klasList' => $klasList,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/aanwezigheid', name: 'aanwezigheid')]
    /**
     * This method shows all events that have not been accepted by the admin
     */
    public function checkpresences()
    {
        // Get all events that have not been accepted
        $userEventsNotAttending = $this->myClass->getUserEventspresence();

        // Sort the events by end date
        usort($userEventsNotAttending, function($a, $b) {
            return $a->getEvent()->getEndDate() <=> $b->getEvent()->getEndDate();
        });

        // Render the notattending template and pass the not accepted events
        return $this->render('admin/checkpresences.html.twig', [
            'queu_events' => $userEventsNotAttending,
        ]);
    }



    #[Route('/setabsent/{id}', name: 'absentuser')]
    public function absentuser(Request $request, int $id, UserEventsRepository $userEventsRepository, EntityManagerInterface $entityManager): Response
    {
        $userEvent = $userEventsRepository->find($id);
        if (!$userEvent) {
            throw $this->createNotFoundException('Inschrijving niet gevonden!');
        }
        $userEvent->setPresence(false);
        $entityManager->flush();
        $this->addFlash(
            'danger',
            'Gebruiker absent gezet'
        );
        return $this->redirectToRoute('aanwezigheid');
    }


    #[Route('/setpresent/{id}', name: 'setpresent')]
    public function setpresent(Request $request, int $id, UserEventsRepository $userEventsRepository, EntityManagerInterface $entityManager): Response
    {
        $userEvent = $userEventsRepository->find($id);
        if (!$userEvent) {
            throw $this->createNotFoundException('Inschrijving niet gevonden!');
        }

        $rating = $request->request->get('rating');
        $userEvent->setRating($rating);


        $userEvent->setPresence(true);

        $entityManager->persist($userEvent);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Aanwezig gezet');

        return $this->redirectToRoute('aanwezigheid');
    }













        #[Route('/yourprofile/{id}', name: 'yourprofile')]
    public function myProfile(ManagerRegistry $doctrine, int $id, UserEventsRepository $userEventsRepository, #[CurrentUser] $user = null): Response
    {


        if (!$user) {
            throw new Exception("Gebruiker niet gevonden!.");
        }
        $profile = $doctrine->getRepository(User::class)->find($id);
        $event = $doctrine->getRepository(Event::class)->findAll();
        if (!$profile) {
            throw new Exception("Profiel niet gevonden!.");
        }
        $userId = $user->getId();
        $userEvents = $userEventsRepository->findBy(['user' => $userId, 'accepted' => true, ]);
        $presentUserEvents = $userEventsRepository->findBy(['user' => $userId, 'accepted' => true, 'presence' => true ]);

        // Initialize a variable to store the total work hours for the current user
        $totalWorkHours = 0;

        // Iterate over the UserEvents entities
        foreach ($presentUserEvents as $userEvent) {
            // Add the "aantalUur" property of the current UserEvents entity to the total
            $totalWorkHours += $userEvent->getEvent()->getAantalUur();
        }

        return $this->render('home/myprofile.html.twig', [
            'profile' => $profile, 'event' => $userEvents, 'evt' => $userEvents,
            'totalWorkHours' => $totalWorkHours,
        ]);
        }




    #[Route('/editprofile/{id}', name: 'editprofile')]
    public function editprofile(Request $request, ManagerRegistry $doctrine, int $id, UserEventsRepository $userEventsRepository, #[CurrentUser] $user = null): Response
    {
        if (!$user) {
            throw new Exception("Gebruiker niet gevonden!.");
        }

        $profile = $doctrine->getRepository(User::class)->find($id);
        if (!$profile) {
            throw new Exception("Profiel niet gevonden!.");
        }

        $form = $this->createForm(EditprofileformType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();

            // Handle image upload

            $entityManager->persist($profile);
            $entityManager->flush();

            $this->addFlash('success', 'Profiel succesvol bijgewerkt!');

            return $this->redirectToRoute('yourprofile', ['id' => $id]);
        }

        $userId = $user->getId();
        $userEvents = $userEventsRepository->findBy(['user' => $userId, 'accepted' => true]);

        // Initialize a variable to store the total work hours for the current user
        $totalWorkHours = 0;

        // Iterate over the UserEvents entities
        foreach ($userEvents as $userEvent) {
            // Add the "aantalUur" property of the current UserEvents entity to the total
            $totalWorkHours += $userEvent->getEvent()->getAantalUur();
        }

        return $this->render('home/editprofile.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
            'event' => $userEvents,
            'userEvents' => $userEvents,
            'totalWorkHours' => $totalWorkHours,
        ]);
    }








    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render("home/about.html.twig");
    }

     #[Route("/set_role_admin/{userId}", name: "set_role_admin")]

    public function setRoleAdmin($userId, UserRepository $userRepository)
    {
        $user = $userRepository->find($userId);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Set the ROLE_ADMIN role
        $user->setRoles(['ROLE_ADMIN']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('studentoverview');
    }


    #[Route("/eventsbydate", name: "eventsbydate")]
    public function eventsbydate(Request $request, EventRepository $eventRepository)
    {
        $form = $this->createForm(EventDateRangeForm::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateRange = $form->getData();

            $startDate = $dateRange->getStartDate();
            $endDate = $dateRange->getEndDate();

            $events = $eventRepository->getEventsByDateRange($startDate, $endDate);

            return $this->render('home/eventsbydate.html.twig', [
                'form' => $form->createView(),
                'events' => $events,
            ]);
        }

        return $this->render('home/eventsbydate.html.twig', [
            'form' => $form->createView(),
            'events' => [],
        ]);
    }

    #[Route("/set_role_user/{userId}", name: "set_role_user")]
    public function setRoleUser($userId, UserRepository $userRepository)
    {
        $user = $userRepository->find($userId);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Set the ROLE_ADMIN role
            $user->setRoles(['ROLE_USER']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('Showteachers');
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

    #[Route('/admin/pastevents', name: 'adminpastEvents')]
    public function adminpastEvents(EventRepository $eventRepository)
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

    #[Route('/beheerder', name: 'app_beheerder')]
    public function beheerderEvents(EventRepository $eventRepository)
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
        return $this->render('beheerder/index.html.twig', [
            'evt' => $evt, 'notAccepted' => $notAccepted
        ]);
    }



    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();
            $role = array_search('ROLE_ADMIN', $roles);

            if ($role !== false) {
                return $this->redirectToRoute('app_admin');
            } else {
                $role = array_search('ROLE_beheerder', $roles);
                if ($role !== false) {
                    return $this->redirectToRoute('app_beheerder');
                } else {
                    return $this->redirectToRoute('blog_list');
                }
            }
        }

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
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils): Response
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

            // Automatically log in the user after registering
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            return $this->redirectToRoute('blog_list');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/showteachers', name: 'Showteachers')]
    public function showTeachers(ManagerRegistry $doctrine): Response
    {
        # fetch all users from the repository
        $users = $doctrine->getRepository(User::class)->findAll();



        #render the info page with the filtered user data
        return $this->render('home/teacheroverview.html.twig', [
            'users' => $users
        ]);
    }


    #[Route('/admin/Event/{id}', name: 'adminEventInfo')]
    public function showAdminEventInfo(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        # fetch the event by id from the repository
        $evt = $doctrine->getRepository(Event::class)->find($id);
        $userEvents = $doctrine->getRepository(UserEvents::class)->findBy(['event' => $evt, 'accepted' => true]);

        #if event not found, throw exception
        if (!$evt) {
            throw $this->createNotFoundException(
                'Geen informatie gevonden voor id ' . $id
            );
        }

        # get all users enrolled in the event
        $users = [];
        foreach ($userEvents as $userEvent) {
            $users[] = $userEvent->getUser();
        }

        # handle delete button submission
        if ($request->request->has('delete')) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($evt);
            $entityManager->flush();

            return $this->redirectToRoute('adminEventList');
        }

        #render the info page with the event data
        return $this->render('home/adminEventinfo.html.twig', [
            'evt' => $evt,
            'users' => $users
        ]);
    }

    #[Route('/admin/Event/{event_id}/user/{user_id}/delete', name: 'delete_user_from_event')]
    public function delete_user_from_event(Request $request, ManagerRegistry $doctrine, int $event_id, int $user_id): Response
    {
        $userEventRepo = $doctrine->getRepository(UserEvents::class);
        $userEvent = $userEventRepo->findOneBy(['event' => $event_id, 'user' => $user_id]);

        if ($userEvent === null) {
            throw $this->createNotFoundException('User event not found.');
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($userEvent);
        $entityManager->flush();

        return $this->redirectToRoute('adminEventInfo', ['id' => $event_id]);
    }


    #[Route('/admin/user/{event_id}/event/{user_id}/delete', name: 'delete_event_from_user')]
    public function delete_event_from_user(Request $request, EntityManagerInterface $entityManager, int $user_id, int $event_id): Response
    {
        $userEventRepo = $entityManager->getRepository(UserEvents::class);
        $userEvent = $userEventRepo->findOneBy(['event' => $event_id, 'user' => $user_id]);
        dd($userEvent);

        $entityManager->remove($userEvent);
        $entityManager->flush();

        return $this->redirectToRoute('studentinfo', ['id' => $user_id]);
    }


    #[Route('/{id}', name: 'more_info')]
    public function showInfo(ManagerRegistry $doctrine, int $id): Response
    {
        $i = 0;
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

    #[Route('/{id}/edit/{field}', name: 'edit_event')]
    public function editField(Request $request, EntityManagerInterface $em, int $id, string $field): Response
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('No event found for id ' . $id);
        }

        $form = $this->createFormBuilder($event)
            ->add($field, TextType::class)
            ->add('submit', SubmitType::class, ['label' => 'Update', 'attr' => ['class' => 'buttondiv1'],])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Event updated successfully.');

            return $this->redirectToRoute('adminEventInfo', ['id' => $event->getId()]);
        }

        return $this->render('home/edit_field.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'field' => $field,
        ]);
    }

    #[Route('/{id}/edit/datum/{field}', name: 'edit_datum')]
    public function editdatum(Request $request, EntityManagerInterface $em, int $id, string $field): Response
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('No event found for id ' . $id);
        }

        $form = $this->createFormBuilder()
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime' // Use string input type instead of datetime
            ])
            ->add('enddate', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime' // Use string input type instead of datetime
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Update',
                'attr' => ['class' => 'buttondiv1'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the submitted form data
            $formData = $form->getData();

            // Create new DateTime objects for the new start and end dates
            $startDate = $formData['date'];
            $endDate = $formData['enddate'];

            // Set the new start and end dates on the event
            $event->setDate($startDate);
            $event->setEndDate($endDate);

            // Update the event in the database
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Event updated successfully.');

            return $this->redirectToRoute('adminEventInfo', ['id' => $event->getId()]);
        }

        return $this->render('home/edit_field.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'field' => $field,
        ]);
    }


    #[Route('/{id}/edit/{field}/beschrijving', name: 'edit_beschrijving')]
    public function editbeschrijving(Request $request, EntityManagerInterface $em, int $id, string $field): Response
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('No event found for id ' . $id);
        }

        $form = $this->createFormBuilder($event, [
            'attr' => ['style' => 'width: 100%',],
        ])
            ->add($field, TextareaType::class, [
                'attr' => ['style' => 'height: 50vh'],

            ])
            ->add('submit', SubmitType::class, ['label' => 'Update', 'attr' => ['class' => 'buttondiv1'],])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Event updated successfully.');

            return $this->redirectToRoute('adminEventInfo', ['id' => $event->getId()]);
        }

        return $this->render('home/edit_field.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'field' => $field,
        ]);
    }


    #[Route('/{id}/edit/{field}/opleiding', name: 'edit_opleiding')]
    public function editopleiding(Request $request, EntityManagerInterface $em, int $id, string $field): Response
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('No event found for id ' . $id);
        }

        $form = $this->createFormBuilder($event)
            ->add('opleiding', EntityType::class, [
                'class' => Opleiding::class,
                'choice_label'  =>function (Opleiding $opleiding)
                {
                    return $opleiding->getName();
                }

//                    'Maybe' => null,
//                    'Yes' => true,
//                    'No' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Bewaar', 'attr' => ['class' => 'buttondiv1'],])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Event updated successfully.');

            return $this->redirectToRoute('adminEventInfo', ['id' => $event->getId()]);
        }

        return $this->render('home/edit_field.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'field' => $field,
        ]);
    }

    #[Route('/edit/eventimage/{id}', name: 'edit_eventimage')]
    public function editeventImage(Request $request, int $id, EventRepository $eventRepository)
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('The event does not exist');
        }

        $form = $this->createForm(EditEventImage::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($form['image']->getData());
            $uploadedFile = $form['image']->getData();
            $destination = $this->getParameter('kernel.project_dir') . '/public/img/event-img';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/event-img/" . Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move($destination, $newFilename);
            $event->setImage($newFilename);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
            return $this->redirectToRoute("adminEventInfo", ['id' => $id]);
        }

        return $this->render('admin/edit_eventimage.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/edit/profileimage/{id}', name: 'edit_profileimage')]
    public function editProfileImage(Request $request, int $id)
    {
        $user = $this->getUser(); // retrieve the currently logged in user
        $form = $this->createForm(EditProfileImageType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['image']->getData();
            $destination = $this->getParameter('kernel.project_dir') . '/public/img/profile-img';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/profile-img/" . Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move($destination, $newFilename);
            $user->setImage($newFilename);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('yourprofile', ['id' => $user->getId()]);
        }

        return $this->render('home/edit_profile_image.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit/inschrijving/presence', name: 'edit_userevents_presence')]
    public function editUserEventPresence(Request $request, int $id, UserEventsRepository $userEventsRepository, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the user event to be edited
        $userEvent = $userEventsRepository->find($id);
        if (!$userEvent) {
            // Throw an exception if the user event cannot be found
            throw $this->createNotFoundException(
                'no user event found for id ' . $id
            );
        }

        // Create the form for editing the user event presence
        $form = $this->createFormBuilder($userEvent)
            ->add('presence', ChoiceType::class, [
                'choices' => [
                    'Aanwezig' => true,
                    'Afwezig' => false,
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Bewaar'])
            ->getForm();

        // Handle the form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the updated user event presence to the database
            $entityManager->flush();

            // Redirect the user to the studentinfo page
            return $this->redirectToRoute('studentinfo', ['id' => $userEvent->getUser()->getId()]);
        }

        // Render the user event presence edit form template
        return $this->render('home/editUserEventPresence.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit/inschrijving/rating', name: 'edit_userevents_rating')]
    public function editUserEventRating(Request $request, int $id, UserEventsRepository $userEventsRepository, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the user event to be edited
        $userEvent = $userEventsRepository->find($id);
        if (!$userEvent) {
            // Throw an exception if the user event cannot be found
            throw $this->createNotFoundException(
                'no user event found for id ' . $id
            );
        }

        // Create the form for editing the user event rating
        $form = $this->createFormBuilder($userEvent);
            $form = $this->createFormBuilder($userEvent)
                ->add('rating', ChoiceType::class, [
                    'choices' => [
                        'O' => 'O',
                        'V' => 'V',
                        'G' => 'G',
                    ],
                ])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        // Handle the form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the updated user event rating to the database
            $entityManager->flush();

            // Redirect the user to the studentinfo page
            return $this->redirectToRoute('studentinfo', ['id' => $userEvent->getUser()->getId()]);
        }

        // Render the user event rating edit form template
        return $this->render('home/editUserEventRating.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/enroll/{id}', name: 'enroll')]
    public function enroll(ManagerRegistry $doctrine, Event $event, #[CurrentUser] $user = null, UserEventsRepository $userEvents, int $id): Response
    {
            #check if the user is already enrolled in the event
            $existingEnrollment = $userEvents->findOneBy(['event' => $event, 'user' => $user]);
            if ($existingEnrollment) {
                $this->addFlash('error', 'U heeft zich al ingeschrijven');
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
                $userevent->setPresence(null);
                $userevent->setRating('0');
                $entityManager->persist($userevent);
                $entityManager->flush();
                $this->addFlash('success', 'Inschrijving succesvol!');
            } else {
                $this->addFlash('error', $message);
            }


            #redirect to the blog list
            return $this->redirectToRoute('blog_list');

    }



    #[Route('/admin/addklas', name: 'add_klas')]
    public function addclassAction(Request $request): Response
    {
        $class = new Klas();

        $form = $this->createForm(AddclassformType::class, $class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($class);
            $entityManager->flush();

            return $this->redirectToRoute('studentoverview');
        }

        return $this->render('admin/addclassform.html.twig', [
            'form' => $form->createView(),
        ]);
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