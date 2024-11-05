<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ApiResource]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['card:read', 'card:write', 'related:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['card:read', 'card:write', 'related:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $value = null;

    #[ORM\Column(length: 255)]
    private ?string $capacity = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    /**
     * @var Collection<int, Related>
     */
    #[ORM\OneToMany(targetEntity: Related::class, mappedBy: 'card')]
    private Collection $relateds;

    /**
     * @var Collection<int, Hand>
     */
    #[ORM\OneToMany(targetEntity: Hand::class, mappedBy: 'card')]
    private Collection $hands;

    public function __construct()
    {
        $this->relateds = new ArrayCollection();
        $this->hands = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCapacity(): ?string
    {
        return $this->capacity;
    }

    public function setCapacity(string $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Related>
     */
    public function getRelateds(): Collection
    {
        return $this->relateds;
    }

    public function addRelated(Related $related): static
    {
        if (!$this->relateds->contains($related)) {
            $this->relateds->add($related);
            $related->setCard($this);
        }

        return $this;
    }

    public function removeRelated(Related $related): static
    {
        if ($this->relateds->removeElement($related)) {
            // set the owning side to null (unless already changed)
            if ($related->getCard() === $this) {
                $related->setCard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Hand>
     */
    public function getHands(): Collection
    {
        return $this->hands;
    }

    public function addHand(Hand $hand): static
    {
        if (!$this->hands->contains($hand)) {
            $this->hands->add($hand);
            $hand->setCard($this);
        }

        return $this;
    }

    public function removeHand(Hand $hand): static
    {
        if ($this->hands->removeElement($hand)) {
            // set the owning side to null (unless already changed)
            if ($hand->getCard() === $this) {
                $hand->setCard(null);
            }
        }

        return $this;
    }
}
