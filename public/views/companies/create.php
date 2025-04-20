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
        input[type="url"],
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
        
        .checkbox-group {
            margin-top: 10px;
        }
        
        .checkbox-group label {
            display: inline-block;
            font-weight: normal;
            margin-left: 5px;
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
            <h2>Add New Company</h2>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="name">Company Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_info">Contact Information</label>
                    <textarea id="contact_info" name="contact_info"></textarea>
                    <small>Enter contact details, address, phone number, etc.</small>
                </div>
                
                <div class="form-group">
                    <label for="logo_url">Logo URL</label>
                    <input type="url" id="logo_url" name="logo_url">
                    <small>Enter a URL to the company logo image (optional)</small>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="active" name="active" value="1" checked>
                    <label for="active">Active</label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button">Save Company</button>
                    <a href="index.php?route=companies" class="button button-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Thaibooklet System</p>
        </div>
    </footer>
</body>
</html>
