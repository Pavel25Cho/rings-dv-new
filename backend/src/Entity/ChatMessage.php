<?php

namespace App\Entity;

use App\Repository\ChatMessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatMessageRepository::class)]
#[ORM\Table(name: 'chat_messages')]
#[ORM\Index(columns: ['chat_id'], name: 'idx_chat_id')]
#[ORM\Index(columns: ['is_read'], name: 'idx_is_read')]
class ChatMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Chat::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Chat $chat = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sentMessages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $sender = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $messageText = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isRead = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $emailNotificationSent = false;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'message', targetEntity: Order::class, cascade: ['persist', 'remove'])]
    private ?Order $order = null;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: ChatAttachment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $attachments;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->attachments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): static
    {
        $this->chat = $chat;
        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function getMessageText(): ?string
    {
        return $this->messageText;
    }

    public function setMessageText(string $messageText): static
    {
        $this->messageText = $messageText;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function isEmailNotificationSent(): bool
    {
        return $this->emailNotificationSent;
    }

    public function setEmailNotificationSent(bool $emailNotificationSent): static
    {
        $this->emailNotificationSent = $emailNotificationSent;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        if ($order === null && $this->order !== null) {
            $this->order->setMessage(null);
        }

        if ($order !== null && $order->getMessage() !== $this) {
            $order->setMessage($this);
        }

        $this->order = $order;

        return $this;
    }

    /**
     * @return Collection<int, ChatAttachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(ChatAttachment $attachment): static
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setMessage($this);
        }

        return $this;
    }

    public function removeAttachment(ChatAttachment $attachment): static
    {
        if ($this->attachments->removeElement($attachment)) {
            if ($attachment->getMessage() === $this) {
                $attachment->setMessage(null);
            }
        }

        return $this;
    }

    public function hasAttachments(): bool
    {
        return !$this->attachments->isEmpty();
    }
}
