<?php

namespace App\Crawl;

use Spatie\Crawler\Crawler as SpatieCrawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;

class Crawler
{
    public function __construct(
        private readonly Observer $observer,
    ) {
    }

    public function crawl(string $url): void
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

        dump($this->observer->getSections());
    }
}
