<?php

namespace App\Command;

use App\Crawl\Crawler;
use App\Entity\Section;
use App\OpenAI\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:crawl',
    description: 'Crawl the given website to extract content',
)]
class CrawlCommand extends Command
{
    public function __construct(
        private readonly Crawler $crawler,
        private readonly Client $client,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::OPTIONAL, 'Url to crawl')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');

        if (false === filter_var($url, \FILTER_VALIDATE_URL)) {
            $io->error('Invalid url.');

            return Command::FAILURE;
        }

        $host = parse_url($url, \PHP_URL_HOST);

        $io->info('Removing data for this domain.');

        $count = $this->entityManager->createQuery(sprintf('DELETE FROM %s s WHERE s.url LIKE :host', Section::class))
            ->setParameter('host', "%s://{$host}%")
            ->execute()
        ;

        $this->entityManager->flush();

        $io->note(sprintf('Removed %d sections.', $count));

        $io->info('Crawling the website.');

        $sections = $this->crawler->crawl($url);

        $io->note(sprintf('Found %d sections.', \count($sections)));

        $io->info('Extracting embeddings.');

        foreach ($sections as $section) {
            $embeddings = $this->client->getEmbeddings($section->content);

            $section->setEmbeddings($embeddings);
        }

        $io->info('Persisting data.');

        foreach ($sections as $section) {
            $this->entityManager->persist($section);
        }

        $this->entityManager->flush();

        $io->success('Finished crawling this website.');

        return Command::SUCCESS;
    }
}
