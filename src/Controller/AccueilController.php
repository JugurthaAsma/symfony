<?php


namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AccueilController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function homeAction(): Response
    {
        $param = $this->getParameter('id');
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        $utilisateur = $utilisateurRepository->find($param);

        // anonyme par défaut
        $nom = '';
        $status = 'Anonyme';

        if ($utilisateur)
        {
            $nom = $utilisateur->getLogin();
            $status = $utilisateur->getStatus() == 1 ? 'Admin' : 'Client';
        }

        $args = [
            'nom' => $nom,
            'status' => $status,

        ];

        return $this->render('niveau3/accueil.html.twig', $args);
    }

}