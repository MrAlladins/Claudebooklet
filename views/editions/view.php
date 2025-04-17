<?php
/**
 * Edition Detail View
 * 
 * Visar detaljerad information om en edition och dess kopplade kuponger
 */

// Kontrollera att $edition finns
if (!isset($edition) || empty($edition)) {
    echo "<div class='error-message'>Ingen edition hittades.</div>";
    return;
}

// Funktion för att formatera datum
function formatDate($dateString) {
    if (empty($dateString)) return 'N/A';
    
    $date = new DateTime($dateString);
    return $date->format('Y-m-d H:i');
}
?>

<div class="container">
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
    
    <?php if (!isset($coupons) || empty($coupons)): ?>
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
                            <a href="index.php?route=coupons&action=edit&id=<?php echo $coupon['id']; ?>" class="btn">Redigera</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Alternativ kortvy för kuponger -->
        <h3>Alternativ kortvy:</h3>
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
                    
                    <a href="index.php?route=coupons&action=edit&id=<?php echo $coupon['id']; ?>" class="btn">Redigera</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
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
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #f5c6cb;
        margin-bottom: 20px;
    }
    .no-coupons {
        background-color: #e2f0d9;
        color: #3c763d;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #c3e6cb;
        margin-bottom: 20px;
    }
</style>