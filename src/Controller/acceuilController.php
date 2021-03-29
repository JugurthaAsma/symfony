<?php


namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class acceuilController extends AbstractController
{
    /**
     * @Route("/", name="acceuilController")
     */
    public function homeAction(): Response
    {
        $args = [
          'type' => 'admin',
          'name' => 'giles',
        ];
        return $this->render('acceuil.html.twig', $args);
    }

}