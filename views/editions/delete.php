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
        
        /* Delete confirmation */
        .delete-confirmation {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .delete-warning {
            color: #dc3545;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        
        .item-details {
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 3px;
        }
        
        .item-details p {
            margin-bottom: 0.5rem;
        }
        
        /* Buttons */
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 2rem;
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
        
        .button-danger {
            background: #dc3545;
        }

        .button-danger:hover {
            background: #c82333;
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
            
            .actions {
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
            <h2>Delete Edition</h2>
            
            <div class="delete-confirmation">
                <div class="delete-warning">
                    <p>Are you sure you want to delete this edition?</p>
                </div>
                
                <div class="item-details">
                    <p><strong>ID:</strong> <?php echo htmlspecialchars($edition['id']); ?></p>
                    <p><strong>Title:</strong> <?php echo htmlspecialchars($edition['title']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($edition['status']); ?></p>
                </div>
                
                <p><strong>Warning:</strong> This action cannot be undone. All coupons associated with this edition will also be deleted.</p>
                
                <div class="actions">
                    <form method="post">
                        <button type="submit" class="button button-danger">Yes, Delete Edition</button>
                    </form>
                    <a href="index.php?route=editions" class="button">Cancel</a>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Thaibooklet System</p>
        </div>
    </footer>
</body>
</html>