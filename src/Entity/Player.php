<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ApiResource]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['player:read', 'player:write'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'players')]
    #[Groups(['player:read'])]
    private ?User $user = null;


    #[ORM\ManyToOne(targetEntity: Party::class, inversedBy: 'players')]
    #[Groups(['player:read'])]
    private ?Party $party = null;

    #[ORM\Column]
    private ?int $point = null;

    #[ORM\Column(nullable: true)]
    private ?int $order_turn = null;

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

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): static
    {
        $this->party = $party;

        return $this;
    }

    public function getPoint(): ?int
    {
        return $this->point;
    }

    public function setPoint(int $point): static
    {
        $this->point = $point;

        return $this;
    }

    public function getOrderTurn(): ?int
    {
        return $this->order_turn;
    }

    public function setOrderTurn(?int $order_turn): static
    {
        $this->order_turn = $order_turn;

        return $this;
    }
}
