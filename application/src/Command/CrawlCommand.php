<?php

namespace App\Command;

use App\Crawl\Crawler;
use App\Entity\Document;
use App\OpenAI\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:crawl',
    description: 'Crawl the website to extract content',
)]
class CrawlCommand extends Command
{
    public function __construct(
        private readonly Crawler $crawler,
        private readonly Client $client,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%env(DOCUMENTATION_URL)%')]
        private readonly string $documentationUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $this->documentationUrl;

        if (false === filter_var($url, \FILTER_VALIDATE_URL)) {
            $io->error('Invalid url.');

            return Command::FAILURE;
        }

        $host = parse_url($url, \PHP_URL_HOST);

        $io->info('Removing data for this domain.');

        $count = $this->entityManager->createQuery(sprintf('DELETE FROM %s s WHERE s.url LIKE :host', Document::class))
            ->setParameter('host', "%://{$host}%")
            ->execute()
        ;

        $this->entityManager->flush();

        $io->note(sprintf('Removed %d documents.', $count));

        $io->info('Crawling the website.');

        $documents = $this->crawler->crawl($url);

        $io->note(sprintf('Found %d documents.', \count($documents)));

        $io->info('Extracting embeddings.');

        foreach ($documents as $document) {
            $embeddings = $this->client->getEmbeddings($document->content);

            $document->setEmbeddings($embeddings);
        }

        $io->info('Persisting data.');

        foreach ($documents as $document) {
            $this->entityManager->persist($document);
        }

        $this->entityManager->flush();

        $io->success('Finished crawling this website.');

        return Command::SUCCESS;
    }
}
