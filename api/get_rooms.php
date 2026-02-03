<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $rooms = get_all_rooms();
    echo json_encode($rooms);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
