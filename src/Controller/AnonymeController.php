<?php


namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller pour les actions qui concernent un utilisateur non authentifié (Anonyme).
 */
class AnonymeController extends AbstractController
{
    /**
     * @Route ("/seConnecter", name="seConnecter")
     */
    public function seConnecterAction(ContainerInterface $container): Response
    {
        $utilisateur = $container->get('utilisateur')->getAnonyme();
        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un anonyme peut se connecter');
            return $this->redirectToRoute('accueil');
        }
        return $this->render('niveau3/seConnecter.html.twig');
    }

    /**
     * @Route ("/creerCompte", name="creerCompte")
     */
    public function creerUnCompteAction(EntityManagerInterface $em, ContainerInterface $container, Request $request): Response
    {
        $utilisateur = $container->get('utilisateur')->getAnonyme();
        if (!$utilisateur) {
            $this->addFlash('error', 'Seul un anonyme peut se créer un compte');
            return $this->redirectToRoute('accueil');
        }

        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->remove('status'); // obligatoirement un compte client
        $form->add('send', SubmitType::class, ['label' => 'Créer mon compte']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->setStatus(false); // obligatoirement un compte client (éviter les faille XSS)
            $utilisateur->setMotDePasse(sha1($utilisateur->getMotDePasse()));
            $em->persist($utilisateur);
            $em->flush();
            $this->addFlash('success', 'Le compte a été créer avec succès');
            return $this->redirectToRoute('accueil');
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', "Erreur, données invalides");
        }

        $args = ['myform' => $form->createView()];
        return $this->render('niveau3/creerCompte.html.twig', $args);
    }

}

/**
 * @author
 * ASMA Jugurtha
 * BOUDAHBA Hylia
 */