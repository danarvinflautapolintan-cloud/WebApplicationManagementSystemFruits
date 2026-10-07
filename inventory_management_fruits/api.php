<?php
// api.php - REST API endpoints
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/database.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDBConnection();

try {
    switch ($action) {
        case 'dashboard':
            handleDashboard($pdo);
            break;
        case 'products':
            handleProducts($pdo, $method);
            break;
        case 'transactions':
            handleTransactions($pdo, $method);
            break;
        case 'users':
            handleUsers($pdo, $method);
            break;
        case 'reports':
            handleReports($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleDashboard($pdo) {
    try {
        $totalProducts = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch()['count'] ?? 0;
        $totalValue = $pdo->query("SELECT SUM(stock * price) as value FROM products")->fetch()['value'] ?? 0;
        $totalCost = $pdo->query("SELECT SUM(stock * cost) as value FROM products")->fetch()['value'] ?? 0;
        $lowStockCount = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock <= min_stock")->fetch()['count'] ?? 0;
        $categories = $pdo->query("SELECT COUNT(DISTINCT category) as count FROM products")->fetch()['count'] ?? 0;
        
        $categoryData = $pdo->query("SELECT category as name, SUM(stock) as value FROM products GROUP BY category")->fetchAll();
        $topProducts = $pdo->query("SELECT * FROM products ORDER BY stock DESC LIMIT 6")->fetchAll();
        $lowStockProducts = $pdo->query("SELECT * FROM products WHERE stock <= min_stock")->fetchAll();
        $monthlySales = $pdo->query("SELECT * FROM monthly_sales ORDER BY FIELD(month, 'Mar','Apr','May','Jun','Jul','Aug')")->fetchAll();
        
        echo json_encode([
            'success' => true,
            'totalProducts' => (int)$totalProducts,
            'totalValue' => (float)$totalValue,
            'totalCost' => (float)$totalCost,
            'lowStockCount' => (int)$lowStockCount,
            'categories' => (int)$categories,
            'categoryData' => $categoryData,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'monthlySales' => $monthlySales
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleProducts($pdo, $method) {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT * FROM products ORDER BY name");
            echo json_encode($stmt->fetchAll());
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO products (name, category, origin, unit, price, cost, stock, min_stock, description, season, image) 
                                  VALUES (:name, :category, :origin, :unit, :price, :cost, :stock, :min_stock, :description, :season, :image)");
            $stmt->execute($data);
            echo json_encode(['id' => $pdo->lastInsertId(), 'success' => true]);
            break;
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                return;
            }
            unset($data['id']);
            $setClause = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $stmt = $pdo->prepare("UPDATE products SET $setClause WHERE id = :id");
            $data['id'] = $id;
            $stmt->execute($data);
            echo json_encode(['success' => true]);
            break;
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleTransactions($pdo, $method) {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT t.*, p.name as product_name, p.unit 
                            FROM transactions t 
                            JOIN products p ON t.product_id = p.id 
                            ORDER BY t.date DESC, t.id DESC");
        echo json_encode($stmt->fetchAll());
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO transactions (product_id, type, qty, date, by_user, note) 
                                  VALUES (:product_id, :type, :qty, :date, :by_user, :note)");
            $stmt->execute($data);
            
            $sign = $data['type'] === 'IN' ? '+' : '-';
            $stmt = $pdo->prepare("UPDATE products SET stock = stock $sign :qty WHERE id = :product_id");
            $stmt->execute(['qty' => $data['qty'], 'product_id' => $data['product_id']]);
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleUsers($pdo, $method) {
    $session = $_SESSION;
    
    // Allow users to update their own profile
    $isOwnProfile = false;
    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        if ($id && $id == $session['user_id']) {
            $isOwnProfile = true;
        }
    }
    
    // Check permissions
    if ($session['user']['role'] !== 'Admin' && !$isOwnProfile) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        return;
    }
    
    if ($method === 'GET') {
        if ($session['user']['role'] === 'Admin') {
            $stmt = $pdo->query("SELECT id, name, email, role, avatar, joined FROM users");
        } else {
            $stmt = $pdo->prepare("SELECT id, name, email, role, avatar, joined FROM users WHERE id = ?");
            $stmt->execute([$session['user_id']]);
        }
        echo json_encode($stmt->fetchAll());
        
    } elseif ($method === 'POST') {
        if ($session['user']['role'] !== 'Admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $avatar = implode('', array_map(fn($w) => $w[0], explode(' ', $data['name'])));
        $avatar = strtoupper(substr($avatar, 0, 2));
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, avatar) 
                              VALUES (:name, :email, :password, :role, :avatar)");
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => $data['role'],
            'avatar' => $avatar
        ]);
        echo json_encode(['success' => true]);
        
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID required']);
            return;
        }
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        $updates = [];
        $params = ['id' => $id];
        
        // Allow updating name and email
        if (isset($data['name']) && !empty($data['name'])) {
            $updates[] = "name = :name";
            $params['name'] = $data['name'];
            
            // Update avatar based on new name
            $nameParts = explode(' ', $data['name']);
            if (count($nameParts) >= 2) {
                $avatar = strtoupper($nameParts[0][0] . $nameParts[count($nameParts)-1][0]);
            } else {
                $avatar = strtoupper(substr($data['name'], 0, 2));
            }
            $updates[] = "avatar = :avatar";
            $params['avatar'] = $avatar;
        }
        if (isset($data['email']) && !empty($data['email'])) {
            // Check if email already exists for another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $id]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Email already exists']);
                return;
            }
            $updates[] = "email = :email";
            $params['email'] = $data['email'];
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $updates[] = "password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['role']) && !empty($data['role']) && $session['user']['role'] === 'Admin') {
            $updates[] = "role = :role";
            $params['role'] = $data['role'];
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            return;
        }
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // If updating own profile, refresh session
        if ($id == $session['user_id']) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['user'] = $stmt->fetch();
        }
        
        echo json_encode(['success' => true]);
        
    } elseif ($method === 'DELETE') {
        if ($session['user']['role'] !== 'Admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID required']);
            return;
        }
        if ($id == $session['user_id']) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete yourself']);
            return;
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}
function handleReports($pdo) {
    $totalValue = $pdo->query("SELECT SUM(stock * price) as value FROM products")->fetch()['value'] ?? 0;
    $totalCost = $pdo->query("SELECT SUM(stock * cost) as value FROM products")->fetch()['value'] ?? 0;
    $profit = $totalValue - $totalCost;
    $margin = $totalValue > 0 ? ($profit / $totalValue) * 100 : 0;
    $transactions = $pdo->query("SELECT COUNT(*) as count FROM transactions")->fetch()['count'] ?? 0;
    
    $products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
    $categoryData = $pdo->query("SELECT category as name, SUM(stock) as value FROM products GROUP BY category")->fetchAll();
    $monthlySales = $pdo->query("SELECT * FROM monthly_sales ORDER BY FIELD(month, 'Mar','Apr','May','Jun','Jul','Aug')")->fetchAll();
    
    echo json_encode([
        'totalValue' => (float)$totalValue,
        'totalCost' => (float)$totalCost,
        'profit' => (float)$profit,
        'margin' => (float)$margin,
        'transactions' => (int)$transactions,
        'products' => $products,
        'categoryData' => $categoryData,
        'monthlySales' => $monthlySales
    ]);
}
?>