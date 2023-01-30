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

    #[ORM\Column(type: "datetime")]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $aantalUur = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: UserEvents::class)]
    private Collection $UserEvents;

    #[ORM\ManyToOne(inversedBy: 'Event')]
    private ?Opleiding $opleiding = null;

    #[ORM\Column]
    private ?int $niveau = null;

    #[ORM\Column(length: 255)]
    private ?string $attendees = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $enddate = null;

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


    public function getDate(): ?string
    {
        if($this->date){
            return $this->date->format('Y-m-d H:i:s');
        }
        return null;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;
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

    public function getOpleiding(): ?Opleiding
    {
        return $this->opleiding;
    }

    public function setOpleiding(?Opleiding $opleiding): self
    {
        $this->opleiding = $opleiding;

        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(int $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getAttendees(): ?string
    {
        return $this->attendees;
    }

    public function setAttendees(string $attendees): self
    {
        $this->attendees = $attendees;

        return $this;
    }

    public function getEnddate(): ?\DateTimeInterface
    {
        return $this->enddate;
    }

    public function setEnddate(\DateTimeInterface $enddate): self
    {
        $this->enddate = $enddate;

        return $this;
    }
}
