<?php

namespace App\Entity;

use App\Repository\PollutionEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PollutionEventRepository::class)]
class PollutionEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $evenement = null;

    #[ORM\Column(length: 255)]
    private ?string $typePollution = null;

    #[ORM\Column(length: 255)]
    private ?string $indicePollution = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvenement(): ?string
    {
        return $this->evenement;
    }

    public function setEvenement(string $evenement): static
    {
        $this->evenement = $evenement;

        return $this;
    }

    public function getTypePollution(): ?string
    {
        return $this->typePollution;
    }

    public function setTypePollution(string $typePollution): static
    {
        $this->typePollution = $typePollution;

        return $this;
    }

    public function getIndicePollution(): ?string
    {
        return $this->indicePollution;
    }

    public function setIndicePollution(string $indicePollution): static
    {
        $this->indicePollution = $indicePollution;

        return $this;
    }
}
