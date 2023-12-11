<?php

namespace App\Entity;

use App\Repository\CollaborateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: CollaborateurRepository::class)]
class Collaborateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?string $nom = null;
    #[ORM\Column]
    private ?string $prenom = null;
    #[ORM\OneToMany(mappedBy: 'collaborateur', targetEntity: Commande::class, orphanRemoval: true)]
    private Collection $commandes;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(nullable: true)]
    private ?int $panier = null;

    #[ORM\Column(length: 10)]
    private ?string $telephone = null;

    #[ORM\OneToMany(mappedBy: 'livreur', targetEntity: Commande::class)]
    private Collection $commandesLivreur;



    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->commandesLivreur = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
    public function getNom(): ?string    {
        return $this->nom;
    }

    /**     * @param string|null $nom     */
    public function setNom(?string $nom): void    {
        $this->nom = $nom;
    }

    /**     * @return string|null     */
    public function getPrenom(): ?string    {
        return $this->prenom;
    }

    /**     * @param string|null $prenom     */
    public function setPrenom(?string $prenom): void    {
        $this->prenom = $prenom;
    }
    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setCollaborateur($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getCollaborateur() === $this) {
                $commande->setCollaborateur(null);
            }
        }

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getPanier(): ?int
    {
        return $this->panier;
    }

    public function setPanier(?int $panier): static
    {
        $this->panier = $panier;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandesLivreur(): Collection
    {
        return $this->commandesLivreur;
    }

    public function addCommandesLivreur(Commande $commandesLivreur): static
    {
        if (!$this->commandesLivreur->contains($commandesLivreur)) {
            $this->commandesLivreur->add($commandesLivreur);
            $commandesLivreur->setLivreur($this);
        }

        return $this;
    }

    public function removeCommandesLivreur(Commande $commandesLivreur): static
    {
        if ($this->commandesLivreur->removeElement($commandesLivreur)) {
            // set the owning side to null (unless already changed)
            if ($commandesLivreur->getLivreur() === $this) {
                $commandesLivreur->setLivreur(null);
            }
        }

        return $this;
    }


}
