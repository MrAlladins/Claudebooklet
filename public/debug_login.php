<?php
// Visa alla fel
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Starta session
session_start();

// Ladda konfigurationen
require_once 'config.php';

// Funktion för att testa lösenordsverifiering
function testPasswordVerify($plainPassword, $hashedPassword) {
    echo "Testar password_verify() med:<br>";
    echo "Lösenord: " . htmlspecialchars($plainPassword) . "<br>";
    echo "Hashat lösenord: " . htmlspecialchars($hashedPassword) . "<br>";
    $result = password_verify($plainPassword, $hashedPassword);
    echo "Resultat: " . ($result ? 'TRUE' : 'FALSE') . "<br><br>";
    return $result;
}

// Skapa databasanslutning
try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p>Databasanslutning lyckades!</p>";
    
    // Hämta användare
    $stmt = $db->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Befintliga användare:</h2>";
    if (empty($users)) {
        echo "<p>Inga användare hittades i databasen.</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Email</th><th>Password Hash</th><th>Name</th><th>Role</th><th>Test Login</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['password']) . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>";
            if ($user['email'] === 'admin@example.com') {
                $testResult = testPasswordVerify('admin123', $user['password']);
                echo "admin123: " . ($testResult ? 'OK' : 'FAIL');
            }
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Skapa en ny admin-användare
    echo "<h2>Skapa ny admin-användare:</h2>";
    $testPassword = 'testadmin123';
    $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
    
    // Ta bort eventuell befintlig test-användare
    $db->query("DELETE FROM users WHERE email = 'testadmin@example.com'");
    
    // Skapa en ny test-användare
    $stmt = $db->prepare("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute(['testadmin@example.com', $hashedPassword, 'Test Admin', 'admin']);
    
    if ($result) {
        echo "<p style='color:green'>Ny testanvändare skapad:</p>";
        echo "<ul>";
        echo "<li>Email: testadmin@example.com</li>";
        echo "<li>Lösenord: testadmin123</li>";
        echo "<li>Hashat lösenord: " . $hashedPassword . "</li>";
        echo "</ul>";
        
        // Verifiera lösenordet
        $verified = password_verify($testPassword, $hashedPassword);
        echo "<p>Verifiering av nyskapat lösenord: " . ($verified ? 'LYCKADES' : 'MISSLYCKADES') . "</p>";
        
        // Testa en manuell inloggning
        $stmt = $db->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->execute(['testadmin@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify('testadmin123', $user['password'])) {
            echo "<p style='color:green'>Manuell inloggningstest LYCKADES!</p>";
        } else {
            echo "<p style='color:red'>Manuell inloggningstest MISSLYCKADES!</p>";
            if (!$user) {
                echo "<p>Användaren hittades inte.</p>";
            } else {
                echo "<p>Lösenordsverifiering misslyckades.</p>";
                echo "<p>Lösenord i databas: " . $user['password'] . "</p>";
                echo "<p>Resultat av verifiering: " . (password_verify('testadmin123', $user['password']) ? 'TRUE' : 'FALSE') . "</p>";
            }
        }
    } else {
        echo "<p style='color:red'>Kunde inte skapa testanvändare.</p>";
    }
    
    // Visa PHP-information om password_hash och password_verify
    echo "<h2>PHP Information:</h2>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
    echo "<p>Password Hashing algoritm: " . PASSWORD_DEFAULT . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Databasfel: " . $e->getMessage() . "</p>";
}
?>

<h2>Testa inloggning med formulär:</h2>
<form method="post" action="debug_login.php?action=test">
    <div>
        <label for="email">E-post:</label>
        <input type="email" id="email" name="email" required value="testadmin@example.com">
    </div>
    <div>
        <label for="password">Lösenord:</label>
        <input type="password" id="password" name="password" required value="testadmin123">
    </div>
    <button type="submit">Testa inloggning</button>
</form>

<?php
// Hantera testinloggning
if (isset($_GET['action']) && $_GET['action'] === 'test') {
    echo "<h3>Testresultat:</h3>";
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p>Email: " . htmlspecialchars($email) . "</p>";
    
    try {
        // Hämta användare
        $stmt = $db->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo "<p style='color:red'>Användaren hittades inte.</p>";
        } else {
            echo "<p>Användare hittad med ID: " . $user['id'] . "</p>";
            echo "<p>Lösenord i databas: " . $user['password'] . "</p>";
            
            $verified = password_verify($password, $user['password']);
            echo "<p>Lösenordsverifiering: " . ($verified ? 'LYCKADES' : 'MISSLYCKADES') . "</p>";
            
            if ($verified) {
                echo "<p style='color:green'>INLOGGNING LYCKADES!</p>";
                
                // Skapa session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                
                echo "<p>Session skapad med user_id: " . $_SESSION['user_id'] . "</p>";
                echo "<p>Du kan nu försöka gå till <a href='index.php'>huvudsidan</a> för att se om du är inloggad.</p>";
            } else {
                echo "<p style='color:red'>INLOGGNING MISSLYCKADES!</p>";
            }
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>Databasfel vid testinloggning: " . $e->getMessage() . "</p>";
    }
}
?>