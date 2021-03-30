<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UtilisateurController extends AbstractController
{
    public function headerAction(): Response
    {
        $param = $this->getParameter('id');

        if ($param == 0 )
        {
            return $this->render('niveau2/headerAnonyme.html.twig');
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            $utilisateurRepository = $em->getRepository('App:Utilisateur');
            $utilisateur = $utilisateurRepository->find($param);

            if ($utilisateur->getStatus())
                return $this->render('niveau2/headerAdmin.html.twig');
            else
                return $this->render('niveau2/headerClient.html.twig');
        }
    }


}