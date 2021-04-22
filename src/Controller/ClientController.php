<?php


namespace App\Controller;

use App\Entity\Panier;
use App\Form\UtilisateurType;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller pour les actions qui concernent un utilisateur authentifié (Client).
 */
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
            $utilisateur->setStatus(false); // en cas de faille XSS
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

        // récupérer le tableau $_POST[produit] et verifier qu'il n'est pas vide (formulaire posté)
        $post = $request->request->get('produit');
        if ($post)
        {
            // ne récupérer que les produits avec une quantité supérieur à 0
            $produitsAchetes = array_filter($post, function ($data) { return $data > 0; });
            $panierRepository = $em->getRepository('App:Panier');
            foreach ($produitsAchetes as $key => $value)
            {
                // retirer la quantite du magasin
                $currentProduit = $produitRepository->find($key);
                $currentProduit->setQuantite($currentProduit->getQuantite() - $value);
                // ajouter au panier

                // chercher le produit dans la panier (si il existe déjà)
                $produitPanier =  $panierRepository->findOneBy([
                    'produit' => $currentProduit,
                    'utilisateur' => $utilisateur
                ]);
                // si le produit n'est pas dans le panier on l'ajoute
                if(!$produitPanier)
                {
                    $panier = new Panier();
                    $panier->setProduit($currentProduit);
                    $panier->setUtilisateur($utilisateur);
                    $panier->setQuantite($value);
                    $em->persist($panier);
                }
                // sinon on ajoute la quantité seulement
                else
                {
                    $produitPanier->setQuantite($produitPanier->getQuantite() + $value);
                }
            }
            // MAJ de la base de données
            $em->flush();
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
     * @Route ("/retirerProduit/{id}",
     *     name="retirerProduit",
     *     requirements={ "id" : "\d+" }
     * )
     */
    public function retirerProduitAction($id, ContainerInterface $container, EntityManagerInterface $em, PanierRepository $panierRepository) : Response
    {
        $utilisateur = $container->get('utilisateur')->getClient();

        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut retirer des produit de son panier');
            return $this->redirectToRoute('accueil');
        }

        $panier = $panierRepository->find($id);
        $produit = $panier->getProduit();
        // remettre à jour la quantite du produit pour le magasin
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
        $this->addFlash('success', 'Votre commande a été passé avec succès');
        return $this->redirectToRoute('panier');
    }

}

/**
 * @author
 * ASMA Jugurtha
 * BOUDAHBA Hylia
 */