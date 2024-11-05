<?php

namespace App\Entity;

use App\Repository\ComboRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ComboRepository::class)]
class Combo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['combo:read', 'combo:write', 'related:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['combo:read', 'combo:write', 'related:read'])]
    private ?string $name = null;
    #[Groups(['related:read'])]
    #[ORM\Column]
    private ?int $value = null;

    /**
     * @var Collection<int, Related>
     */
    #[ORM\OneToMany(targetEntity: Related::class, mappedBy: 'combo')]
    private Collection $relateds;

    public function __construct()
    {
        $this->relateds = new ArrayCollection();
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

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

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
            $related->setCombo($this);
        }

        return $this;
    }

    public function removeRelated(Related $related): static
    {
        if ($this->relateds->removeElement($related)) {
            // set the owning side to null (unless already changed)
            if ($related->getCombo() === $this) {
                $related->setCombo(null);
            }
        }

        return $this;
    }
}
