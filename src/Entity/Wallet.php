<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $btc = null;

    #[ORM\Column(length: 255)]
    private ?string $eth = null;

    #[ORM\Column(length: 255)]
    private ?string $usdt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBtc(): ?string
    {
        return $this->btc;
    }

    public function setBtc(string $btc): static
    {
        $this->btc = $btc;

        return $this;
    }

    public function geteth(): ?string
    {
        return $this->eth;
    }

    public function seteth(string $eth): static
    {
        $this->eth = $eth;

        return $this;
    }

    public function getUsdt(): ?string
    {
        return $this->usdt;
    }

    public function setUsdt(string $usdt): static
    {
        $this->usdt = $usdt;

        return $this;
    }
}
