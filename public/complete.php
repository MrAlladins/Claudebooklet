<?php
/**
 * Complete.php
 * 
 * Listar upp aktiva editioner från databasen
 */

// Aktivera felrapportering
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Försök inkludera config.php för databasanslutning
try {
    require_once 'config.php';
} catch (Exception $e) {
    die("Kunde inte inkludera config.php: " . $e->getMessage());
}

// Kontrollera att nödvändiga variabler finns
if (!isset($host) || !isset($username) || !isset($password) || !isset($dbname)) {
    die("Konfigurationsfel: Nödvändiga databasvariabler saknas i config.php");
}

// Skapa anslutning
try {
    $conn = new mysqli($host, $username, $password, $dbname);
} catch (Exception $e) {
    die("Databasanslutningsfel: " . $e->getMessage());

// Kontrollera anslutning
if ($conn->connect_error) {
    die("Anslutningen misslyckades: " . $conn->connect_error);
}

// Logga databasen vi anslutit till
error_log("Ansluten till databas: $dbname");

// Sätt UTF-8 för anslutningen
$conn->set_charset("utf8mb4");

/**
 * Funktion för att hämta aktiva editioner
 * Anpassad specifikt för editions-tabellen
 */
function getActiveEditions($conn) {
    // Använd den specifika tabellen "editions"
    // En edition är aktiv om:
    // 1. published_at är satt (inte NULL) 
    // 2. valid_until är antingen NULL eller ett datum i framtiden
    $currentDate = date('Y-m-d');
    
    $sql = "SELECT * FROM editions 
            WHERE published_at IS NOT NULL 
            AND (valid_until IS NULL OR valid_until >= '$currentDate') 
            ORDER BY published_at DESC";
    
    // Logga SQL-frågan för felsökning
    error_log("SQL-fråga: $sql");
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        return ["error" => "Databasfrågan misslyckades: " . $conn->error . " (SQL: $sql)"];
    }
    
    $editions = [];
    while ($row = $result->fetch_assoc()) {
        $editions[] = $row;
    }
    
    return $editions;
}

// Hämta aktiva editioner
$activeEditions = getActiveEditions($conn);

// Kontrollera om det finns ett felmeddelande
$hasError = isset($activeEditions['error']);

?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktiva Editioner</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
            background-color: #f8f9fa;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .edition-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .edition-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .edition-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .edition-title {
            font-size: 1.4em;
            margin-top: 0;
            color: #2980b9;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .edition-info {
            margin: 10px 0;
        }
        .edition-info span {
            font-weight: bold;
        }
        .edition-image {
            margin: 15px 0;
            text-align: center;
        }
        .edition-image img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 4px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
            margin-bottom: 20px;
        }
        .no-editions {
            background-color: #e2f0d9;
            color: #3c763d;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: #fff;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .filter-container {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .filter-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            max-width: 300px;
        }
        .view-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .view-btn {
            padding: 8px 15px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .view-btn.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Aktiva Editioner</h1>
        
        <?php if ($hasError): ?>
            <div class="error-message">
                <?php echo $activeEditions['error']; ?>
            </div>
        <?php elseif (empty($activeEditions)): ?>
            <div class="no-editions">
                Inga aktiva editioner hittades.
            </div>
        <?php else: ?>
            <div class="filter-container">
                <label for="editionFilter">Filtrera editioner:</label>
                <input type="text" id="editionFilter" class="filter-input" placeholder="Skriv för att filtrera...">
            </div>
            
            <div class="view-controls">
                <button id="tableViewBtn" class="view-btn active">Tabellvy</button>
                <button id="cardViewBtn" class="view-btn">Kortvy</button>
            </div>
            
            <!-- Tabellvy för editioner -->
            <table id="editionsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titel</th>
                        <th>Beskrivning</th>
                        <th>Publicerad</th>
                        <th>Giltig till</th>
                        <th>Åtgärder</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeEditions as $edition): ?>
                        <tr class="edition-row">
                            <td><?php echo htmlspecialchars($edition['id']); ?></td>
                            <td><?php echo htmlspecialchars($edition['title']); ?></td>
                            <td><?php echo htmlspecialchars($edition['description']); ?></td>
                            <td><?php echo htmlspecialchars($edition['published_at']); ?></td>
                            <td><?php echo $edition['valid_until'] ? htmlspecialchars($edition['valid_until']) : 'Obegränsad'; ?></td>
                            <td>
                                <a href="view_edition.php?id=<?php echo $edition['id']; ?>" class="btn">Visa detaljer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Alternativ kortvy för editioner -->
            <div class="edition-grid">
                <?php foreach ($activeEditions as $edition): ?>
                    <div class="edition-card">
                        <h3 class="edition-title"><?php echo htmlspecialchars($edition['title']); ?></h3>
                        
                        <div class="edition-info">
                            <span>Beskrivning:</span>
                            <?php echo htmlspecialchars($edition['description']); ?>
                        </div>
                        
                        <div class="edition-info">
                            <span>Publicerad:</span>
                            <?php echo htmlspecialchars($edition['published_at']); ?>
                        </div>
                        
                        <?php if ($edition['valid_until']): ?>
                        <div class="edition-info">
                            <span>Giltig till:</span>
                            <?php echo htmlspecialchars($edition['valid_until']); ?>
                        </div>
                        <?php else: ?>
                        <div class="edition-info">
                            <span>Giltig till:</span> Obegränsad
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($edition['image_path']): ?>
                        <div class="edition-image">
                            <img src="<?php echo htmlspecialchars($edition['image_path']); ?>" alt="<?php echo htmlspecialchars($edition['title']); ?>" />
                        </div>
                        <?php endif; ?>
                        
                        <a href="view_edition.php?id=<?php echo $edition['id']; ?>" class="btn">Visa detaljer</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Funktion för att filtrera editionerna
        document.getElementById('editionFilter').addEventListener('input', function() {
            const filterValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('.edition-row');
            const cards = document.querySelectorAll('.edition-card');
            
            // Filtrera tabellrader
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filterValue) ? '' : 'none';
            });
            
            // Filtrera kort
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(filterValue) ? '' : 'none';
            });
        });
        
        // Växla mellan tabellvy och kortvy
        const tableViewBtn = document.getElementById('tableViewBtn');
        const cardViewBtn = document.getElementById('cardViewBtn');
        const editionsTable = document.getElementById('editionsTable');
        const editionGrid = document.querySelector('.edition-grid');
        
        // Standardinställning: visa tabell, dölj kort
        editionGrid.style.display = 'none';
        
        tableViewBtn.addEventListener('click', function() {
            editionsTable.style.display = 'table';
            editionGrid.style.display = 'none';
            tableViewBtn.classList.add('active');
            cardViewBtn.classList.remove('active');
        });
        
        cardViewBtn.addEventListener('click', function() {
            editionsTable.style.display = 'none';
            editionGrid.style.display = 'grid';
            cardViewBtn.classList.add('active');
            tableViewBtn.classList.remove('active');
        });
    </script>
</body>
</html>

<?php
// Stäng anslutning
$conn->close();
?>