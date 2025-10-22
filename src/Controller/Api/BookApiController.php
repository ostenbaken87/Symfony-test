<?php

namespace App\Controller\Api;

use App\DTO\UpdateBookRequest;
use App\Repository\BookRepository;
use App\Serializer\BookSerializer;
use App\Service\BookService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/books')]
class BookApiController extends AbstractController
{
    public function __construct(
        private BookSerializer $bookSerializer
    ) {
    }

    #[Route('/list', name: 'api_books_list', methods: ['GET'])]
    public function list(Request $request, BookRepository $bookRepository): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 50);

        $result = $bookRepository->findPaginatedWithAuthor($page, $limit);

        return $this->json([
            'success' => true,
            'data' => $this->bookSerializer->toArrayCollection($result['books']),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
                'pages' => ceil($result['total'] / $limit),
            ],
        ]);
    }

    #[Route('/by-id', name: 'api_books_by_id', methods: ['GET'])]
    public function byId(Request $request, BookRepository $bookRepository): JsonResponse
    {
        $id = $request->query->getInt('id');

        if (!$id) {
            return $this->json([
                'success' => false,
                'error' => 'Parameter "id" is required',
            ], Response::HTTP_BAD_REQUEST);
        }

        $book = $bookRepository->find($id);

        if (!$book) {
            return $this->json([
                'success' => false,
                'error' => 'Book not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $this->bookSerializer->toArray($book),
        ]);
    }

    #[Route('/update', name: 'api_books_update', methods: ['POST'])]
    public function update(
        Request $request,
        BookRepository $bookRepository,
        BookService $bookService,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $dto = UpdateBookRequest::fromArray($data);

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'success' => false,
                'error' => (string) $errors,
            ], Response::HTTP_BAD_REQUEST);
        }

        $book = $bookRepository->find($dto->id);

        if (!$book) {
            return $this->json([
                'success' => false,
                'error' => 'Book not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $result = $bookService->updateBook($book, $dto);

        if (!$result['success']) {
            return $this->json([
                'success' => false,
                'errors' => $result['errors'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $this->bookSerializer->toArrayShort($book),
        ]);
    }

    #[Route('/{id}', name: 'api_books_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        BookRepository $bookRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $book = $bookRepository->find($id);

        if (!$book) {
            return $this->json([
                'success' => false,
                'error' => 'Book not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($book);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Book deleted successfully',
        ]);
    }
}

