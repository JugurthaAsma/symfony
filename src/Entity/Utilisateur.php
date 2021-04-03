<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="im2021_utilisateurs")
 * @ORM\Entity(repositoryClass=UtilisateurRepository::class)
 * @UniqueEntity(fields={"login"}, message="Login déjà existant")
 */
class Utilisateur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="pk", type="integer", length=11)
     */
    private $id;

    /**
     * @ORM\Column(
     *     name="identifiant",
     *     type="string",
     *     unique=true,
     *     length=30,
     *     options={"comment"="sert de login (doit être unique)"}
     *)
     * @Assert\NotBlank(message="le login est obligatoire")
     * @Assert\Length(max="30", maxMessage="le login ne doit pas dépasser les 30 caractères")
     */
    private $login;

    /**
     * @ORM\Column(
     *     name="motdepasse",
     *     type="string",
     *     length=64,
     *     options={"comment"="mot de passe crypté : il faut une taille assez grande pour ne pas le tronquer"}
     *)
     * @Assert\NotBlank(message="le mot de passe est obligatoire")
     * @Assert\Length(max="64", maxMessage="le mot de passe ne doit pas dépasser les 64 caractères")
     */
    private $motDePasse;

    /**
     * @ORM\Column(type="string", length=30, nullable=true, options={"default"=NULL})
     * @Assert\Length(max="30", maxMessage="le nom ne doit pas dépasser les 30 caractères")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=30, nullable=true, options={"default"=NULL})
     * @Assert\Length(max="30", maxMessage="le prénom ne doit pas dépasser les 30 caractères")
     */
    private $prenom;

    /**
     * @ORM\Column(name="anniversaire", type="date", nullable=true, options={"default"=NULL})
     */
    private $dateDeNaissance;

    /**
     * @ORM\Column(name="isadmin", type="boolean", options={"default"=0})
     * @Assert\Type("bool", message="{{ value }} n'est pas un {{ type }}")
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateDeNaissance(): ?DateTimeInterface
    {
        return $this->dateDeNaissance;
    }

    public function setDateDeNaissance(?DateTimeInterface $dateDeNaissance): self
    {
        $this->dateDeNaissance = $dateDeNaissance;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function __construct()
    {
        $this->nom = null;
        $this->prenom = null;
        $this->dateDeNaissance = null;
        $this->status = false;

    }
}
