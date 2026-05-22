<?php

namespace App\Message;

class SendEmailMessage
{
    public function __construct(
        private string $to,
        private string $subject,
        private string $body,
        private array $context = []
    ) {}

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
