<?php


namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Controller pour les actions qui concernent un administrateur.
 */
class AdminController extends AbstractController
{
    /**
     * @Route ("/gererUtilisateurs", name="gererUtilisateurs")
     */
    public function gererUtilisateursAction(ContainerInterface $container, UtilisateurRepository $utilisateurRepository) : Response
    {
        // Savoir si on est admin (utilisation du service)
        $utilisateur = $container->get('utilisateur')->getAdmin();

        if (!$utilisateur) // si on n'est pas admin
        {
            $this->addFlash('error', 'Seul un administrateur peut gérer les utilisateur');
            return $this->redirectToRoute('accueil');
        }

        $utilisateurs = $utilisateurRepository->findAll();
        return $this->render('niveau3/utilisateurs.html.twig', ['utilisateurs' => $utilisateurs]);
    }

    /**
     * @Route ("/supprimerUtilisateur/{id}",
     *     name="supprimerUtilisateur",
     *     requirements={ "id" : "\d+" }
     * )
     */
    public function supprimerUtilisateurAction($id, EntityManagerInterface $em, ContainerInterface $container, UtilisateurRepository $utilisateurRepository) : Response
    {
        $utilisateur = $container->get('utilisateur')->getAdmin();

        if (!$utilisateur)
        {
            $this->addFlash('error', 'Seul un administrateur peut supprimer un utilisateur');
            return $this->redirectToRoute('accueil');
        }

        $utilisateurSupprime = $utilisateurRepository->find($id);
        if ($utilisateurSupprime == null)
        {
            $this->addFlash('error', 'L\'utilisateur nº '. $id .' n\'existe pas');
        }
        elseif ($utilisateurSupprime === $utilisateur)
        {
            $this->addFlash('error', 'Impossible de supprimer l\'utilisateur loggué');
        }
        else
        {
            // vider le panier
            $paniers = $utilisateurSupprime->getPaniers();
            foreach ($paniers as $panier) {
                $currentProduit = $panier->getProduit();
                $currentProduit->setQuantite($currentProduit->getQuantite() + $panier->getQuantite());
                $em->remove($panier);
            }

            $em->remove($utilisateurSupprime);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès');
        }

        return $this->redirectToRoute('gererUtilisateurs');
    }

    /**
     * @Route ("/ajouter", name="produit_ajouter")
     */
    public function ajouterAction(EntityManagerInterface $em, ContainerInterface $container, Request $request):Response
    {
        $utilisateur = $container->get('utilisateur')->getAdmin();

        if (!$utilisateur)
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
            $this->addFlash('error', "Erreur, données invalides");

        $args = ['myform' => $form->createView()];
        return $this->render('niveau3/ajout.html.twig', $args);
    }
}

/**
 * @author
 * ASMA Jugurtha
 * BOUDAHBA Hylia
 */