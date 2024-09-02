<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Section
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'NONE')]
        public readonly string $id,
        #[ORM\Column]
        public readonly ?string $originalSourceId,
        #[ORM\Column]
        public readonly string $url,
        #[ORM\Column]
        public readonly string $title,
        #[ORM\Column]
        public readonly string $content,
    ) {
    }
}
