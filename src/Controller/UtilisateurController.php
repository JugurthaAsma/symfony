<?php


namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller qui regroupe toutes les méthodes/actions en commun des autres controllers
 */
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
     * @Route("/", name="accueil")
     */
    public function accueilAction(UtilisateurRepository $utilisateurRepository): Response
    {
        $param = $this->getParameter('id');
        $utilisateur = $utilisateurRepository->find($param);

        // anonyme par défaut
        $prenom = '';
        $status = 'Anonyme';

        if ($utilisateur) {
            $prenom = $utilisateur->getPrenom();
            $status = $utilisateur->getStatus() == 1 ? 'Admin' : 'Client';
        }

        $args = [
            'prenom' => $prenom,
            'status' => $status,
        ];

        return $this->render('niveau3/accueil.html.twig', $args);
    }

    /**
     * @Route ("/seDeconnecter", name="seDeconnecter")
     */
    public function seDeconnecterAction(): Response
    {
        if ($this->getAnonyme())
            $this->addFlash('error', 'Vous n\'êtes même pas connecté' );
        else
            $this->addFlash('success', 'Déconnexion avec succès');
        return $this->redirectToRoute('accueil');
    }


    /**
     * Méthodes à utiliser comme service pour savoir dans les autres controllers si on est :
     * - non authentifié (utilisateur non trouvé dans la base de données)
     * - authentifié client
     * - authentifié administrateur
     *
     */

    public function getAdmin()
    {
        $utilisateur = $this->getUtilisateur();
        return (!$utilisateur || !$utilisateur->getStatus()) ? false : $utilisateur;
    }

    public function getClient()
    {
        $utilisateur = $this->getUtilisateur();
        return (!$utilisateur || $utilisateur->getStatus()) ? false : $utilisateur;
    }

    public function getAnonyme()
    {
        return !$this->getUtilisateur();
    }

    // l'utilisateur actuellement loggué
    private function getUtilisateur() : ?Utilisateur
    {
        $param = $this->getParameter('id');
        $utilisateurRepository = $this->getDoctrine()->getManager()->getRepository('App:Utilisateur');
        return $utilisateurRepository->find($param);
    }

}

/**
 * @author
 * ASMA Jugurtha
 * BOUDAHBA Hylia
 */