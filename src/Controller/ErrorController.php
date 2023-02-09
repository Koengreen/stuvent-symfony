<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function enrollErrorAction(): Response
    {
        return $this->render('error/enroll.html.twig');
    }
}