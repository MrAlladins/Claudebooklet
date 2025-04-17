<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>Thaibooklet System</title>
    <!-- Här inkluderar du samma stilmallar som du använder för andra sidor -->
    <style>
        /* Basala stilar om du behöver specifika för denna sida */
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
            <h2>Tillgängliga Editioner för Kundvy</h2>
            
            <?php if (empty($editions)): ?>
                <p>Inga aktiva editioner finns tillgängliga.</p>
            <?php else: ?>
                <div class="editions-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Titel</th>
                                <th>Beskrivning</th>
                                <th>Giltig till</th>
                                <th>Åtgärd</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($editions as $edition): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($edition['title']); ?></td>
                                <td><?php echo htmlspecialchars(substr($edition['description'], 0, 100)) . (strlen($edition['description']) > 100 ? '...' : ''); ?></td>
                                <td><?php echo $edition['valid_until'] ? date('Y-m-d', strtotime($edition['valid_until'])) : 'Ingen slutdatum'; ?></td>
                                <td>
                                    <a href="index.php?route=customer&id=<?php echo $edition['id']; ?>" target="_blank" class="button">Visa Kundvy</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Thaibooklet System</p>
        </div>
    </footer>
</body>
</html>