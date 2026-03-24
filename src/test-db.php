<?php
// Get database configuration from environment variables
$host = $_ENV['MYSQL_HOST'] ?? 'db';
$dbname = $_ENV['MYSQL_DATABASE'] ?? 'myapp';
$username = $_ENV['MYSQL_USER'] ?? 'appuser';
$password = $_ENV['MYSQL_PASSWORD'] ?? 'apppassword';
$port = $_ENV['MYSQL_PORT'] ?? '3306';

echo "<h1>PHP MySQL Connection Test</h1>";
echo "<h2>Environment Configuration</h2>";
echo "<p><strong>Host:</strong> $host</p>";
echo "<p><strong>Database:</strong> $dbname</p>";
echo "<p><strong>Username:</strong> $username</p>";
echo "<p><strong>Port:</strong> $port</p>";
echo "<hr>";

try {
    // Create PDO connection using environment variables
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<p style='color: green;'>✅ Successfully connected to MySQL database!</p>";
    
    // Get MySQL version
    $stmt = $pdo->query('SELECT VERSION() as version');
    $version = $stmt->fetch();
    echo "<p><strong>MySQL Version:</strong> " . $version['version'] . "</p>";
    
    // Create a test table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "<p>✅ Test table 'users' created successfully!</p>";
    
    // Check if we need to insert sample data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        // Insert sample data only if table is empty
        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute(['John Doe', 'john@example.com']);
        $stmt->execute(['Jane Smith', 'jane@example.com']);
        $stmt->execute(['Bob Wilson', 'bob@example.com']);
        
        echo "<p>✅ Sample data inserted!</p>";
    } else {
        echo "<p>ℹ️ Table already contains $count records.</p>";
    }
    
    // Fetch and display data
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
    $users = $stmt->fetchAll();
    
    echo "<h2>Users in Database:</h2>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Created At</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show database info
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $db_info = $stmt->fetch();
    echo "<p><strong>Current Database:</strong> " . $db_info['current_db'] . "</p>";
    
    // Show tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "<p><strong>Tables in database:</strong> ";
    foreach ($tables as $table) {
        echo $table[array_keys($table)[0]] . " ";
    }
    echo "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Connection Details:</strong></p>";
    echo "<ul>";
    echo "<li>DSN: mysql:host=$host;port=$port;dbname=$dbname</li>";
    echo "<li>Username: $username</li>";
    echo "</ul>";
    echo "<p><strong>Troubleshooting tips:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure the database container is running: <code>docker-compose ps</code></li>";
    echo "<li>Check if the database is ready: <code>docker-compose logs db</code></li>";
    echo "<li>Verify the environment variables in .env file</li>";
    echo "<li>Check if environment variables are passed to container: <code>docker-compose exec web env | grep MYSQL</code></li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h2>Environment Variables Debug</h2>";
echo "<pre>";
echo "All MYSQL environment variables:\n";
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'MYSQL') === 0) {
        echo "$key = $value\n";
    }
}
echo "</pre>";

echo "<hr>";
echo "<p><a href='index.php'>← Back to Home</a></p>";
?>