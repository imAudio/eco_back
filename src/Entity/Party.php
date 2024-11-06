<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\PartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartyRepository::class)]
class Party
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'parties')]
    private ?user $winner = null;

    #[ORM\Column]
    private ?int $turn = null;

    /**
     * @var Collection<int, Play>
     */
    #[ORM\OneToMany(targetEntity: Play::class, mappedBy: 'party')]
    private Collection $plays;

    /**
     * @var Collection<int, Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'party')]
    private Collection $players;

    public function __construct()
    {
        $this->plays = new ArrayCollection();
        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getWinner(): ?user
    {
        return $this->winner;
    }

    public function setWinner(?user $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function getTurn(): ?int
    {
        return $this->turn;
    }

    public function setTurn(int $turn): static
    {
        $this->turn = $turn;

        return $this;
    }

    /**
     * @return Collection<int, Play>
     */
    public function getPlays(): Collection
    {
        return $this->plays;
    }

    public function addPlay(Play $play): static
    {
        if (!$this->plays->contains($play)) {
            $this->plays->add($play);
            $play->setParty($this);
        }

        return $this;
    }

    public function removePlay(Play $play): static
    {
        if ($this->plays->removeElement($play)) {
            // set the owning side to null (unless already changed)
            if ($play->getParty() === $this) {
                $play->setParty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setParty($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getParty() === $this) {
                $player->setParty(null);
            }
        }

        return $this;
    }
}
