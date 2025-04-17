<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>Thaibooklet System</title>
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
            padding: 0 15px;
        }
        
        /* Header */
        header {
            background: #4a6fa5;
            color: #fff;
            padding: 1rem 0;
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
        
        /* Main content */
        main {
            padding: 2rem 0;
        }
        
        h2 {
            margin-bottom: 1.5rem;
            color: #4a6fa5;
        }
        
        /* Forms */
        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 16px;
        }
        
        textarea {
            height: 150px;
            resize: vertical;
        }
        
        /* Buttons */
        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        
        .button {
            background: #4a6fa5;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }
        .button:hover {
            background: #3a5f95;
        }
        
        .button-cancel {
            background: #6c757d;
        }
        .button-cancel:hover {
            background: #5a6268;
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Image styles */
        .current-image {
            margin-bottom: 15px;
        }
        
        .current-image img {
            display: block;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 3px;
            max-width: 200px;
            margin-bottom: 10px;
        }
        
        .file-input-wrapper {
            margin-top: 10px;
        }
        
        /* Footer */
        footer {
            background: #4a6fa5;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
            margin-top: 2rem;
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
            
            .form-actions {
                flex-direction: column;
            }
            
            .button {
                width: 100%;
                text-align: center;
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
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="index.php?route=editions">Editions</a></li>
                    <li><a href="index.php?route=companies">Companies</a></li>
                    <li><a href="index.php?route=coupons">Coupons</a></li>
                    <li><a href="index.php?route=users">Users</a></li>
                    <li><a href="index.php?route=login&action=logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <main>
        <div class="container">
            <h2><?php echo isset($edition) ? 'Edit Edition' : 'Add New Edition'; ?></h2>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Edition Title</label>
                    <input type="text" id="title" name="title" value="<?php echo isset($edition) ? htmlspecialchars($edition['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo isset($edition) ? htmlspecialchars($edition['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edition_image">Edition Image</label>
                    <?php if (isset($edition) && !empty($edition['image_path'])): ?>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($edition['image_path']); ?>" alt="Current Image">
                        <div>
                            <label>
                                <input type="checkbox" name="remove_image" value="1"> Remove current image
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="file-input-wrapper">
                        <input type="file" id="edition_image" name="edition_image" accept="image/jpeg,image/png,image/gif">
                        <small>Accepted formats: JPG, PNG, GIF. Max size: 2MB</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="publish_date">Publish Date</label>
                    <input type="date" id="publish_date" name="publish_date" value="<?php echo isset($edition['publish_date']) ? htmlspecialchars($edition['publish_date']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="valid_until">Valid Until</label>
                    <input type="date" id="valid_until" name="valid_until" value="<?php echo isset($edition['valid_until']) ? htmlspecialchars($edition['valid_until']) : ''; ?>">
                    <small>Leave empty for no expiration date</small>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="active" <?php echo (isset($edition) && $edition['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (isset($edition) && $edition['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="archived" <?php echo (isset($edition) && $edition['status'] == 'archived') ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button">Save Edition</button>
                    <a href="index.php?route=editions" class="button button-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Thaibooklet System</p>
        </div>
    </footer>

    <?php
    // PHP-kod för att hantera bilduppladdning och databaslagring
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Hantera bilduppladdning
        $image_path = isset($edition) ? $edition['image_path'] : null;
        
        if (isset($_FILES['edition_image']) && $_FILES['edition_image']['error'] == 0) {
            // Definiera tillåtna filtyper
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            // Max filstorlek (2MB)
            $max_size = 2 * 1024 * 1024;
            
            // Hämta filinformation
            $file_name = $_FILES['edition_image']['name'];
            $file_size = $_FILES['edition_image']['size'];
            $file_tmp = $_FILES['edition_image']['tmp_name'];
            $file_type = $_FILES['edition_image']['type'];
            
            // Skapa ett unikt filnamn för att undvika överskrivning
            $unique_file_name = uniqid() . '_' . $file_name;
            
            // Sätt målkatalog för uppladdade bilder
            $upload_dir = 'uploads/editions/';
            
            // Skapa katalogen om den inte finns
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $unique_file_name;
            
            // Validera filtyp
            if (!in_array($file_type, $allowed_types)) {
                $error = "Endast JPG, PNG och GIF-bilder är tillåtna.";
            }
            // Validera filstorlek
            elseif ($file_size > $max_size) {
                $error = "Filstorleken får inte överstiga 2MB.";
            }
            // Försök ladda upp filen
            elseif (move_uploaded_file($file_tmp, $upload_path)) {
                // Ta bort gammal bild om det finns en
                if (!empty($image_path) && file_exists($image_path)) {
                    unlink($image_path);
                }
                $image_path = $upload_path;
            } else {
                $error = "Det uppstod ett fel vid uppladdning av bilden.";
            }
        }
        
        // Hantera borttagning av bild
        if (isset($_POST['remove_image']) && $_POST['remove_image'] == 1) {
            // Ta bort filen om den existerar
            if (!empty($image_path) && file_exists($image_path)) {
                unlink($image_path);
            }
            $image_path = null;
        }
        
        // Om inga fel uppstod, fortsätt med att spara i databasen
        if (!isset($error)) {
            try {
                // Hämta värden från formuläret
                $title = $_POST['title'];
                $description = $_POST['description'];
                $publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : null;
                $valid_until = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
                $status = $_POST['status'];
                
                // SQL för att spara editionen med image_path
                if (isset($edition)) {
                    // Uppdatera befintlig edition
                    $sql = "UPDATE editions SET 
                        title = :title,
                        description = :description,
                        publish_date = :publish_date,
                        valid_until = :valid_until,
                        status = :status";
                    
                    // Lägg till image_path endast om den ändrats
                    if ($image_path !== null || isset($_POST['remove_image'])) {
                        $sql .= ", image_path = :image_path";
                    }
                    
                    $sql .= " WHERE id = :id";
                    
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id', $edition['id']);
                } else {
                    // Skapa ny edition
                    $sql = "INSERT INTO editions (
                        title, description, publish_date, valid_until, 
                        status, image_path
                    ) VALUES (
                        :title, :description, :publish_date, :valid_until,
                        :status, :image_path
                    )";
                    
                    $stmt = $db->prepare($sql);
                }
                
                // Bind parametrar
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':publish_date', $publish_date);
                $stmt->bindParam(':valid_until', $valid_until);
                $stmt->bindParam(':status', $status);
                
                // Bind image_path om den anges i SQL-frågan
                if (!isset($edition) || $image_path !== null || isset($_POST['remove_image'])) {
                    $stmt->bindParam(':image_path', $image_path);
                }
                
                // Utför SQL-frågan
                $stmt->execute();
                
                // Omdirigera efter framgång
                header('Location: index.php?route=editions&success=1');
                exit;
                
            } catch (PDOException $e) {
                $error = "Databasfel: " . $e->getMessage();
            }
        }
    }
    ?>
</body>
</html>