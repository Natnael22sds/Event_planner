<?php
// events_feed.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require 'config.php';

$start = isset($_GET['start']) ? $_GET['start'] : null;
$end = isset($_GET['end']) ? $_GET['end'] : null;
$category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;

// Basic query with optional filters (prepared)
$sql = "SELECT e.id, e.title, e.description, e.start_datetime AS start, e.end_datetime AS end, e.location, e.category, u.name AS organizer
        FROM events e
        JOIN eusers u ON e.organizer_id = u.id
        WHERE 1=1 ";

$params = [];
if ($start && $end) {
    $sql .= " AND (e.start_datetime BETWEEN ? AND ? OR e.end_datetime BETWEEN ? AND ?)";
    $params[] = $start; $params[] = $end; $params[] = $start; $params[] = $end;
}
if ($category) {
    $sql .= " AND e.category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY e.start_datetime ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map DB rows to FullCalendar event objects
$fc = array_map(function($e) {
    return [
        'id' => $e['id'],
        'title' => $e['title'],
        'start' => $e['start'],
        'end' => $e['end'],
        'extendedProps' => [
            'description' => $e['description'],
            'location' => $e['location'],
            'category' => $e['category'],
            'organizer' => $e['organizer'],
        ],
    ];
}, $events);

echo json_encode($fc);
