<?php
// Ladda konfigurationen
require_once 'config.php';

// Skapa databasanslutning
try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Om formuläret har skickats
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        $role = $_POST['role'] ?? 'customer';
        
        if (empty($email) || empty($password) || empty($name)) {
            $error = "Alla fält måste fyllas i.";
        } else {
            // Hasha lösenordet
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Kolla om användaren redan finns
            $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = "Användaren med denna e-post finns redan.";
            } else {
                // Skapa användaren
                $stmt = $db->prepare("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([$email, $hashedPassword, $name, $role]);
                
                if ($result) {
                    $success = "Användare skapad framgångsrikt!";
                } else {
                    $error = "Kunde inte skapa användaren.";
                }
            }
        }
    }
    
    // Hämta alla användare för visning
    $users = $db->query("SELECT id, email, name, role, created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Databasfel: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lägg till användare - Thaibooklet System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background: #f7f7f7;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #4a6fa5;
            margin-bottom: 20px;
        }
        
        form {
            margin-bottom: 30px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        button {
            background: #4a6fa5;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 3px;
            cursor: pointer;
        }
        
        button:hover {
            background: #3a5f95;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 15px;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #4a6fa5;
            color: #fff;
        }
        
        tr:nth-child(even) {
            background: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lägg till användare</h1>
        
        <a href="index.php">Tillbaka till systemet</a>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div>
                <label for="email">E-post</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div>
                <label for="password">Lösenord</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div>
                <label for="name">Namn</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div>
                <label for="role">Roll</label>
                <select id="role" name="role">
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="customer" selected>Customer</option>
                </select>
            </div>
            
            <button type="submit">Skapa användare</button>
        </form>
        
        <h2>Befintliga användare</h2>
        
        <?php if (empty($users)): ?>
            <p>Inga användare hittades.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>E-post</th>
                        <th>Namn</th>
                        <th>Roll</th>
                        <th>Skapad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>