<?php

namespace App\Crawl;

use App\Entity\Section;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class SectionExtractor
{
    /**
     * @return Section[]
     */
    public function extract(string $url, string $html): array
    {
        $crawler = new DomCrawler($html);
        $crawler = $crawler->filter('h1, h2, h3, h4, h5, h6');

        // Iterate over the h1-h6 titles
        foreach ($crawler as $node) {
            $sectionUrl = $url;

            // Attach an anchor to the URL if title has an id
            if ($node instanceof \DOMElement && $node->hasAttribute('id')) {
                $sectionUrl .= '#' . $node->getAttribute('id');
            }

            $title = $this->cleanContent($node->textContent);
            $content = '';
            $contentNode = $node->nextSibling;

            // Parse the content until the next title
            while ($contentNode && !$this->isTitle($contentNode)) {
                $content .= $contentNode->ownerDocument->saveHTML($contentNode);
                $contentNode = $contentNode->nextSibling;
            }

            $sections[] = new Section(
                $sectionUrl,
                $title,
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
