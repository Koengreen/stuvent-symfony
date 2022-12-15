<?php

namespace App\Entity;

use App\Repository\AboutPageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AboutPageRepository::class)]
class AboutPage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $abouttext = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAbouttext(): ?string
    {
        return $this->abouttext;
    }

    public function setAbouttext(string $abouttext): self
    {
        $this->abouttext = $abouttext;

        return $this;
    }
}
