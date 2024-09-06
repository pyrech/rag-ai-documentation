<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Section
{
    public const VECTOR_LENGTH = 1536;

    #[ORM\Column(type: 'integer')]
    public readonly int $tokens;
    #[ORM\Column(type: 'vector', length: self::VECTOR_LENGTH)]
    public readonly array $embeddings;

    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'NONE')]
        #[ORM\Column(type: 'string', nullable: false)]
        public readonly string $id,
        #[ORM\Column(type: 'text')]
        public readonly string $url,
        #[ORM\Column(type: 'text')]
        public readonly string $title,
        #[ORM\Column(type: 'text')]
        public readonly string $content,
    ) {
    }

    public function setEmbeddings(array $embeddings): void
    {
        $this->tokens = \count($embeddings);
        $this->embeddings = $embeddings;
    }
}
