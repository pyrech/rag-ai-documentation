<?php

namespace App\OpenAI;

use App\Entity\Message;
use App\Entity\Section;
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

    /**
     * @return array<float>
     */
    public function getEmbeddings(string $content): array
    {
        $cacheKey = md5($content);

        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            $data = $this->call('/v1/embeddings', [
                'model' => 'text-embedding-3-small', // "small" model will produce vectors of 1536 dimensions
                'input' => $content,
            ]);

            $cacheItem->set($data);
            $this->cache->save($cacheItem);
        }

        $data = $cacheItem->get();

        if (!($data['data'][0]['embedding'] ?? false)) {
            throw new \RuntimeException('Could not get embeddings from OpenAI response.');
        }

        return $data['data'][0]['embedding'];
    }

    /**
     * @param Section[] $sections
     * @param Message[] $historyMessages
     */
    public function getAnswer(array $sections, array $historyMessages): string
    {
        $prompt = 'You are a friendly chatbot. \
    You respond in a concise, technically credible tone. \
    You only use information from the provided information.';

        $messages = [
            [
                'role' => 'system',
                'content' => $prompt,
            ],
        ];

        foreach ($historyMessages as $message) {
            $messages[] = [
                'role' => $message->isMe ? 'user' : 'assistant',
                'content' => $message->content,
            ];
        }

        $relevantInformation = 'Relevant information: \n';
        foreach ($sections as $section) {
            $relevantInformation .= "{$section->title} - {$section->content} \n";
        }

        $messages[] = [
            'role' => 'assistant',
            'content' => $relevantInformation,
        ];

        $data = $this->call('/v1/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => $messages,
        ]);

        if (!($data['choices'][0]['message']['content'] ?? false)) {
            throw new \RuntimeException('Could not get suggestion from OpenAI response.');
        }

        return $data['choices'][0]['message']['content'];
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function call(string $endpoint, array $data): array
    {
        $response = $this->client->request('POST', "https://api.openai.com{$endpoint}", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
            ],
            'json' => $data,
        ]);

        return $response->toArray();
    }
}
