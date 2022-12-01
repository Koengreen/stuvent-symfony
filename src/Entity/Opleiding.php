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

    #[ORM\OneToMany(mappedBy: 'opleiding', targetEntity: User::class)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'opleiding', targetEntity: Event::class)]
    private Collection $Event;

    public function __construct()
    {
        $this->opleiding_id = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->Event = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setOpleiding($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getOpleiding() === $this) {
                $user->setOpleiding(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvent(): Collection
    {
        return $this->Event;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->Event->contains($event)) {
            $this->Event->add($event);
            $event->setOpleiding($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->Event->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getOpleiding() === $this) {
                $event->setOpleiding(null);
            }
        }

        return $this;
    }
}
