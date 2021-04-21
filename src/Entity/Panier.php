<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table (
 *     name="im2021_asso_utilisateurs_produits",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="aup_index", columns={"id_utilisateur", "id_produit"})
 *     }
 *)
 * @ORM\Entity(repositoryClass=PanierRepository::class)
 */
class Panier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantite;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="paniers")
     * @ORM\JoinColumn(name="id_utilisateur", nullable=false, referencedColumnName="pk")
     */
    private $utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Produit::class, inversedBy="paniers")
     * @ORM\JoinColumn(name="id_produit", nullable=false)
     */
    private $produit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }
}

/**
 * @author
 * ASMA Jugurtha
 * BOUDAHBA Hylia
 */
