<?php


namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
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
     * @Route ("/seDeconnecter", name="seDeconnecter")
     */
    public function seDeconnecterAction(UtilisateurRepository $utilisateurRepository): Response
    {
        $param = $this->getParameter('id');
        if ($this->getAnonyme($param, $utilisateurRepository))
            $this->addFlash('error', 'Vous n\'êtes même pas connecté' );
        else
            $this->addFlash('success', 'Déconnexion avec succès');
        return $this->redirectToRoute('accueil');
    }


    public function getAdmin($param, $utilisateurRepository)
    {
        $utilisateur = $utilisateurRepository->find($param);

        if (!$utilisateur || !$utilisateur->getStatus())
            return false;
        else
            return $utilisateur;

    }

    public function getClient($param, $utilisateurRepository)
    {
        $utilisateur = $utilisateurRepository->find($param);

        if (!$utilisateur || $utilisateur->getStatus())
            return false;
        else
            return $utilisateur;

    }

    public function getAnonyme($param, $utilisateurRepository)
    {
        $utilisateur = $utilisateurRepository->find($param);

        return !$utilisateur;
    }


}