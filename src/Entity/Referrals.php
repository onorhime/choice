<?php

namespace App\Entity;

use App\Repository\ReferralsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferralsRepository::class)]
class Referrals
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'referrals')]
    private ?User $referrer = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'parent')]
    private Collection $referred;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdat = null;

    public function __construct()
    {
        $this->referred = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferrer(): ?User
    {
        return $this->referrer;
    }

    public function setReferrer(?User $referrer): static
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getReferred(): Collection
    {
        return $this->referred;
    }

    public function addReferred(User $referred): static
    {
        if (!$this->referred->contains($referred)) {
            $this->referred->add($referred);
            $referred->setParent($this);
        }

        return $this;
    }

    public function removeReferred(User $referred): static
    {
        if ($this->referred->removeElement($referred)) {
            // set the owning side to null (unless already changed)
            if ($referred->getParent() === $this) {
                $referred->setParent(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedat(): ?\DateTimeInterface
    {
        return $this->createdat;
    }

    public function setCreatedat(?\DateTimeInterface $createdat): static
    {
        $this->createdat = $createdat;

        return $this;
    }
}
