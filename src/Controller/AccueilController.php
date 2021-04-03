<?php


namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;


class AccueilController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function homeAction(): Response
    {
        $param = $this->getParameter('id');

        if ($param == 0 )
        {
            $args = [
                'nom' => '',
                'status' => 'Anonyme',
            ];
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            $utilisateurRepository = $em->getRepository('App:Utilisateur');
            $utilisateur = $utilisateurRepository->find($param);
            dump($utilisateur);

            $args = [
                'nom' => $utilisateur->getLogin(),
                'status' => $utilisateur->getStatus() == 1 ? 'Admin' : 'Client',

            ];
        }


        return $this->render('niveau3/accueil.html.twig', $args);
    }

    /**
     * @Route(
     *     "/ajout/{login}/{motdepasse}/{status}",
     *     name="ajout",
     *     defaults={"status":0}
     * )
     */
    public function ajoutAction($login, $motdepasse, $status): Response
    {
        $em = $this->getDoctrine($login)->getManager();

        $utilisateur = new Utilisateur();
        $utilisateur->setLogin($login);
        $utilisateur->setMotDePasse(sha1($motdepasse));
        if($status == 1)
            $utilisateur->setStatus(true);


        $em->persist($utilisateur);
        $em->flush();

        $this->addFlash('info', 'Utilisateur ajouté !');


        return $this->redirectToRoute('accueil');
    }
}