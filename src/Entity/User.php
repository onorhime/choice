<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $visiblepassword = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdat = null;

    /**
     * @var Collection<int, Referrals>
     */
    #[ORM\OneToMany(targetEntity: Referrals::class, mappedBy: 'referrer')]
    private Collection $referrals;

    #[ORM\ManyToOne(inversedBy: 'referred')]
    private ?Referrals $parent = null;

    #[ORM\Column(nullable: true)]
    private ?float $balance = null;

    #[ORM\Column(nullable: true)]
    private ?float $totaldeposit = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalwithdrawal = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalinterests = null;

    /**
     * @var Collection<int, Deposit>
     */
    #[ORM\OneToMany(targetEntity: Deposit::class, mappedBy: 'user')]
    private Collection $deposits;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $btc = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $eth = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $usdt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dob = null;

    /**
     * @var Collection<int, Withdrawal>
     */
    #[ORM\OneToMany(targetEntity: Withdrawal::class, mappedBy: 'user')]
    private Collection $withdrawals;

    public function __construct()
    {
        $this->referrals = new ArrayCollection();
        $this->deposits = new ArrayCollection();
        $this->withdrawals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getVisiblepassword(): ?string
    {
        return $this->visiblepassword;
    }

    public function setVisiblepassword(?string $visiblepassword): static
    {
        $this->visiblepassword = $visiblepassword;

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

    /**
     * @return Collection<int, Referrals>
     */
    public function getReferrals(): Collection
    {
        return $this->referrals;
    }

    public function addReferral(Referrals $referral): static
    {
        if (!$this->referrals->contains($referral)) {
            $this->referrals->add($referral);
            $referral->setReferrer($this);
        }

        return $this;
    }

    public function removeReferral(Referrals $referral): static
    {
        if ($this->referrals->removeElement($referral)) {
            // set the owning side to null (unless already changed)
            if ($referral->getReferrer() === $this) {
                $referral->setReferrer(null);
            }
        }

        return $this;
    }

    public function getParent(): ?Referrals
    {
        return $this->parent;
    }

    public function setParent(?Referrals $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getTotaldeposit(): ?float
    {
        return $this->totaldeposit;
    }

    public function setTotaldeposit(?float $totaldeposit): static
    {
        $this->totaldeposit = $totaldeposit;

        return $this;
    }

    public function getTotalwithdrawal(): ?float
    {
        return $this->totalwithdrawal;
    }

    public function setTotalwithdrawal(?float $totalwithdrawal): static
    {
        $this->totalwithdrawal = $totalwithdrawal;

        return $this;
    }

    public function getTotalinterests(): ?float
    {
        return $this->totalinterests;
    }

    public function setTotalinterests(?float $totalinterests): static
    {
        $this->totalinterests = $totalinterests;

        return $this;
    }

    /**
     * @return Collection<int, Deposit>
     */
    public function getDeposits(): Collection
    {
        return $this->deposits;
    }

    public function addDeposit(Deposit $deposit): static
    {
        if (!$this->deposits->contains($deposit)) {
            $this->deposits->add($deposit);
            $deposit->setUser($this);
        }

        return $this;
    }

    public function removeDeposit(Deposit $deposit): static
    {
        if ($this->deposits->removeElement($deposit)) {
            // set the owning side to null (unless already changed)
            if ($deposit->getUser() === $this) {
                $deposit->setUser(null);
            }
        }

        return $this;
    }

    public function getBtc(): ?string
    {
        return $this->btc;
    }

    public function setBtc(?string $btc): static
    {
        $this->btc = $btc;

        return $this;
    }

    public function getEth(): ?string
    {
        return $this->eth;
    }

    public function setEth(?string $eth): static
    {
        $this->eth = $eth;

        return $this;
    }

    public function getUsdt(): ?string
    {
        return $this->usdt;
    }

    public function setUsdt(?string $usdt): static
    {
        $this->usdt = $usdt;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getDob(): ?string
    {
        return $this->dob;
    }

    public function setDob(?string $dob): static
    {
        $this->dob = $dob;

        return $this;
    }

    /**
     * @return Collection<int, Withdrawal>
     */
    public function getWithdrawals(): Collection
    {
        return $this->withdrawals;
    }

    public function addWithdrawal(Withdrawal $withdrawal): static
    {
        if (!$this->withdrawals->contains($withdrawal)) {
            $this->withdrawals->add($withdrawal);
            $withdrawal->setUser($this);
        }

        return $this;
    }

    public function removeWithdrawal(Withdrawal $withdrawal): static
    {
        if ($this->withdrawals->removeElement($withdrawal)) {
            // set the owning side to null (unless already changed)
            if ($withdrawal->getUser() === $this) {
                $withdrawal->setUser(null);
            }
        }

        return $this;
    }
}
