<?php


namespace App\Controller;


use App\Entity\Panier;
use App\Entity\Produit;
use App\Controller\UtilisateurController;
use App\Entity\Utilisateur;
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
    public function magasinAction(Request $request) : Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        $produitRepository = $em->getRepository('App:Produit');

        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getClient($utilisateurRepository);
        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut avoir accès au magasin');
            return $this->redirectToRoute('accueil');
        }

        $produits = $produitRepository->findAll();

        $post = $request->request->get('produit');
        if ($post)
        {
            dump('post');
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
                    dump('user');
                    dump($utilisateur);
                    $panier->setUtilisateur($utilisateur);
                    $panier->setQuantite($value);
                    $em->persist($panier);
                    dump('new');
                    dump($panier);

                }
                else
                {
                    $produitPanier->setQuantite($produitPanier->getQuantite() + $value);
                    dump('update');
                    dump($produitPanier);
                }
                $em->flush();

            }

        }


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

    /**
     * @Route ("/panier", name="panier")
     */
    public function panierAction() : Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getClient($utilisateurRepository);
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
    public function retirerProduitAction($id) : Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getClient($utilisateurRepository);
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
    public function viderPanierAction() : Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getClient($utilisateurRepository);
        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut vider son panier');
            return $this->redirectToRoute('accueil');
        }

        $panierRepository = $em->getRepository('App:Panier');
        $paniers = $panierRepository->findBy(['utilisateur' => $utilisateur]);

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
    public function acheterAction() : Response
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateurRepository = $em->getRepository('App:Utilisateur');
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getClient($utilisateurRepository);
        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un Client peut acheter les produits de son panier');
            return $this->redirectToRoute('accueil');
        }

        $panierRepository = $em->getRepository('App:Panier');
        $paniers = $panierRepository->findBy(['utilisateur' => $utilisateur]);

        foreach ($paniers as $panier)
            $em->remove($panier);


        $em->flush();
        return $this->redirectToRoute('panier');
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
