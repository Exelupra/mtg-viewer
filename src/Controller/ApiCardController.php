<?php

namespace App\Controller;

use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/all', name: 'List all cards', methods: ['GET'])]
    #[OA\Get(description: 'Return all cards in the database')]
    #[OA\Parameter(name: 'page', description: 'Page number', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'pageSize', description: 'Number of cards per page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 100))]
    #[OA\Response(response: 200, description: 'List all cards')]
    public function cardAll(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 100);

        $offset = ($page - 1) * $pageSize;

        $cards = $this->entityManager->getRepository(Card::class)
            ->findBy([], null, $pageSize, $offset);

        return $this->json($cards);
    }



    #[Route('/search', name: 'Search cards', methods: ['GET'])]
    #[OA\Get(description: 'Search cards by name and setCode')]
    #[OA\Parameter(name: 'q', description: 'Search term', in: 'query', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'setCode', description: 'Set code', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'List of matching cards')]
    #[OA\Response(response: 400, description: 'Bad request')]
    public function searchCards(Request $request): Response
    {
        $searchTerm = $request->query->get('q');
        $setCode = $request->query->get('setCode');

        // Vérifie si la recherche a au moins 3 caractères
        if (strlen($searchTerm) < 3) {
            return $this->json(['error' => 'Search term must be at least 3 characters'], 400);
        }

        $repository = $this->entityManager->getRepository(Card::class);
        $queryBuilder = $repository->createQueryBuilder('c')
            ->where('c.name LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        // Ajoute le filtre setCode s'il est fourni
        if ($setCode) {
            $queryBuilder->andWhere('c.setCode = :setCode')
                ->setParameter('setCode', $setCode);
        }

        $cards = $queryBuilder->setMaxResults(20) // Limite à 20 résultats
        ->getQuery()
            ->getResult();

        $this->logger->info('API endpoint called: card with ' . $searchTerm . ' and setCode ' . $setCode);
        return $this->json($cards);
    }
    #[Route('/set-codes', name: 'List set codes', methods: ['GET'])]
    #[OA\Get(description: 'List all available set codes')]
    #[OA\Response(response: 200, description: 'List of available set codes')]
    public function listSetCodes(): Response
    {
        $repository = $this->entityManager->getRepository(Card::class);
        $setCodes = $repository->createQueryBuilder('c')
            ->select('DISTINCT c.setCode')
            ->orderBy('c.setCode', 'ASC')
            ->getQuery()
            ->getResult();

        $this->logger->info('API endpoint called: list set codes');
        return $this->json($setCodes);
    }
    #[Route('/{uuid}', name: 'Show card', methods: ['GET'])]
    #[OA\Parameter(name: 'uuid', description: 'UUID of the card', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Get(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {
        $this->logger->info('API endpoint called: Show card with UUID ' . $uuid);
        $card = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $uuid]);
        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }
        return $this->json($card);
    }
}
