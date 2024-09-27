<?php

namespace App\Twig\Components;

use App\Entity\Message;
use App\Form\ChatMessageType;
use App\OpenAI\Client;
use App\Repository\MessageRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Chat', template: 'components/Chat.html.twig')]
class Chat extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public function __construct(
        private readonly Client $client,
        private readonly MessageRepository $messageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function hasValidationErrors(): bool
    {
        return $this->getForm()->isSubmitted() && !$this->getForm()->isValid();
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messageRepository->findLatest();
    }

    #[LiveAction]
    public function submit(): void
    {
        $this->submitForm();

        $input = $this->getForm()->get('input')->getData();

        $message = new Message($input, true);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $embeddings = $this->client->getEmbeddings($input);
        $sections = $this->sectionRepository->findNearest($embeddings);
        $messages = $this->messageRepository->findLatest();
        $answer = $this->client->getAnswer($sections, $messages);

        $message = new Message($answer, false);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $this->resetForm();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ChatMessageType::class);
    }
}