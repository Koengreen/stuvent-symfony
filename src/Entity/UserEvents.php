<?php

namespace App\Entity;

use App\Repository\UserEventsRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;

#[ORM\Entity(repositoryClass: UserEventsRepository::class)]
class UserEvents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $accepted = null;

    #[ORM\ManyToOne(inversedBy: 'UserEvents')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'UserEvents')]
    private ?Event $event = null;

    #[ORM\Column(nullable: true)]
    private ?bool $presence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rating = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
    public function getAccepted(): ?Event
    {
        return $this->event;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function isPresence(): ?bool
    {
        return $this->presence;
    }

    public function setPresence(?bool $presence): self
    {
        $this->presence = $presence;

        return $this;
    }

    public static function getUncheckedPresences(EntityManagerInterface $em)
    {
        return $em->getRepository(UserEvents::class)->findBy(['presence' => null]);
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): self
    {
        $this->rating = $rating;

        return $this;
    }



}
