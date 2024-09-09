<?php

namespace App\Controller;

use App\Form\ChatMessageType;
use App\OpenAI\Client;
use App\Repository\SectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly SectionRepository $sectionRepository,
    ) {
    }

    #[Route('/', name: 'home')]
    #[Route('/chat', name: 'chat_index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ChatMessageType::class);

        $input = $suggestions = null;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $input = $form->getData()['input'];

            $embeddings = $this->client->getEmbeddings($input);

            $sections = $this->sectionRepository->findNearest($embeddings);

            $suggestions = $this->client->getSuggestions($sections, $input);
        }

        return $this->render('chat/index.html.twig', [
            'form' => $form,
            'input' => $input,
            'suggestions' => $suggestions,
        ]);
    }
}
