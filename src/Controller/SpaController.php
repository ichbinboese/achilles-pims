<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SpaController extends AbstractController
{
    #[Route('/{vueRouting}', name: 'spa_catch_all', requirements: ['vueRouting' => '^(?!api).+'])]
    public function index(): Response
    {
        return $this->render('base.html.twig'); // Oder 'index.html.twig', je nachdem
    }
}
