<?php


namespace App\Controller;


use App\Entity\Produit;
use App\Controller\UtilisateurController;
use App\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route ("/produit")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route ("/magasin", name="magasin")
     */
    public function magasinAction() : Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        $produitRepository = $em->getRepository('App:Produit');

        $utilisateur = $this->getClient($utilisateurRepository);
        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut avoir accès au magasin');
            return $this->redirectToRoute('accueil');
        }

        $produits = $produitRepository->findAll();
        return $this->render('niveau3/magasin.html.twig', ['produits' => $produits]);
    }

    /**
     * @Route ("/ajouter", name="produit_ajouter")
     */
    public function ajouterAction(Request $request):Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        if (!$this->getAdmin($utilisateurRepository))
        {
            $this->addFlash('error', 'Seul un administrateur peut ajouter un produit');
            return $this->redirectToRoute('accueil');
        }

        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->add('ajouter', SubmitType::class, ['label' => 'Ajouter le produit']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Le produit a été ajouté avec succès');
            return $this->redirectToRoute('accueil');
        }

        if ($form->isSubmitted())
            $this->addFlash('error', "Erreur, modifications invalide");

        $args = ['myform' => $form->createView()];
        return $this->render('niveau3/ajout.html.twig', $args);
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
