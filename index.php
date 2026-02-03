<?php
// --- KONFIGURASI DATABASE & REDIS (Sama seperti sebelumnya) ---
$db_host = "lks-db.xxxxxx.ap-southeast-1.rds.amazonaws.com"; // Ganti Endpoint RDS
$db_user = "admin";
$db_pass = "password123";
$db_name = "toko_db";

$redis_host = "127.0.0.1"; // Jika Redis diinstall di masing-masing EC2 (Local)
// $redis_host = "10.0.x.x"; // Jika Redis menggunakan ElastiCache/Server Terpisah
$redis_port = 6379;

// --- IDENTITAS SERVER (PENTING UNTUK PENILAIAN ALB) ---
// Mengambil Private IP server untuk membuktikan Load Balancing berjalan
$server_ip = $_SERVER['SERVER_ADDR'];
$server_name = gethostname();

// --- LOGIKA REDIS (COUNTER) ---
$redis = new Redis();
$redis_msg = "";
$visitor_count = 0;

try {
    $redis->connect($redis_host, $redis_port);
    $visitor_count = $redis->incr("total_pengunjung");
    $redis_msg = "<span style='color:green'>Connected</span>";
} catch (Exception $e) {
    $redis_msg = "<span style='color:red'>Error</span>";
}

// --- LOGIKA DATABASE ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
$db_msg = $conn->connect_error ? "<span style='color:red'>Error</span>" : "<span style='color:green'>Connected</span>";
$produk = [];

if (!$conn->connect_error) {
    $result = $conn->query("SELECT * FROM product"); //isi product sesuai tabel 
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $produk[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LKS Store - Load Balanced</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
        .box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .highlight { color: #d9534f; font-weight: bold; }
        .server-info { background: #d9edf7; color: #31708f; padding: 10px; border-radius: 4px; margin-bottom: 15px;}
    </style>
</head>
<body>
    <div class="box">
        <h2>🛍️ LKS Online Store</h2>
        
        <div class="server-info">
            <strong>Serving from:</strong> <?php echo $server_name . " (" . $server_ip . ")"; ?>
        </div>

        <p>Status DB: <?php echo $db_msg; ?> | Status Redis: <?php echo $redis_msg; ?></p>
        <h3>Total Pengunjung: <span class="highlight"><?php echo $visitor_count; ?></span></h3>
    </div>
</body>
</html>
