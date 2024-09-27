<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', nullable: false)]
    public readonly string $id;

    #[ORM\Column(type: 'datetime_immutable')]
    public readonly \DateTimeInterface $createdAt;

    public function __construct(
        #[ORM\Column(type: 'text')]
        public readonly string $content,
        #[ORM\Column(type: 'boolean')]
        public readonly bool $isMe,
    ) {
        $this->id = uuid_create();
        $this->createdAt = new \DateTimeImmutable();
    }
}
