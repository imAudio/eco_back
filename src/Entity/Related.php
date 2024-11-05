<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RelatedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RelatedRepository::class)]
#[ApiResource]
class Related
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['related:read', 'related:write'])]
    private ?int $id = null;


    #[ORM\ManyToOne(targetEntity: Combo::class, inversedBy: 'relateds')]
    #[Groups(['related:read'])]
    private ?Combo $combo = null;

    #[ORM\ManyToOne(targetEntity: Card::class, inversedBy: 'relateds')]
    #[Groups(['related:read'])]
    private ?Card $card = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCombo(): ?Combo
    {
        return $this->combo;
    }

    public function setCombo(?Combo $combo): static
    {
        $this->combo = $combo;

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
}
