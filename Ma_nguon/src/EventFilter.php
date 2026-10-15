<?php
declare(strict_types=1);

function filterEvents(array $events, string $query, string $category = '', bool $availableOnly = false): array
{
    $result = [];
    foreach ($events as $event) {
        $matchQuery = $query === '' || stripos($event['title'], $query) !== false;
        $matchCategory = $category === '' || $event['category'] === $category;

        $availableSeats = max(0, $event['capacity'] - $event['registered']);
        $matchAvailable = !$availableOnly || $availableSeats > 0;

        if ($matchQuery && $matchCategory && $matchAvailable) {
            $result[] = $event;
        }
    }
    return $result;
}
