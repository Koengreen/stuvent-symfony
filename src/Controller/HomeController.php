<?php

namespace App\Controller;
use App\Entity\About;
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
    #[Route('/beheerder/addadmin', name: 'add_admin')]
    public function addadmin(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
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

            return $this->redirectToRoute('blog_list');
        }

        return $this->render('admin/addadmins.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }



    #[Route('/', name: 'blog_list')]
    public function show(EventRepository $eventRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $repoArticles = $em->getRepository(UserEvents::class);
        $totalAttendees = $repoArticles->createQueryBuilder('a')
            ->select('count(a.user)')
            ->getQuery()
            ->getSingleScalarResult();
        $evt = $eventRepository->findAll();
        return $this->render('home/index.html.twig', [
            'evt' => $evt, 'totalAttendees' => $totalAttendees,
        ]);
    }
    #[Route('/myprofile/{id}', name: 'myProfile')]
    public function myProfile(ManagerRegistry $doctrine, int $id, UserEventsRepository $userEventsRepository, #[CurrentUser] $user): Response
    {
        $i = 0;
        $profile = $doctrine->getRepository(User::class)->find($id);
        $event = $doctrine->getRepository(Event::class)->findAll();
#
        if (!$profile) {
            return $this->redirectToRoute('app_login');
            ;
        }

        $userid = $user->getId();
        $evt = $userEventsRepository->findBy(['user' => $userid]);
        return $this->render('home/myprofile.html.twig', [
            'profile' => $profile,'event' => $event,  'evt' => $evt,
            'i' => $i,
        ]);


    }


#[Route('/about', name: 'about')]
    public function about():Response {
        return $this->render("home/about.html.twig");
    }


    #[Route('/about/edit', name: 'aboutedit')]
    public function editabout(Request $request , EntityManagerInterface $entityManager,  ManagerRegistry $doctrine): Response
    {

        $about = new About();
        $form = $this->createForm(Aboutpageeditorform::class, $about);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($form['images']->getData());
            $uploadedFile = $form['images']->getData();
            $destination = $this->getParameter('kernel.project_dir').'/public/img/aboutpage';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/event-img/".  Urlizer::urlize( $originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
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
        $user->setRoles(["ROLE_User"]);
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
        $evt = $doctrine->getRepository(Event::class)->find($id);
#
       if (!$evt) {
           throw $this->createNotFoundException(
             'no information found for id ' . $id
            );
        }
        return $this->render('home/info.html.twig', [
            'evt' => $evt,
       ]);
    }

    #[Route('/enroll/{id}', name: 'enroll')]
    public function enroll(ManagerRegistry $doctrine, Event $event, #[CurrentUser] $user): Response
    {// dd($user);
            $entityManager = $doctrine->getManager();
            $userevent = new UserEvents();
            $userevent->setEvent($event);
            $userevent->setUser($user);
            $userevent->setAccepted(false);
            if ($userevent == true) {
                $entityManager->persist($userevent);
                $entityManager->flush();
                $this->addFlash('succes', 'inschrijving succesvol');
                return $this->redirectToRoute('blog_list');
            }
            return $this->render('home/index.html.twig');

    }

    #[Route('/admin/add', name: 'add_events')]
    public function addevents(Request $request , EntityManagerInterface $entityManager): Response
    {
        $event = new Event ();
        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($form['image']->getData());
            $uploadedFile = $form['image']->getData();
            $destination = $this->getParameter('kernel.project_dir').'/public/img/event-img';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = "img/event-img/".  Urlizer::urlize( $originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
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
        $entityManager = $doctrine->getManager();
        $event = $entityManager->getRepository(Event::class)->find($id);
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin', [
            'id' => $event->getId()
        ]);
    }
}


