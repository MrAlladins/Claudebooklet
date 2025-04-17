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
// Kontrollera om ett editions-ID har skickats
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Felaktigt eller saknat ID. <a href='list.php'>Gå tillbaka till listan</a>");
}
$editionId = (int)$_GET['id'];
// Hämta edition
$sql = "SELECT * FROM editions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $editionId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Ingen edition hittades med ID: $editionId. <a href='list.php'>Gå tillbaka till listan</a>");
}
$edition = $result->fetch_assoc();
// Hämta kopplade kuponger
$sql = "SELECT * FROM coupons WHERE edition_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $editionId);
$stmt->execute();
$result = $stmt->get_result();
$coupons = [];
while ($row = $result->fetch_assoc()) {
    $coupons[] = $row;
}
// Funktion för att formatera datum
function formatDate($dateString) {
    if (empty($dateString)) return 'N/A';
    
    $date = new DateTime($dateString);
    return $date->format('Y-m-d H:i');
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($edition['title']); ?> - Edition</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
            background-color: #f8f9fa;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .edition-header {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .edition-info {
            flex: 1;
            min-width: 300px;
        }
        .edition-image {
            flex: 1;
            min-width: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .edition-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 5px;
        }
        .no-image {
            background-color: #eee;
            width: 100%;
            height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 5px;
        }
        .detail-row {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #7f8c8d;
        }
        .section-title {
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin: 30px 0 20px;
        }
        .coupon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .coupon-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .coupon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .coupon-title {
            font-size: 1.4em;
            margin-top: 0;
            margin-bottom: 10px;
            color: #2980b9;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .coupon-info {
            margin: 10px 0;
        }
        .coupon-info span {
            font-weight: bold;
        }
        .no-coupons {
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
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        /* Menu styling */
        header {
            background: #4a6fa5;
            color: #fff;
            padding: 1rem 0;
            margin-bottom: 30px;
        }
        
        header h1 {
            margin-bottom: 1rem;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-right: 1rem;
        }
        
        nav ul li a {
            color: #fff;
            text-decoration: none;
        }
        
        nav ul li a:hover {
            text-decoration: underline;
        }
        
        footer {
            background: #4a6fa5;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
            margin-top: 50px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
            }
            
            nav ul li {
                margin-right: 0;
                margin-bottom: 0.5rem;
            }
            
            .edition-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Thaibooklet System</h1>
            <nav>
                <ul>
                    <li><a href="/thaibooklet/index.php">Dashboard</a></li>
                    <li><a href="/thaibooklet/index.php?route=editions">Editions</a></li>
                    <li><a href="/thaibooklet/index.php?route=companies">Companies</a></li>
                    <li><a href="/thaibooklet/index.php?route=coupons">Coupons</a></li>
                    <li><a href="/thaibooklet/index.php?route=users">Users</a></li>
                    <li><a href="/thaibooklet/index.php?route=login&action=logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <a href="list.php" class="back-link">&larr; Tillbaka till editioner</a>
        
        <h1><?php echo htmlspecialchars($edition['title']); ?></h1>
        
        <div class="edition-header">
            <div class="edition-info">
                <div class="detail-row">
                    <div class="detail-label">ID:</div>
                    <div><?php echo htmlspecialchars($edition['id']); ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Beskrivning:</div>
                    <div><?php echo htmlspecialchars($edition['description']); ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Skapad:</div>
                    <div><?php echo formatDate($edition['created_at']); ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Publicerad:</div>
                    <div><?php echo formatDate($edition['published_at']); ?></div>
                </div>
                
                <?php if (isset($edition['status'])): ?>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div><?php echo htmlspecialchars($edition['status']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($edition['woocommerce_product_id']) && $edition['woocommerce_product_id']): ?>
                <div class="detail-row">
                    <div class="detail-label">WooCommerce Produkt ID:</div>
                    <div><?php echo htmlspecialchars($edition['woocommerce_product_id']); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="detail-row">
                    <div class="detail-label">Giltig till:</div>
                    <div>
                        <?php 
                        if (isset($edition['valid_until']) && $edition['valid_until']) {
                            echo formatDate($edition['valid_until']);
                        } else {
                            echo 'Obegränsad';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="edition-image">
                <?php if (isset($edition['image_path']) && $edition['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($edition['image_path']); ?>" alt="<?php echo htmlspecialchars($edition['title']); ?>" />
                <?php else: ?>
                    <div class="no-image">Ingen bild tillgänglig</div>
                <?php endif; ?>
            </div>
        </div>
        
        <h2 class="section-title">Kuponger kopplade till denna edition</h2>
        
        <?php if (empty($coupons)): ?>
            <div class="no-coupons">
                Inga kuponger är kopplade till denna edition.
            </div>
        <?php else: ?>
            <!-- Visa kuponger i tabellform -->
            <table id="couponsTable">
                <thead>
                    <tr>
                        <?php 
                        // Dynamiskt skapa kolumnrubriker baserat på första raden
                        $firstCoupon = $coupons[0];
                        foreach ($firstCoupon as $key => $value) {
                            if ($key !== 'edition_id' && $key !== 'id') {
                                echo "<th>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . "</th>";
                            }
                        }
                        echo "<th>Åtgärder</th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr class="coupon-row">
                            <?php foreach ($coupon as $key => $value): ?>
                                <?php if ($key !== 'edition_id' && $key !== 'id'): ?>
                                    <td><?php echo htmlspecialchars($value); ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <td>
                                <a href="/thaibooklet/index.php?route=coupons&action=edit&id=<?php echo $coupon['id']; ?>" class="btn">Redigera</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Alternativ kortvy för kuponger -->
            <h3>Kuponger - kortvy</h3>
            <div class="coupon-grid">
                <?php foreach ($coupons as $coupon): ?>
                    <div class="coupon-card">
                        <?php 
                        // Försök hitta någon form av titel för kupongen
                        $couponTitle = '';
                        if (isset($coupon['title'])) {
                            $couponTitle = $coupon['title'];
                        } elseif (isset($coupon['name'])) {
                            $couponTitle = $coupon['name'];
                        } elseif (isset($coupon['code'])) {
                            $couponTitle = 'Kupong: ' . $coupon['code'];
                        } elseif (isset($coupon['description'])) {
                            $couponTitle = substr($coupon['description'], 0, 30) . '...';
                        } else {
                            $couponTitle = 'Kupong #' . $coupon['id'];
                        }
                        ?>
                        <h3 class="coupon-title"><?php echo htmlspecialchars($couponTitle); ?></h3>
                        
                        <?php foreach ($coupon as $key => $value): ?>
                            <?php if ($key !== 'id' && $key !== 'edition_id' && $key !== 'title' && $key !== 'name' && !is_null($value) && $value !== ''): ?>
                                <div class="coupon-info">
                                    <span><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $key))); ?>:</span>
                                    <?php echo htmlspecialchars($value); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <a href="/thaibooklet/index.php?route=coupons&action=edit&id=<?php echo $coupon['id']; ?>" class="btn">Redigera</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Thaibooklet System</p>
        </div>
    </footer>
</body>
</html>
<?php
// Stäng anslutning
$conn->close();
?>