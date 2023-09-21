<?php

namespace App\Entity;

use App\Repository\CollaborateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: CollaborateurRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Collaborateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

//    #[ORM\Column]
//    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;
    #[ORM\Column]
    private ?string $nom = null;
    #[ORM\Column]
    private ?string $prenom = null;

    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'collaborateurs')]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
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
//    public function getRoles(): array
//    {
//        $roles = $this->roles;
//        // guarantee every user at least has ROLE_USER
//        $roles[] = 'ROLE_USER';
//
//        return array_unique($roles);
//    }
//
//    public function setRoles(array $roles): static
//    {
//        $this->roles = $roles;
//
//        return $this;
//    }

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

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     */
    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return string|null
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @param string|null $prenom
     */
    public function setPrenom(?string $prenom): void
    {
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
     * @return Collection<int, Role>
     */
    public function getRoles(): array
    {
        $roles = [];

        foreach ($this->roles as $role) {
            $roles[] = $role->getRole();
        }

        return array_unique($roles);
    }

    public function addRoles(Role $roles): static
    {
        if (!$this->roles->contains($roles)) {
            $this->roles->add($roles);
            $roles->addCollaborateur($this);
        }

        return $this;
    }

    public function removeRoles(Role $roles): static
    {
        if ($this->roles->removeElement($roles)) {
            $roles->removeCollaborateur($this);
        }

        return $this;
    }
}