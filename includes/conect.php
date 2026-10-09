<?php
// conect.php - الاتصال بقاعدة البيانات (النسخة النهائية)

$host = 'localhost';
$dbname = 'azouz_bookstore';
$username = 'root';
$password = '';

try {
    $con = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // دالة لجلب جميع الكتب المتاحة
    function getAvailableBooks($con, $limit = 12) {
        try {
            $stmt = $con->prepare("CALL GetAvailableBooks(?)");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            // إذا فشل الإجراء المخزن، استخدم الاستعلام المباشر
            $stmt = $con->prepare("
                SELECT b.*, u.full_name as seller_name, s.name as section_name 
                FROM books b 
                LEFT JOIN users u ON b.seller_id = u.id 
                LEFT JOIN sections s ON b.section_id = s.id 
                WHERE b.status = 'متاح' 
                ORDER BY b.created_at DESC 
                LIMIT " . (int)$limit
            );
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }
    
    // دالة للبحث عن الكتب
    function searchBooks($con, $keyword) {
        try {
            $stmt = $con->prepare("CALL SearchBooks(?)");
            $stmt->execute([$keyword]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            // استعلام بديل إذا فشل الإجراء المخزن
            $stmt = $con->prepare("
                SELECT b.*, u.full_name as seller_name 
                FROM books b 
                LEFT JOIN users u ON b.seller_id = u.id 
                WHERE b.status = 'متاح' 
                AND (b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)
                ORDER BY b.created_at DESC
            ");
            $searchTerm = "%$keyword%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            return $stmt->fetchAll();
        }
    }
    
    // باقي الدوال كما هي...
    function getBooksBySection($con, $section_id) {
        $stmt = $con->prepare("
            SELECT b.*, u.full_name as seller_name 
            FROM books b 
            LEFT JOIN users u ON b.seller_id = u.id 
            WHERE b.section_id = ? AND b.status = 'متاح'
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$section_id]);
        return $stmt->fetchAll();
    }
    
    function isBookAvailable($con, $book_id) {
        $stmt = $con->prepare("SELECT status FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();
        return ($book && $book['status'] == 'متاح');
    }
    
    function markBookAsSold($con, $book_id) {
        $stmt = $con->prepare("UPDATE books SET status = 'تم البيع' WHERE id = ?");
        return $stmt->execute([$book_id]);
    }
    
    function getUserBooks($con, $user_id) {
        $stmt = $con->prepare("
            SELECT b.*, s.name as section_name 
            FROM books b 
            LEFT JOIN sections s ON b.section_id = s.id 
            WHERE b.seller_id = ? 
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    function addBook($con, $data, $image) {
        $stmt = $con->prepare("
            INSERT INTO books (title, author, description, price, section_id, seller_id, book_condition, image, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'متاح')
        ");
        return $stmt->execute([
            $data['title'],
            $data['author'],
            $data['description'],
            $data['price'],
            $data['section_id'],
            $data['seller_id'],
            $data['book_condition'],
            $image
        ]);
    }
    
    function getAllSections($con) {
        $stmt = $con->prepare("SELECT * FROM sections WHERE is_active = 1 ORDER BY sort_order, name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    function getBookDetails($con, $book_id) {
        $stmt = $con->prepare("
            SELECT b.*, u.full_name as seller_name, u.phone as seller_phone, u.email as seller_email,
                   s.name as section_name 
            FROM books b 
            LEFT JOIN users u ON b.seller_id = u.id 
            LEFT JOIN sections s ON b.section_id = s.id 
            WHERE b.id = ?
        ");
        $stmt->execute([$book_id]);
        return $stmt->fetch();
    }
    
    function createOrder($con, $userId, $bookId, $address, $phone, $paymentId) {
        $stmt = $con->prepare("CALL CreateOrder(?, ?, ?, ?, ?, @order_number)");
        $stmt->execute([$userId, $bookId, $address, $phone, $paymentId]);
        
        $result = $con->query("SELECT @order_number as order_number")->fetch();
        return $result['order_number'];
    }
    
    function logActivity($con, $userId, $action, $description) {
        $stmt = $con->prepare("
            INSERT INTO activity_log (user_id, action, description, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return $stmt->execute([$userId, $action, $description, $ip]);
    }
    
} catch(PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>