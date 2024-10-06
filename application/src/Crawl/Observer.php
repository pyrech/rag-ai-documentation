<?php

namespace App\Crawl;

use App\Entity\Document;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Symfony\Contracts\Service\ResetInterface;

class Observer extends CrawlObserver implements ResetInterface
{
    /** @var Document[] */
    private array $documents = [];

    public function __construct(
        private readonly DocumentExtractor $extractor,
        private readonly LoggerInterface   $logger,
    ) {
    }

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function reset()
    {
        $this->documents = [];
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
        $this->documents = array_merge($this->documents, $this->extractor->extract($stringUrl, $html));
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
