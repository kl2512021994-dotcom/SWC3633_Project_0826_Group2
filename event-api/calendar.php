<?php
require_once 'db.php';

$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

if (!$eventId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Event ID is required"]);
    exit();
}

$stmt = $pdo->prepare("SELECT e.*, v.venue_name, v.address 
                       FROM events e 
                       JOIN venues v ON e.venue_id = v.venue_id 
                       WHERE e.event_id = ?");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Event not found"]);
    exit();
}

// Format dates for Google Calendar API / iCal format
$startTime = strtotime($event['event_date']);
$endTime = $startTime + (2 * 3600); // Default duration 2 hours

$startISO = date("Ymd\THis\Z", gmdate($startTime));
$endISO = date("Ymd\THis\Z", gmdate($endTime));

$title = urlencode($event['title']);
$description = urlencode($event['description']);
$location = urlencode($event['venue_name'] . ", " . $event['address']);

// Third-Party Google Calendar Dynamic Export Link
$googleCalendarUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE" .
                     "&text={$title}" .
                     "&dates={$startISO}/{$endISO}" .
                     "&details={$description}" .
                     "&location={$location}";

// Raw iCal (.ics) formatted content output
$icsContent = "BEGIN:VCALENDAR\r\n" .
              "VERSION:2.0\r\n" .
              "PRODID:-//Event Ticketing System//EN\r\n" .
              "BEGIN:VEVENT\r\n" .
              "SUMMARY:" . $event['title'] . "\r\n" .
              "DESCRIPTION:" . $event['description'] . "\r\n" .
              "LOCATION:" . $event['venue_name'] . ", " . $event['address'] . "\r\n" .
              "DTSTART:" . date("Ymd\THis\Z", $startTime) . "\r\n" .
              "DTEND:" . date("Ymd\THis\Z", $endTime) . "\r\n" .
              "END:VEVENT\r\n" .
              "END:VCALENDAR";

echo json_encode([
    "status" => "success",
    "event_id" => $eventId,
    "event_title" => $event['title'],
    "calendar_integration" => [
        "google_calendar_link" => $googleCalendarUrl,
        "ics_format" => $icsContent
    ]
]);
?>
