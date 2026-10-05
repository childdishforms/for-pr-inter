<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\ContactsRepositoryInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Info(title: 'Contacts API', version: '1.0.0')]
final class ApiController extends AbstractController
{
    public function __construct(
        private readonly ContactsRepositoryInterface $contacts
    ) {
    }

    #[Route('/api/contacts', name: 'getContacts', methods: ['GET'])]
    #[OA\Get(path: '/api/contacts', summary: 'Список контактов', responses: [
        new OA\Response(response: 200, description: 'Список контактов'),
    ])]
    public function getContacts(): JsonResponse
    {
        return $this->json($this->contacts->fetchAllContacts());
    }

    #[Route('/api/contacts/{id}', name: 'getContact', methods: ['GET'])]
    #[OA\Get(path: '/api/contacts/{id}', summary: 'Контакт по ID', parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ], responses: [
        new OA\Response(response: 200, description: 'Контакт'),
        new OA\Response(response: 404, description: 'Контакт не найден'),
    ])]
    public function getContact(int $id): JsonResponse
    {
        $contact = $this->contacts->fetchContact($id);

        return $contact === null
            ? $this->json(['error' => 'Контакт не найден.'], Response::HTTP_NOT_FOUND)
            : $this->json($contact);
    }

    #[Route('/api/contacts', name: 'addContact', methods: ['POST'])]
    #[OA\Post(path: '/api/contacts', summary: 'Добавить контакт', requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(type: 'object', required: ['name', 'phone'], properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'phone', type: 'string'),
            new OA\Property(property: 'email', type: 'string', nullable: true),
            new OA\Property(property: 'note', type: 'string', nullable: true),
        ]),
    ), responses: [
        new OA\Response(response: 201, description: 'Контакт создан'),
        new OA\Response(response: 400, description: 'Не указаны имя или телефон'),
    ])]
    public function addContact(Request $request): JsonResponse
    {
        $data = $request->toArray();

        if (!is_string($data['name'] ?? null) || trim($data['name']) === ''
            || !is_string($data['phone'] ?? null) || trim($data['phone']) === '') {
            return $this->json(['error' => 'Укажите имя и телефон.'], Response::HTTP_BAD_REQUEST);
        }

        $id = $this->contacts->addContact(
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['note'] ?? null,
        );

        return $this->json($this->contacts->fetchContact($id), Response::HTTP_CREATED);
    }

    #[Route('/api/contacts/{id}', name: 'editContact', methods: ['PATCH'])]
    #[OA\Patch(path: '/api/contacts/{id}', summary: 'Изменить контакт', parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object', properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'phone', type: 'string'),
        new OA\Property(property: 'email', type: 'string', nullable: true),
        new OA\Property(property: 'note', type: 'string', nullable: true),
    ])), responses: [
        new OA\Response(response: 200, description: 'Контакт изменён'),
        new OA\Response(response: 400, description: 'Нет полей для изменения'),
        new OA\Response(response: 404, description: 'Контакт не найден'),
    ])]
    public function editContact(int $id, Request $request): JsonResponse
    {
        $data = array_intersect_key($request->toArray(), [
            'name' => true,
            'phone' => true,
            'email' => true,
            'note' => true,
        ]);

        if ($data === []) {
            return $this->json(['error' => 'Нет полей для изменения.'], Response::HTTP_BAD_REQUEST);
        }

        if ($this->contacts->editContact($id, $data) === 0) {
            return $this->json(['error' => 'Контакт не найден.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->contacts->fetchContact($id));
    }

    #[Route('/api/contacts/{id}', name: 'deleteContact', methods: ['DELETE'])]
    #[OA\Delete(path: '/api/contacts/{id}', summary: 'Удалить контакт', parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ], responses: [
        new OA\Response(response: 200, description: 'Контакт удалён'),
        new OA\Response(response: 404, description: 'Контакт не найден'),
    ])]
    public function deleteContact(int $id): JsonResponse
    {
        if ($this->contacts->deleteContact($id) === 0) {
            return $this->json(['error' => 'Контакт не найден.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['deleted' => true]);
    }
}
