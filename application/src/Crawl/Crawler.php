<?php

namespace App\Crawl;

use App\Entity\Document;
use Spatie\Crawler\Crawler as SpatieCrawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;

class Crawler
{
    public function __construct(
        private readonly Observer $observer,
    ) {
    }

    /**
     * @return Document[]
     */
    public function crawl(string $url): array
    {
        $this->observer->reset();

        SpatieCrawler::create()
            ->setCrawlObserver($this->observer)
            ->setCrawlProfile(new CrawlInternalUrls($url))
            ->acceptNofollowLinks()
            ->setMaximumDepth(1000)
            // ->setConcurrency(1)
            // ->setMaximumResponseSize(1024 * 1024 * 3)
            // ->setParseableMimeTypes(['text/html', 'text/plain'])
            ->startCrawling($url)
        ;

        return $this->observer->getDocuments();
    }
}
