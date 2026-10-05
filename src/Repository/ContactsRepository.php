<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\ContactsRepositoryInterface;
use Doctrine\DBAL\Connection;
use RuntimeException;

final class ContactsRepository implements ContactsRepositoryInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function fetchAllContacts(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT
                id,
                name,
                phone,
                email,
                note
            FROM
                contacts
            ORDER BY id',
        );
    }

    public function fetchContact(int $id): ?array
    {
        $contact = $this->connection->fetchAssociative(
            'SELECT id,
                name,
                phone,
                email,
                note
            FROM
                contacts
            WHERE id = :id',
            ['id' => $id],
        );

        return $contact === false ? null : $contact;
    }

    public function addContact(string $name, string $phone, ?string $email, ?string $note): int
    {
        $id = $this->connection->fetchOne(
            'INSERT INTO contacts
                (name, phone, email, note)
             VALUES
                (:name, :phone, :email, :note)
             RETURNING id',
            [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'note' => $note,
            ],
        );

        if ($id === false) {
            throw new RuntimeException('Contact was not created.');
        }

        return (int) $id;
    }

    public function editContact(int $id, array $data): int
    {
        $fields = array_intersect_key($data, [
            'name' => true,
            'phone' => true,
            'email' => true,
            'note' => true,
        ]);

        if ($fields === []) {
            return 0;
        }

        return $this->connection->update('contacts', $fields, ['id' => $id]);
    }

    public function deleteContact(int $id): int
    {
        return $this->connection->delete('contacts', ['id' => $id]);
    }
}
