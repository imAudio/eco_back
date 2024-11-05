<?php

namespace App\Entity;

use App\Repository\HandRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HandRepository::class)]
class Hand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['hand:read', 'hand:write'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'relateds')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['hand:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Card::class, inversedBy: 'relateds')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['hand:read'])]
    private ?Card $card = null;

    #[ORM\ManyToOne(targetEntity: Party::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['hand:read'])]
    private ?Party $party = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): static
    {
        $this->card = $card;

        return $this;
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): static
    {
        $this->party = $party;

        return $this;
    }
}
