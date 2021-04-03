<?php


namespace App\Controller;


use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $produitRepository = $em->getRepository('App:Produit');
        $produits = $produitRepository->findAll();
        return $this->render('niveau3/magasin.html.twig', ['produits' => $produits]);
    }

    /**
     * @Route ("/ajouter", name="produit_ajouter")
     */
    public function ajouterAction():Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->add('ajouter', SubmitType::class, ['label' => 'Ajouter le produit']);

        $args = ['myform' => $form->createView()];
        dump($form);
        return $this->render('niveau3/ajout.html.twig', $args);
    }
}