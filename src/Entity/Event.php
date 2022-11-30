<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Company = null;

    #[ORM\Column(length: 255)]
    private ?string $Hourstype = null;

    #[ORM\Column(length: 255)]
    private ?string $Eventtype = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;

    #[ORM\Column(length: 255)]
    private ?string $time = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $aantalUur = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: UserEvents::class)]
    private Collection $UserEvents;

    public function __construct()
    {
        $this->UserEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->Company;
    }

    public function setCompany(?string $Company): self
    {
        $this->Company = $Company;

        return $this;
    }

    public function getHourstype(): ?string
    {
        return $this->Hourstype;
    }

    public function setHourstype(string $Hourstype): self
    {
        $this->Hourstype = $Hourstype;

        return $this;
    }

    public function getEventtype(): ?string
    {
        return $this->Eventtype;
    }

    public function setEventtype(string $Eventtype): self
    {
        $this->Eventtype = $Eventtype;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(string $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getAantalUur(): ?int
    {
        return $this->aantalUur;
    }

    public function setAantalUur(int $aantalUur): self
    {
        $this->aantalUur = $aantalUur;

        return $this;
    }

    /**
     * @return Collection<int, UserEvents>
     */
    public function getUserEvents(): Collection
    {
        return $this->UserEvents;
    }

    public function addUserEvent(UserEvents $userEvent): self
    {
        if (!$this->UserEvents->contains($userEvent)) {
            $this->UserEvents->add($userEvent);
            $userEvent->setEvent($this);
        }

        return $this;
    }

    public function removeUserEvent(UserEvents $userEvent): self
    {
        if ($this->UserEvents->removeElement($userEvent)) {
            // set the owning side to null (unless already changed)
            if ($userEvent->getEvent() === $this) {
                $userEvent->setEvent(null);
            }
        }

        return $this;
    }
}
