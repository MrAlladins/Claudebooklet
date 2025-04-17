<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editions - Thaibooklet System</title>
    <link rel="stylesheet" href="path/to/your/styles.css"> <!-- Om du har en extern CSS-fil -->
</head>
<body>
    <header>
        <div class="container">
            <h1>Thaibooklet System</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="index.php?route=editions" class="active">Editions</a></li>
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
            <div class="actions">
                <h2>Editions</h2>
                <a href="index.php?route=editions&action=create" class="button">+ Add New Edition</a>
            </div>
            
            <?php if (empty($editions)): ?>
            <p>No editions found. Add your first edition to get started.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($editions as $edition): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($edition['id']); ?></td>
                        <td><?php echo htmlspecialchars($edition['title']); ?></td>
                        <td>
                            <span class="company-status status-<?php echo $edition['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                <?php echo ucfirst($edition['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($edition['created_at']); ?></td>
                        <td class="action-buttons">
                            <a href="index.php?route=editions&action=edit&id=<?php echo $edition['id']; ?>" class="button">Edit</a>
                            <a href="index.php?route=editions&action=delete&id=<?php echo $edition['id']; ?>" class="button button-danger">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
