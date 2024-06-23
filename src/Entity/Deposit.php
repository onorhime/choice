<?php

namespace App\Entity;

use App\Repository\DepositRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepositRepository::class)]
class Deposit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'deposits')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plan = null;

    #[ORM\Column(nullable: true)]
    private ?float $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $method = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $duration = null;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(?string $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

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

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getProfit(): ?string
    {
        return $this->profit;
    }

    public function setProfit(?string $profit): static
    {
        $this->profit = $profit;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }
}
