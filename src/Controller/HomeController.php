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
    /**
     * @route ('/', name=app_show)
     */
    public function show(EventRepository $eventRepository){
        $evt =$eventRepository->findAll();
        return $this->render('home/index.html.twig', [
            'evt' => $evt,
        ]);
    }


}

