<?php
// Database configuration for InfinityFree
$db_host = 'sql104.infinityfree.com';
$db_user = 'if0_40608932';
$db_pass = 'LUTinImGXZw';
$db_name = 'if0_40608932_old_studio';

// Default 0
$total_users = 0;

// Try database connection
@mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

if (!$conn->connect_error) {
    // Get REAL unique IPs from last 30 days
    $sql = "SELECT COUNT(DISTINCT ip_address) as total FROM visits WHERE visit_time > DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        $total_users = (int)$row['total'];
    }
    
    $conn->close();
} else {
    // File-based: Count unique IPs from last 30 days
    $visitor_files = glob(__DIR__ . '/visitors_*.txt');
    $all_ips = [];
    $thirty_days_ago = strtotime('-30 days');
    
    foreach ($visitor_files as $file) {
        if (file_exists($file) && filemtime($file) >= $thirty_days_ago) {
            $ips = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($ips) {
                $all_ips = array_merge($all_ips, $ips);
            }
        }
    }
    
    $unique_ips = array_unique($all_ips);
    $total_users = count($unique_ips);
}

// Minimum 1 (current visitor)
if ($total_users < 1) {
    $total_users = 1;
}

// Output count
echo $total_users;
?>