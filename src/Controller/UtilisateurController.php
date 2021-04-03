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

        if ($param == 0 )
        {
            return $this->render('niveau2/anonyme.html.twig', ['nbrProduits' => $nbrProduits]);
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            $utilisateurRepository = $em->getRepository('App:Utilisateur');
            $utilisateur = $utilisateurRepository->find($param);

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
        return $this->render('niveau3/seConnecter.html.twig');
    }

    /**
     * @Route ("/seDeconnecter", name="seDeconnecter")
     */
    public function seDeconnecterAction(): Response
    {

        $this->addFlash('success', 'Déconnexion avec succès');
        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route ("/creerCompte", name="creerCompte")
     */
    public function creerUnCompteAction(): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->add('send', SubmitType::class, ['label' => 'Créer mon compte']);

        $args = ['myform' => $form->createView()];
        return $this->render('niveau3/creerCompte.html.twig', $args);
    }

    /**
     * @Route ("/modifierProfil", name="modifierProfil")
     */
    public function modifierProfilAction(Request $request): Response
    {
        $param = $this->getParameter('id');
        if (!$param)
        {
            $this->addFlash('error', 'Pour modifier un profil, il faut déjà en avoir un !');
            return $this->redirectToRoute('accueil');
        }

        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        $utilisateur = $utilisateurRepository->find($param);

        if ($utilisateur->getStatus())
        {
            $this->addFlash('error', 'Un admin ne peut pas modifier des profils');
            return $this->redirectToRoute('accueil');
        }

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->add('send', SubmitType::class, ['label' => 'Modifier mon profil']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
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
        $param = $this->getParameter('id');
        if (!$param)
        {
            $this->addFlash('error', 'Pour gérer les utilisateurs, il faut être admin, et non pas un anonyme !');
            return $this->redirectToRoute('accueil');
        }

        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        $utilisateur = $utilisateurRepository->find($param);

        if (!$utilisateur->getStatus())
        {
            $this->addFlash('error', 'Un client ne peut pas gérer les utilisateurs');
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
        $param = $this->getParameter('id');
        if (!$param)
        {
            $this->addFlash('error', 'Pour supprimer un utilisateur, il faut être admin, et non pas un anonyme !');
            return $this->redirectToRoute('accueil');
        }

        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        $utilisateur = $utilisateurRepository->find($param);

        if (!$utilisateur->getStatus())
        {
            $this->addFlash('error', 'Un client ne peut pas supprimer un utilisateur');
            return $this->redirectToRoute('accueil');
        }

        $utilisateur = $utilisateurRepository->find($id);
        $em->remove($utilisateur);
        $em->flush();
        return $this->redirectToRoute('gererUtilisateurs');
    }


    private function redirectIfNotAdmin()
    {

    }


}