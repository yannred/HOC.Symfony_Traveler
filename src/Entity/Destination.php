<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DestinationRepository")
 */
class Destination
{
    use BaseEntityTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ville;

    /** @ORM\Embedded(class = "App\Entity\LatLng") */
    protected $coordinates;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pays", inversedBy="destinations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pays;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Voyage", mappedBy="destination", orphanRemoval=true)
     */
    private $voyages;

    public function __construct()
    {
        $this->voyages = new ArrayCollection();
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCoordinates(): ?LatLng
    {
        return $this->coordinates;
    }

    public function setCoordinates(?LatLng $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * @return Collection|Voyage[]
     */
    public function getVoyages(): Collection
    {
        return $this->voyages;
    }

    public function addVoyage(Voyage $voyage): self
    {
        if (!$this->voyages->contains($voyage)) {
            $this->voyages[] = $voyage;
            $voyage->setDestination($this);
        }

        return $this;
    }

    public function removeVoyage(Voyage $voyage): self
    {
        if ($this->voyages->contains($voyage)) {
            $this->voyages->removeElement($voyage);
            // set the owning side to null (unless already changed)
            if ($voyage->getDestination() === $this) {
                $voyage->setDestination(null);
            }
        }

        return $this;
    }
}
