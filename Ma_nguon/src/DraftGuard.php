<?php
declare(strict_types=1);

function canComplete(array $session, int $eventId, int $now, int $ttl): bool {
    if (!isset($session['draft']) || !is_array($session['draft'])) {
        return false;
    }
    $draft = $session['draft'];
    if (!isset($draft['data'], $draft['created_at']) || !is_array($draft['data'])) {
        return false;
    }
    if (!isset($draft['data']['event_id']) || (int)$draft['data']['event_id'] !== $eventId) {
        return false;
    }
    $createdAt = (int)$draft['created_at'];
    if ($createdAt > $now) {
        return false;
    }
    if ($ttl <= 0) {
        return false;
    }
    if ($now - $createdAt >= $ttl) {
        return false;
    }
    return true;
}
