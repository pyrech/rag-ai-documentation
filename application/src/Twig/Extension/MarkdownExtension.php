<?php

namespace App\Twig\Extension;

use League\CommonMark\MarkdownConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    public function __construct(
        private readonly MarkdownConverter $markdownConverter,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', $this->markdownConverter->convert(...), ['is_safe' => ['html']]),
        ];
    }
}
