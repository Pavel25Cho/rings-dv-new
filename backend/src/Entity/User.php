<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isBlocked = false;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $cart = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Chat::class, orphanRemoval: true)]
    private Collection $chats;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: ChatMessage::class)]
    private Collection $sentMessages;

    public function __construct()
    {
        $this->chats = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->cart = [];
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): static
    {
        $this->isBlocked = $isBlocked;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles, true);
    }

    public function getCart(): ?array
    {
        return $this->cart ?? [];
    }

    public function setCart(?array $cart): static
    {
        $this->cart = $cart;
        return $this;
    }

    public function addToCart(int $ringId, int $quantity): static
    {
        if (!$this->cart) {
            $this->cart = [];
        }

        $found = false;
        foreach ($this->cart as &$item) {
            if ($item['ringId'] === $ringId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->cart[] = [
                'ringId' => $ringId,
                'quantity' => $quantity
            ];
        }

        return $this;
    }

    public function removeFromCart(int $ringId): static
    {
        if (!$this->cart) {
            return $this;
        }

        $this->cart = array_values(array_filter($this->cart, function($item) use ($ringId) {
            return $item['ringId'] !== $ringId;
        }));

        return $this;
    }

    public function updateCartItemQuantity(int $ringId, int $quantity): static
    {
        if (!$this->cart) {
            return $this;
        }

        foreach ($this->cart as &$item) {
            if ($item['ringId'] === $ringId) {
                $item['quantity'] = $quantity;
                break;
            }
        }

        return $this;
    }

    public function clearCart(): static
    {
        $this->cart = [];
        return $this;
    }
}
