<?php


namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class homeController extends AbstractController
{
    /**
     * @Route("/", name="homeController")
     */
    public function homeAction(): Response
    {
        $args = [
          'type' => 'admin',
          'name' => 'giles',
        ];
        return $this->render('home.html.twig', $args);
    }

}