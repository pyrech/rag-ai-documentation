<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
class Section
{
    public const VECTOR_LENGTH = 1536;

    #[ORM\Column(type: 'integer')]
    public int $tokens;
    /** @var float[] */
    #[ORM\Column(type: 'vector', length: self::VECTOR_LENGTH)]
    public array $embeddings;

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

    /**
     * @param float[] $embeddings
     */
    public function setEmbeddings(array $embeddings): void
    {
        $this->tokens = \count($embeddings);
        $this->embeddings = $embeddings;
    }
}
