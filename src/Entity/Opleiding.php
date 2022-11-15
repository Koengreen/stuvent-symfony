<?php

namespace App\Entity;

use App\Repository\OpleidingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpleidingRepository::class)]
class Opleiding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'opleiding', targetEntity: User::class)]
    private Collection $opleiding_id;

    public function __construct()
    {
        $this->opleiding_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getOpleidingId(): Collection
    {
        return $this->opleiding_id;
    }

    public function addOpleidingId(User $opleidingId): self
    {
        if (!$this->opleiding_id->contains($opleidingId)) {
            $this->opleiding_id->add($opleidingId);
            $opleidingId->setOpleiding($this);
        }

        return $this;
    }

    public function removeOpleidingId(User $opleidingId): self
    {
        if ($this->opleiding_id->removeElement($opleidingId)) {
            // set the owning side to null (unless already changed)
            if ($opleidingId->getOpleiding() === $this) {
                $opleidingId->setOpleiding(null);
            }
        }

        return $this;
    }
}
