<?php

namespace App\Crawl;

use App\Entity\Section;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Symfony\Contracts\Service\ResetInterface;

class Observer extends CrawlObserver implements ResetInterface
{
    /** @var Section[] */
    private array $sections = [];

    public function __construct(
        private readonly SectionExtractor $extractor,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return Section[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function reset()
    {
        $this->sections = [];
    }

    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        $html = (string) $response->getBody();

        if (!$html) {
            return;
        }

        $stringUrl = (string) $url;
        $this->sections = array_merge($this->sections, $this->extractor->extract($stringUrl, $html));
    }

    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        $this->logger->error('Could not crawl this url.', [
            'url' => (string) $url,
            'exception' => $requestException->getMessage(),
        ]);
    }

    public function finishedCrawling(): void
    {

    }
}
