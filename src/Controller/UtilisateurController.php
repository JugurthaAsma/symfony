<?php


namespace App\Controller;


use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UtilisateurController extends AbstractController
{
    public function headerAction(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $produitRepository = $em->getRepository(Produit::class);


        // get le nombre de produits disponibles
        $nbrProduits = $produitRepository->createQueryBuilder('produits')
            ->select('count(produits.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $param = $this->getParameter('id');
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        $utilisateur = $utilisateurRepository->find($param);

        if (!$utilisateur)
        {
            return $this->render('niveau2/anonyme.html.twig', ['nbrProduits' => $nbrProduits]);
        }
        else
        {

            if ($utilisateur->getStatus())
                return $this->render('niveau2/admin.html.twig', ['nbrProduits' => $nbrProduits]);
            else
                return $this->render('niveau2/client.html.twig', ['nbrProduits' => $nbrProduits]);
        }
    }

    /**
     * @Route ("/seConnecter", name="seConnecter")
     */
    public function seConnecterAction(): Response
    {
        $utilisateurRepository = $this->getDoctrine()->getManager()->getRepository('App:Utilisateur');

        if (!$this->getAnonyme($utilisateurRepository))
        {
            $this->addFlash('error', 'Seul un anonyme peut se connecter');
            return $this->redirectToRoute('accueil');
        }
        return $this->render('niveau3/seConnecter.html.twig');
    }

    /**
     * @Route ("/seDeconnecter", name="seDeconnecter")
     */
    public function seDeconnecterAction(): Response
    {
        $utilisateurRepository = $this->getDoctrine()->getManager()->getRepository('App:Utilisateur');
        if ($this->getAnonyme($utilisateurRepository))
            $this->addFlash('error', 'Vous n\'êtes même pas connecté' );
        else
            $this->addFlash('success', 'Déconnexion avec succès');
        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route ("/creerCompte", name="creerCompte")
     */
    public function creerUnCompteAction(Request $request): Response
    {
        $em =$this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        if (!$this->getAnonyme($utilisateurRepository))
        {
            $this->addFlash('error', 'Seul un anonyme peut se créer un compte');
            return $this->redirectToRoute('accueil');
        }

        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->remove('status'); // obligatoirement un compte client
        $form->add('send', SubmitType::class, ['label' => 'Créer mon compte']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $utilisateur->setStatus(false); // obligatoirement un compte client (faille XSS)
            $utilisateur->setMotDePasse(sha1($utilisateur->getMotDePasse()));
            $em->persist($utilisateur);
            $em->flush();
            $this->addFlash('success', 'Le compte a été créer avec succès');
            return $this->redirectToRoute('accueil');
        }

        if ($form->isSubmitted())
        {
            $this->addFlash('error', "Erreur, données invalides");
        }

        $args = ['myform' => $form->createView()];
        return $this->render('niveau3/creerCompte.html.twig', $args);
    }

    /**
     * @Route ("/modifierProfil", name="modifierProfil")
     */
    public function modifierProfilAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        $utilisateur = $this->getClient($utilisateurRepository);
        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut modifier son profil');
            return $this->redirectToRoute('accueil');
        }

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->remove('status'); // obligatoirement un compte client
        $form->add('send', SubmitType::class, ['label' => 'Modifier mon profil']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $utilisateur->setStatus(false);
            $em->flush();
            $this->addFlash('success', 'Profil modifié avec succès');
            return $this->redirectToRoute('magasin');
        }

        if ($form->isSubmitted())
            $this->addFlash('error', 'Erreur, modifications invalide');

        $args = ['myform' => $form->createView()];
        return $this->render('niveau3/creerCompte.html.twig', $args);
    }

    /**
     * @Route ("/gererUtilisateurs", name="gererUtilisateurs")
     */
    public function gererUtilisateursAction() : Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');

        if (!$this->getAdmin($utilisateurRepository))
        {
            $this->addFlash('error', 'Seul un administrateur peut gérer les utilisateur');
            return $this->redirectToRoute('accueil');
        }

        $utilisateurs = $utilisateurRepository->findAll();
        return $this->render('niveau3/utilisateurs.html.twig', ['utilisateurs' => $utilisateurs]);
    }

    /**
     * @Route ("/supprimerUtilisateur/{id}", name="supprimerUtilisateur")
     */
    public function supprimerUtilisateurAction($id) : Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');

        if (!$this->getAdmin($utilisateurRepository))
        {
            $this->addFlash('error', 'Seul un administrateur peut supprimer un utilisateur');
            return $this->redirectToRoute('accueil');
        }


        $utilisateurSupprime = $utilisateurRepository->find($id);
        $em->remove($utilisateurSupprime);
        $em->flush();
        return $this->redirectToRoute('gererUtilisateurs');
    }


    public function getAdmin($utilisateurRepository)
    {
        $param = $this->getParameter('id');
        $utilisateur = $utilisateurRepository->find($param);

        if (!$utilisateur || !$utilisateur->getStatus())
            return false;
        else
            return $utilisateur;

    }

    public function getClient($utilisateurRepository)
    {
        $param = $this->getParameter('id');
        $utilisateur = $utilisateurRepository->find($param);

        if (!$utilisateur || $utilisateur->getStatus())
            return false;
        else
            return $utilisateur;

    }

    public function getAnonyme($utilisateurRepository)
    {
        $param = $this->getParameter('id');
        $utilisateur = $utilisateurRepository->find($param);

        return !$utilisateur;
    }


}