<?php
// preview.php - Placeras i din views/editions-mapp
// Antag att $edition och $coupons har hämtats från databasen baserat på ID

$edition_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Exempel på hur du kan hämta editionsdata
$stmt = $db->prepare("SELECT * FROM editions WHERE id = ?");
$stmt->execute([$edition_id]);
$edition = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$edition) {
    header("Location: index.php?route=editions");
    exit;
}

// Hämta alla kuponger för denna edition
$stmt = $db->prepare("
    SELECT c.*, co.name as company_name, cat.name as category_name 
    FROM coupons c
    LEFT JOIN companies co ON c.company_id = co.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE c.edition_id = ? AND c.status = 'active'
    ORDER BY c.title
");
$stmt->execute([$edition_id]);
$coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Eventuellt gruppera kuponger efter kategori
$coupons_by_category = [];
foreach ($coupons as $coupon) {
    $category = !empty($coupon['category_name']) ? $coupon['category_name'] : 'Utan kategori';
    $coupons_by_category[$category][] = $coupon;
}

$title = "Förhandsgranskning: " . htmlspecialchars($edition['title']);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background: #f7f7f7;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
        }
        
        /* Header */
        header {
            background: #4a6fa5;
            color: #fff;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        
        header h1 {
            margin-bottom: 0.5rem;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .back-link {
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .back-link:before {
            content: "←";
            margin-right: 5px;
        }
        
        /* Edition info */
        .edition-info {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .edition-info h2 {
            color: #4a6fa5;
            margin-bottom: 1rem;
        }
        
        .edition-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .edition-detail {
            margin-bottom: 1rem;
        }
        
        .edition-detail h3 {
            font-size: 1rem;
            margin-bottom: 0.3rem;
            color: #666;
        }
        
        /* Category sections */
        .category-section {
            margin-bottom: 2rem;
        }
        
        .category-section h2 {
            color: #4a6fa5;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #4a6fa5;
        }
        
        /* Coupon cards */
        .coupon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .coupon-card {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .coupon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .coupon-image {
            height: 180px;
            overflow: hidden;
            position: relative;
        }
        
        .coupon-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .coupon-card:hover .coupon-image img {
            transform: scale(1.05);
        }
        
        .coupon-content {
            padding: 15px;
        }
        
        .coupon-company {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .coupon-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .coupon-value {
            font-size: 1.1rem;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        
        .coupon-description {
            margin-bottom: 15px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .coupon-validity {
            font-size: 0.8rem;
            color: #888;
            margin-top: 10px;
        }
        
        /* Modal for enlarged image */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            margin: auto;
            display: block;
            max-width: 80%;
            max-height: 80vh;
            margin-top: 5vh;
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .edition-details {
                grid-template-columns: 1fr;
            }
            
            .coupon-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                max-width: 95%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php?route=editions" class="back-link">Tillbaka till Editioner</a>
                <h1>Förhandsgranskning av Edition</h1>
            </nav>
        </div>
    </header>
    
    <main>
        <div class="container">
            <div class="edition-info">
                <h2><?php echo htmlspecialchars($edition['title']); ?></h2>
                
                <div class="edition-details">
                    <div class="edition-detail">
                        <h3>Beskrivning:</h3>
                        <p><?php echo nl2br(htmlspecialchars($edition['description'] ?? '')); ?></p>
                    </div>
                    
                    <div class="edition-detail">
                        <h3>Publicerad:</h3>
                        <p><?php echo isset($edition['publish_date']) ? date('d F Y', strtotime($edition['publish_date'])) : '-'; ?></p>
                    </div>
                    
                    <div class="edition-detail">
                        <h3>Giltig till:</h3>
                        <p><?php echo isset($edition['valid_until']) ? date('d F Y', strtotime($edition['valid_until'])) : 'Tills vidare'; ?></p>
                    </div>
                </div>
            </div>
            
            <?php if (empty($coupons)): ?>
            <div class="no-coupons">
                <p>Inga kuponger finns tillgängliga för denna edition.</p>
            </div>
            <?php else: ?>
                
                <?php if (!empty($coupons_by_category)): ?>
                    <?php foreach ($coupons_by_category as $category => $category_coupons): ?>
                    <div class="category-section">
                        <h2><?php echo htmlspecialchars($category); ?></h2>
                        
                        <div class="coupon-grid">
                            <?php foreach ($category_coupons as $coupon): ?>
                            <div class="coupon-card">
                                <?php if (!empty($coupon['image_path'])): ?>
                                <div class="coupon-image">
                                    <img src="<?php echo htmlspecialchars($coupon['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($coupon['title']); ?>"
                                         onclick="openImageModal('<?php echo htmlspecialchars(addslashes($coupon['image_path'])); ?>', '<?php echo htmlspecialchars(addslashes($coupon['title'])); ?>')">
                                </div>
                                <?php endif; ?>
                                
                                <div class="coupon-content">
                                    <div class="coupon-company"><?php echo htmlspecialchars($coupon['company_name']); ?></div>
                                    <h3 class="coupon-title"><?php echo htmlspecialchars($coupon['title']); ?></h3>
                                    <div class="coupon-value"><?php echo htmlspecialchars($coupon['value']); ?></div>
                                    
                                    <?php if (!empty($coupon['description'])): ?>
                                    <div class="coupon-description"><?php echo nl2br(htmlspecialchars(substr($coupon['description'], 0, 100))); ?><?php echo strlen($coupon['description']) > 100 ? '...' : ''; ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="coupon-validity">
                                        Giltig: 
                                        <?php if (!empty($coupon['valid_from']) && !empty($coupon['valid_until'])): ?>
                                            <?php echo date('d/m/Y', strtotime($coupon['valid_from'])); ?> - <?php echo date('d/m/Y', strtotime($coupon['valid_until'])); ?>
                                        <?php elseif (!empty($coupon['valid_until'])): ?>
                                            Till <?php echo date('d/m/Y', strtotime($coupon['valid_until'])); ?>
                                        <?php else: ?>
                                            Tills vidare
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
            <?php endif; ?>
            
            <!-- Modal för att visa förstorad bild -->
            <div id="imageModal" class="modal">
                <span class="close" onclick="closeImageModal()">&times;</span>
                <img class="modal-content" id="modalImage">
            </div>
        </div>
    </main>
    
    <script>
    // Funktioner för att hantera bildmodalen
    function openImageModal(imageSrc, imageAlt) {
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("modalImage");
        
        modal.style.display = "block";
        modalImg.src = imageSrc;
        modalImg.alt = imageAlt;
    }
    
    function closeImageModal() {
        var modal = document.getElementById("imageModal");
        modal.style.display = "none";
    }
    
    // Stäng modalen om användaren klickar utanför bilden
    window.onclick = function(event) {
        var modal = document.getElementById("imageModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
</body>
</html>