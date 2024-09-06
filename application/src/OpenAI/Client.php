<?php

namespace App\OpenAI;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    public function __construct(
        #[Autowire('%env(OPENAI_API_KEY)%')]
        private readonly string $apiKey,
        private readonly HttpClientInterface $client,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function getEmbeddings(string $content): array
    {
        $cacheKey = md5($content);

        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            $response = $this->client->request('POST', 'https://api.openai.com/v1/embeddings', [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                ],
                'json' => [
                    'model' => 'text-embedding-3-small', // "small" model will produce vectors of 1536 dimensions
                    'input' => $content,
                ],
            ]);

            $data = $response->toArray();

            $cacheItem->set($data);
            $this->cache->save($cacheItem);
        }

        $data = $cacheItem->get();

        if (!$data['data'][0]['embedding'] ?? false) {
            throw new \RuntimeException('Could not get embeddings from OpenAI response.');
        }

        return $data['data'][0]['embedding'];
    }
}
