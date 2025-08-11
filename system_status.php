<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upay Ticket - System Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.20/dist/full.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <i class="fas fa-bus text-blue-600"></i> Upay Ticket System Status
            </h1>
            <p class="text-gray-600">Complete system diagnostics and health check</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Database Status -->
            <div class="card bg-white shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-xl">
                        <i class="fas fa-database text-green-600"></i> Database Status
                    </h2>
                    <div class="divider"></div>
                    
                    <?php
                    require_once 'config/database.php';
                    
                    $dbStatus = [];
                    
                    try {
                        include_once 'config/database.php';
                        $database = new Database();
                        $db = $database->getConnection();
                        $dbStatus['connection'] = true;
                        
                        // Check required tables
                        $requiredTables = ['users', 'routes', 'buses', 'bus_schedules', 'seats', 'bookings', 'booking_seats', 'coupons', 'admin_users'];
                        $dbStatus['tables'] = [];
                        
                        foreach ($requiredTables as $table) {
                            try {
                                $stmt = $db->query("SELECT COUNT(*) FROM $table");
                                $count = $stmt->fetchColumn();
                                $dbStatus['tables'][$table] = ['exists' => true, 'count' => $count];
                            } catch (Exception $e) {
                                $dbStatus['tables'][$table] = ['exists' => false, 'error' => $e->getMessage()];
                            }
                        }
                        
                        // Check admin users
                        try {
                            $stmt = $db->query("SELECT username, email, role, status FROM admin_users");
                            $dbStatus['admin_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            $dbStatus['admin_users'] = [];
                        }
                        
                    } catch (Exception $e) {
                        $dbStatus['connection'] = false;
                        $dbStatus['error'] = $e->getMessage();
                    }
                    
                    if ($dbStatus['connection']) {
                        echo '<div class="alert alert-success mb-4">';
                        echo '<i class="fas fa-check-circle"></i> Database connection successful';
                        echo '</div>';
                        
                        echo '<div class="overflow-x-auto">';
                        echo '<table class="table table-zebra">';
                        echo '<thead><tr><th>Table</th><th>Status</th><th>Records</th></tr></thead>';
                        echo '<tbody>';
                        
                        foreach ($dbStatus['tables'] as $tableName => $tableInfo) {
                            echo '<tr>';
                            echo '<td>' . $tableName . '</td>';
                            if ($tableInfo['exists']) {
                                echo '<td><span class="badge badge-success">Exists</span></td>';
                                echo '<td>' . $tableInfo['count'] . '</td>';
                            } else {
                                echo '<td><span class="badge badge-error">Missing</span></td>';
                                echo '<td>-</td>';
                            }
                            echo '</tr>';
                        }
                        
                        echo '</tbody></table>';
                        echo '</div>';
                        
                        if (!empty($dbStatus['admin_users'])) {
                            echo '<div class="mt-4">';
                            echo '<h3 class="font-bold mb-2">Admin Users:</h3>';
                            foreach ($dbStatus['admin_users'] as $admin) {
                                $statusBadge = $admin['status'] === 'active' ? 'badge-success' : 'badge-warning';
                                echo '<div class="badge ' . $statusBadge . ' mr-2 mb-1">';
                                echo $admin['username'] . ' (' . $admin['role'] . ')';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="alert alert-error">';
                        echo '<i class="fas fa-times-circle"></i> Database connection failed: ' . $dbStatus['error'];
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- API Status -->
            <div class="card bg-white shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-xl">
                        <i class="fas fa-plug text-blue-600"></i> API Status
                    </h2>
                    <div class="divider"></div>
                    
                    <div id="api-status">
                        <div class="loading loading-spinner loading-md"></div>
                        Testing API endpoints...
                    </div>
                </div>
            </div>

            <!-- File Structure -->
            <div class="card bg-white shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-xl">
                        <i class="fas fa-folder text-yellow-600"></i> File Structure
                    </h2>
                    <div class="divider"></div>
                    
                    <?php
                    $requiredFiles = [
                        'index.html' => 'Main booking page',
                        'admin/index.html' => 'Admin dashboard',
                        'admin/login.html' => 'Admin login',
                        'scripts/index.js' => 'Main JavaScript',
                        'styles/style.css' => 'Styles',
                        'config/database.php' => 'Database config',
                        'api/auth.php' => 'Authentication API',
                        'api/routes.php' => 'Routes API',
                        'api/buses.php' => 'Buses API',
                        'api/bookings.php' => 'Bookings API'
                    ];
                    
                    echo '<div class="space-y-2">';
                    foreach ($requiredFiles as $file => $description) {
                        $exists = file_exists($file);
                        $statusClass = $exists ? 'text-green-600' : 'text-red-600';
                        $icon = $exists ? 'fa-check' : 'fa-times';
                        
                        echo '<div class="flex justify-between items-center">';
                        echo '<span class="text-sm">' . $file . '</span>';
                        echo '<span class="' . $statusClass . '">';
                        echo '<i class="fas ' . $icon . '"></i> ';
                        echo $exists ? 'Found' : 'Missing';
                        echo '</span>';
                        echo '</div>';
                    }
                    echo '</div>';
                    ?>
                </div>
            </div>

            <!-- System Information -->
            <div class="card bg-white shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-xl">
                        <i class="fas fa-info-circle text-purple-600"></i> System Information
                    </h2>
                    <div class="divider"></div>
                    
                    <div class="space-y-2 text-sm">
                        <div><strong>PHP Version:</strong> <?php echo phpversion(); ?></div>
                        <div><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></div>
                        <div><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></div>
                        <div><strong>Current Path:</strong> <?php echo __DIR__; ?></div>
                        <div><strong>MySQL Available:</strong> 
                            <?php echo extension_loaded('pdo_mysql') ? 'Yes' : 'No'; ?>
                        </div>
                        <div><strong>Session Support:</strong> 
                            <?php echo extension_loaded('session') ? 'Yes' : 'No'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card bg-white shadow-xl mt-6">
            <div class="card-body">
                <h2 class="card-title text-xl">
                    <i class="fas fa-tools text-orange-600"></i> Quick Actions
                </h2>
                <div class="divider"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="database_fix.php" class="btn btn-primary">
                        <i class="fas fa-wrench"></i> Fix Database
                    </a>
                    <a href="admin/login.html" class="btn btn-secondary">
                        <i class="fas fa-sign-in-alt"></i> Admin Login
                    </a>
                    <a href="index.html" class="btn btn-accent">
                        <i class="fas fa-home"></i> Main Site
                    </a>
                </div>
                
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-bold text-blue-800 mb-2">Default Admin Credentials:</h3>
                    <div class="text-sm text-blue-700">
                        <div><strong>Email:</strong> admin@upayticket.com</div>
                        <div><strong>Password:</strong> admin123</div>
                        <div class="mt-2 text-xs">
                            <i class="fas fa-info-circle"></i> 
                            These credentials are created automatically if admin_users table is empty
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test API endpoints
        async function testAPI() {
            const apiStatus = document.getElementById('api-status');
            const endpoints = [
                { name: 'Auth API', url: 'api/auth.php', method: 'POST', data: { action: 'test' } },
                { name: 'Routes API', url: 'api/routes.php', method: 'GET' },
                { name: 'Buses API', url: 'api/buses.php', method: 'GET' },
                { name: 'Bookings API', url: 'api/bookings.php', method: 'GET' }
            ];

            let results = '<div class="space-y-2">';
            
            for (const endpoint of endpoints) {
                try {
                    const options = {
                        method: endpoint.method,
                        headers: { 'Content-Type': 'application/json' }
                    };
                    
                    if (endpoint.data) {
                        options.body = JSON.stringify(endpoint.data);
                    }
                    
                    const response = await fetch(endpoint.url, options);
                    const statusClass = response.ok ? 'text-green-600' : 'text-red-600';
                    const icon = response.ok ? 'fa-check' : 'fa-times';
                    
                    results += `
                        <div class="flex justify-between items-center">
                            <span class="text-sm">${endpoint.name}</span>
                            <span class="${statusClass}">
                                <i class="fas ${icon}"></i> ${response.status}
                            </span>
                        </div>
                    `;
                } catch (error) {
                    results += `
                        <div class="flex justify-between items-center">
                            <span class="text-sm">${endpoint.name}</span>
                            <span class="text-red-600">
                                <i class="fas fa-times"></i> Error
                            </span>
                        </div>
                    `;
                }
            }
            
            results += '</div>';
            apiStatus.innerHTML = results;
        }

        // Run API tests on page load
        testAPI();
    </script>
</body>
</html>
