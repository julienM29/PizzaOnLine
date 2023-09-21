<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateHeureLivraison = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateHeurePreparation = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: DetailCommande::class, orphanRemoval: true)]
    private Collection $detailsCommande;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Collaborateur $collaborateur = null;

    public function __construct()
    {
        $this->detailsCommande = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateHeureLivraison(): ?\DateTimeInterface
    {
        return $this->dateHeureLivraison;
    }

    public function setDateHeureLivraison(\DateTimeInterface $dateHeureLivraison): static
    {
        $this->dateHeureLivraison = $dateHeureLivraison;

        return $this;
    }

    public function getDateHeurePreparation(): ?\DateTimeInterface
    {
        return $this->dateHeurePreparation;
    }

    public function setDateHeurePreparation(\DateTimeInterface $dateHeurePreparation): static
    {
        $this->dateHeurePreparation = $dateHeurePreparation;

        return $this;
    }

    /**
     * @return Collection<int, DetailCommande>
     */
    public function getDetailsCommande(): Collection
    {
        return $this->detailsCommande;
    }

    public function addDetailsCommande(DetailCommande $detailsCommande): static
    {
        if (!$this->detailsCommande->contains($detailsCommande)) {
            $this->detailsCommande->add($detailsCommande);
            $detailsCommande->setCommande($this);
        }

        return $this;
    }

    public function removeDetailsCommande(DetailCommande $detailsCommande): static
    {
        if ($this->detailsCommande->removeElement($detailsCommande)) {
            // set the owning side to null (unless already changed)
            if ($detailsCommande->getCommande() === $this) {
                $detailsCommande->setCommande(null);
            }
        }

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getCollaborateur(): ?Collaborateur
    {
        return $this->collaborateur;
    }

    public function setCollaborateur(?Collaborateur $collaborateur): static
    {
        $this->collaborateur = $collaborateur;

        return $this;
    }
}
