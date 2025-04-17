<?php
// Inkludera config.php för databasinställningar
include_once __DIR__ . '/../../../thaibooklet/config.php';

// Skapa anslutning till databasen
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Kontrollera anslutning
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hämta alla editions från databasen
$sql = "SELECT id, title FROM editions";
$result = $conn->query($sql);

echo "<h1>Editions</h1>";
if ($result->num_rows > 0) {
    echo "<ul>";
    // Visa varje edition som en klickbar länk
    while ($row = $result->fetch_assoc()) {
        // Uppdatera länken för att peka på editiondetail.php
        echo "<li><a href='/thaibooklet/views/editions/editiondetail.php?id=" . $row['id'] . "'>" . htmlspecialchars($row['title']) . "</a></li>";
    }
    echo "</ul>";
} else {
    echo "Inga editions hittades.";
}

// Stäng anslutningen
$conn->close();
?>
