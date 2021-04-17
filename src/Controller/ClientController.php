<?php


namespace App\Controller;

use App\Entity\Panier;
use App\Form\UtilisateurType;
use App\Repository\ProduitRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{

    /**
     * @Route ("/modifierProfil", name="modifierProfil")
     */
    public function modifierProfilAction(ContainerInterface $container, Request $request, EntityManagerInterface $em): Response
    {
        $utilisateur = $container->get('utilisateur')->getClient();

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
            $utilisateur->setMotDePasse(sha1($utilisateur->getMotDePasse()));
            $em->flush();
            $this->addFlash('success', 'Profil modifié avec succès');
            return $this->redirectToRoute('magasin');
        }

        if ($form->isSubmitted())
            $this->addFlash('error', 'Erreur, modifications invalide');

        $args = ['myform' => $form->createView()];
        return $this->render('niveau3/modifierProfil.html.twig', $args);
    }

    /**
     * @Route ("/magasin", name="magasin")
     */
    public function magasinAction(ContainerInterface $container, EntityManagerInterface $em, ProduitRepository $produitRepository, Request $request) : Response
    {
        $utilisateur = $container->get('utilisateur')->getClient();
        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut avoir accès au magasin');
            return $this->redirectToRoute('accueil');
        }

        $produits = $produitRepository->findAll();

        $post = $request->request->get('produit');
        if ($post)
        {
            $produitsAchetes = array_filter($post, function ($data) { return $data > 0; });
            $panierRepository = $em->getRepository('App:Panier');
            foreach ($produitsAchetes as $key => $value)
            {
                // retirer la quantite du magasin
                $currentProduit = $produitRepository->find($key);
                $currentProduit->setQuantite($currentProduit->getQuantite() - $value);
                // ajouter au panier
                $produitPanier =  $panierRepository->findOneBy([
                    'produit' => $currentProduit,
                    'utilisateur' => $utilisateur
                ]);
                // si le produit n'est pas dans le panier on
                if(!$produitPanier)
                {
                    $panier = new Panier();
                    $panier->setProduit($currentProduit);
                    $panier->setUtilisateur($utilisateur);
                    $panier->setQuantite($value);
                    $em->persist($panier);
                }
                else
                {
                    $produitPanier->setQuantite($produitPanier->getQuantite() + $value);
                }
                $em->flush();

            }

        }
        return $this->render('niveau3/magasin.html.twig', ['produits' => $produits]);
    }

    /**
     * @Route ("/panier", name="panier")
     */
    public function panierAction(ContainerInterface $container) : Response
    {
        $utilisateur = $container->get('utilisateur')->getClient();

        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut accéder à son panier');
            return $this->redirectToRoute('accueil');
        }
        $panier = $utilisateur->getPaniers();

        return $this->render('niveau3/panier.html.twig', ['panier' => $panier]);
    }

    /**
     * @Route ("/retirerProduit/{id}", name="retirerProduit")
     */
    public function retirerProduitAction($id, ContainerInterface $container, EntityManagerInterface $em) : Response
    {
        $utilisateur = $container->get('utilisateur')->getClient();

        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut retirer des produit de son panier');
            return $this->redirectToRoute('accueil');
        }

        $panierRepository = $em->getRepository('App:Panier');
        $panier = $panierRepository->find($id);
        $produit = $panier->getProduit();
        $produit->setQuantite($produit->getQuantite() + $panier->getQuantite());

        $em->remove($panier);
        $em->persist($produit);
        $em->flush();
        return $this->redirectToRoute('panier');
    }

    /**
     * @Route ("/viderPanier", name="viderPanier")
     */
    public function viderPanierAction(ContainerInterface $container, EntityManagerInterface $em) : Response
    {
        $utilisateur = $container->get('utilisateur')->getClient();

        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut vider son panier');
            return $this->redirectToRoute('accueil');
        }

        $paniers = $utilisateur->getPaniers();

        foreach ($paniers as $panier) {
            $currentProduit = $panier->getProduit();
            $currentProduit->setQuantite($currentProduit->getQuantite() + $panier->getQuantite());
            $em->remove($panier);
        }

        $em->flush();
        return $this->redirectToRoute('panier');
    }

    /**
     * @Route ("/acheter", name="acheter")
     */
    public function acheterAction(ContainerInterface $container, EntityManagerInterface $em) : Response
    {
        $utilisateur = $container->get('utilisateur')->getClient();

        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut acheter les produits de son panier');
            return $this->redirectToRoute('accueil');
        }

        $paniers = $utilisateur->getPaniers();

        foreach ($paniers as $panier)
            $em->remove($panier);

        $em->flush();
        return $this->redirectToRoute('panier');
    }

}