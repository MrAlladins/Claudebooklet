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
        
        /* Buttons and actions */
        .actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
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
        }

        .button:hover {
            background: #3a5f95;
        }
        
        .button-danger {
            background: #dc3545;
        }

        .button-danger:hover {
            background: #c82333;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

        tr:hover {
            background: #e9e9e9;
        }
        
        .edition-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            color: #fff;
            font-weight: bold;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        
        .status-draft {
            background-color: #6c757d;
        }
        
        .status-published {
            background-color: #28a745;
        }
        
        .status-archived {
            background-color: #dc3545;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        /* Footer */
        footer {
            background: #4a6fa5;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
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
            
            .actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            table {
                display: block;
                overflow-x: auto;
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
            <div class="actions">
                <h2>Editions</h2>
                <a href="index.php?route=editions&action=create" class="button">+ Create New Edition</a>
            </div>
            
            <?php if (empty($editions)): ?>
            <p>No editions found. Create your first edition to get started.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Coupons</th>
                        <th>Created</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($editions as $edition): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($edition['id']); ?></td>
                        <td><?php echo htmlspecialchars($edition['title']); ?></td>
                        <td>
                            <span class="edition-status status-<?php echo $edition['status']; ?>">
                                <?php echo ucfirst(htmlspecialchars($edition['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo (int)$edition['coupon_count']; ?></td>
                        <td><?php echo htmlspecialchars($edition['created_at']); ?></td>
                        <td><?php echo $edition['published_at'] ? htmlspecialchars($edition['published_at']) : 'Not published'; ?></td>
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