<?php
declare(strict_types=1);
require_once __DIR__.'/Database.php';
final class RegistrationRepository {
    public function __construct(private mysqli $connection) {}
    public function save(int $eventId, string $name, string $email): string {
        $email = strtolower(trim($email));
        $name = trim($name);
        if ($eventId <= 0 || $name === '' || strlen($name)>120
            || str_contains($name, "\0") || strlen($email)>200
            || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('REGISTRATION_INVALID');
        }
        $statement = $this->connection->prepare(
            'INSERT INTO registrations (event_id,name,email) VALUES (?,?,?)'
        );
        try {
            $statement->execute([$eventId, $name, $email]);
            return 'CREATED';
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                return 'DUPLICATE';
            }
            throw $e;
        }
    }
    public function listForEvent(int $eventId): array {
        $statement = $this->connection->prepare(
            'SELECT id,event_id,name,email FROM registrations WHERE event_id=? ORDER BY id'
        );
        $statement->execute([$eventId]);
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }
}
