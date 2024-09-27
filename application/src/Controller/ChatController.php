<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(DOCUMENTATION_URL)%')]
        private readonly string $documentationUrl,
    ) {
    }

    #[Route('/', name: 'chat_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('chat/index.html.twig', [
            'url' => $this->documentationUrl,
        ]);
    }
}
