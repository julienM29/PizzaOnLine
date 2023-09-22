<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToMany(targetEntity: Collaborateur::class, mappedBy: 'rolesUtilisateur')]
    private Collection $collaborateurs;



    public function __construct()
    {
        $this->collaborateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Collaborateur>
     */

    /**
     * @return Collection<int, Collaborateur>
     */
    public function getCollaborateurs(): Collection
    {
        return $this->collaborateurs;
    }

    public function addCollaborateur(Collaborateur $collaborateur): static
    {
        if (!$this->collaborateurs->contains($collaborateur)) {
            $this->collaborateurs->add($collaborateur);
            $collaborateur->addRolesUtilisateur($this);
        }

        return $this;
    }

    public function removeCollaborateur(Collaborateur $collaborateur): static
    {
        if ($this->collaborateurs->removeElement($collaborateur)) {
            $collaborateur->removeRolesUtilisateur($this);
        }

        return $this;
    }

}
