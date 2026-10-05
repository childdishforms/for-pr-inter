<?php

declare(strict_types=1);

namespace App\Contract;

interface ContactsRepositoryInterface
{
    public function fetchAllContacts(): array;

    public function fetchContact(int $id): ?array;

    public function addContact(string $name, string $phone, ?string $email, ?string $note): int;

    public function editContact(int $id, array $data): int;

    public function deleteContact(int $id): int;
}
