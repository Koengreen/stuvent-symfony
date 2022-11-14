<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    #[Route('/', name: 'blog_list')]
    public function show(EventRepository $eventRepository)
    {
        $evt = $eventRepository->findAll();
        return $this->render('home/index.html.twig', [
            'evt' => $evt,
        ]);
    }


    #[Route('/register')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        return $this->render('home/register.html.twig', [
            'number' => $number,
        ]);
    }


    #[Route('/{id}', name: 'more_info')]
    public function showInfo(ManagerRegistry $doctrine, int $id): Response
    {
        $evt = $doctrine->getRepository(Event::class)->find($id);

        if (!$evt) {
            throw $this->createNotFoundException(

                'no information found for id ' . $id
            );
        }
        return $this->render('home/info.html.twig', [
            'evt' => $evt,
        ]);

    }
}

