<?php

namespace App\Crawl;

use App\Entity\Section;

class SectionExtractor
{
    /**
     * @return Section[]
     */
    public function extract(string $url, string $html): array
    {
        // All h1-h6 titles
        $xPathTitles = implode(' | ', array_map(
            fn (int $i): string => sprintf('//h%d[1]', $i),
            range(1, 6)
        ));

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $sections = [];

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query($xPathTitles);

        /** @var \DOMElement $node */
        foreach ($nodes as $node) {
            $documentUrl = $url;
            $originalSourceId = null;

            if ($node->hasAttribute('id')) {
                $documentUrl .= '#' . $node->getAttribute('id');
                $originalSourceId = $node->getAttribute('id');
            }
            $content = '';
            $contentNode = $node->nextSibling;

            while ($contentNode && !$this->isTitle($contentNode)) {
                $content .= $dom->saveHTML($contentNode);
                $contentNode = $contentNode->nextSibling;
            }

            $sections[] = new Section(
                hash('xxh3', $documentUrl),
                $originalSourceId,
                $documentUrl,
                $this->cleanContent($node->textContent),
                $this->cleanContent($content),
            );
        }

        return $sections;
    }

    private function isTitle(\DOMNode $node): bool
    {
        if (!\in_array($node->nodeName, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
            return false;
        }

        return true;
    }

    private function cleanContent(string $content): string
    {
        return trim(
            str_replace('¶', '',
                strip_tags($content, '<code>')
            )
        );
    }
}
