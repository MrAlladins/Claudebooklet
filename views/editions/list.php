<?php
// Databasanslutningsinställningar direkt i filen
$db_host = 'localhost';
$db_user = 'u357300497_klickjonas';
$db_pass = 'Jonas366#';
$db_name = 'u357300497_thaibooklet';
// Anslut till databasen
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Anslutningen misslyckades: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
// Hämta aktiva editioner
$sql = "SELECT * FROM editions WHERE published_at IS NOT NULL ORDER BY published_at DESC";
$result = $conn->query($sql);
$editions = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $editions[] = $row;
    }
}
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
        .edition-link {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .edition-link:hover {
            color: #1a5276;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Aktiva Editioner</h1>
        
        <?php if (empty($editions)): ?>
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
                    <?php foreach ($editions as $edition): ?>
                        <tr class="edition-row">
                            <td><?php echo htmlspecialchars($edition['id']); ?></td>
                            <td>
                                <a href="/thaibooklet/views/editions/editiondetail.php?id=<?php echo $edition['id']; ?>" class="edition-link">
                                    <?php echo htmlspecialchars($edition['title']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($edition['description']); ?></td>
                            <td><?php echo htmlspecialchars($edition['published_at']); ?></td>
                            <td><?php echo $edition['valid_until'] ? htmlspecialchars($edition['valid_until']) : 'Obegränsad'; ?></td>
                            <td>
                                <a href="/thaibooklet/views/editions/editiondetail.php?id=<?php echo $edition['id']; ?>" class="btn">Visa detaljer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Alternativ kortvy för editioner -->
            <div class="edition-grid">
                <?php foreach ($editions as $edition): ?>
                    <div class="edition-card">
                        <h3 class="edition-title">
                            <a href="/thaibooklet/views/editions/editiondetail.php?id=<?php echo $edition['id']; ?>" class="edition-link">
                                <?php echo htmlspecialchars($edition['title']); ?>
                            </a>
                        </h3>
                        
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
                        
                        <?php if (isset($edition['image_path']) && $edition['image_path']): ?>
                        <div class="edition-image">
                            <img src="<?php echo htmlspecialchars($edition['image_path']); ?>" alt="<?php echo htmlspecialchars($edition['title']); ?>" />
                        </div>
                        <?php endif; ?>
                        
                        <a href="/thaibooklet/views/editions/editiondetail.php?id=<?php echo $edition['id']; ?>" class="btn">Visa detaljer</a>
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