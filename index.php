<?php
// Track user visit
if (file_exists(__DIR__ . '/track.php')) {
    include_once('track.php');
}

// Get REAL total users
$db_host = 'sql104.infinityfree.com';
$db_user = 'if0_40608932';
$db_pass = 'LUTinImGXZw';
$db_name = 'if0_40608932_old_studio';

$total_users = 0;

// Try database connection
$db_available = function_exists('mysqli_init');
$conn = false;
if ($db_available) {
    @mysqli_report(MYSQLI_REPORT_OFF);
    $db_options = mysqli_init();
    if ($db_options) {
        $db_options->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
        @$db_options->real_connect($db_host, $db_user, $db_pass, $db_name);
    }
    $conn = $db_options;
}

if ($conn && !$conn->connect_error) {
    // Get unique IPs from last 30 days
    $sql = "SELECT COUNT(DISTINCT ip_address) as total FROM visits WHERE visit_time > DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $total_users = (int)$row['total'];
    }
    $conn->close();
} else {
    // File-based count
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

// Minimum 1
if ($total_users < 1) {
    $total_users = 1;
}

// Function to get tool download counts
function getToolDownloadCounts() {
    global $db_host, $db_user, $db_pass, $db_name, $db_available;
    
    $counts = [];
    
    $conn = false;
    if ($db_available) {
        @mysqli_report(MYSQLI_REPORT_OFF);
        $db_options = mysqli_init();
        if ($db_options) {
            $db_options->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
            @$db_options->real_connect($db_host, $db_user, $db_pass, $db_name);
        }
        $conn = $db_options;
    }
    
    if ($conn && !$conn->connect_error) {
        $sql = "SELECT tool_title, download_count FROM tool_downloads";
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $counts[$row['tool_title']] = (int)$row['download_count'];
            }
        }
        $conn->close();
    } else {
        // File-based counts
        $download_file = __DIR__ . '/tool_downloads.json';
        if (file_exists($download_file)) {
            $json_data = @file_get_contents($download_file);
            if ($json_data) {
                $counts = json_decode($json_data, true);
            }
        }
    }
    
    return $counts;
}

// Get all download counts
$download_counts = getToolDownloadCounts();

// Load tools from JSON file
$tools = [];
$tools_file = __DIR__ . '/tools.json';
if (file_exists($tools_file)) {
    $json_data = file_get_contents($tools_file);
    $tools_data = json_decode($json_data, true);
    
    if (is_array($tools_data)) {
        foreach ($tools_data as $tool) {
            $tool['download_count'] = isset($download_counts[$tool['title']]) ? $download_counts[$tool['title']] : 0;
            $tools[] = $tool;
        }
    }
}

// If no tools loaded from JSON, use empty array
if (empty($tools)) {
    $tools = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLD-STUDIO TOOLKIT - Professional Tools Collection</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 8px;
            color: #333;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            padding: 12px 8px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 10px;
            animation: none;
            position: relative;
        }

        .header h1 {
            font-size: 1.2em;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 0.65em;
            color: #666;
            margin-bottom: 6px;
        }

        .stats {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.6em;
            font-weight: 600;
            box-shadow: 0 3px 8px rgba(102, 126, 234, 0.4);
        }

        .stats i {
            margin-right: 5px;
        }

        /* Admin Menu Button */
        .admin-menu-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
        }

        .admin-menu-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.6);
        }

        .toolkit-label {
            position: absolute;
            top: 12px;
            left: 12px;
            color: #667eea;
            font-size: 0.7em;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Tools Grid - Exactly 4 columns */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 10px;
        }

        .tool-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            animation: none;
        }

        .tool-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .tool-image {
            width: 100%;
            height: 80px;
            object-fit: contain;
            border-bottom: 1px solid #667eea;
            background: #f5f5f5;
            padding: 3px;
        }

        .tool-content {
            padding: 6px;
        }

        .tool-title {
            font-size: 0.5em;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            min-height: 24px;
            display: flex;
            align-items: center;
            line-height: 1.1;
        }

        /* Download info */
        .download-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .download-count {
            font-size: 0.45em;
            color: #666;
            font-weight: 500;
        }

        .download-count i {
            margin-right: 2px;
            color: #667eea;
        }

        .download-btn {
            display: block;
            width: 100%;
            padding: 5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.5em;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
        }

        .download-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5);
        }

        .download-btn i {
            margin-right: 4px;
        }

        /* Footer */
        .footer {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            animation: none;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .footer-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.55em;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .footer-link:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5);
        }

        .footer-link i {
            font-size: 1em;
        }

        .powered-by {
            font-size: 0.6em;
            color: #666;
            font-weight: 500;
            margin-top: 8px;
        }

        .powered-by strong {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 600px) {
            .tools-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Image loading */
        .tool-image.error {
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <span class="toolkit-label">TOOLKIT</span>
            <button class="admin-menu-btn" onclick="openAdminMenu()" title="Admin Panel">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <h1><i class="fas fa-toolbox"></i> OLD-STUDIO TOOLKIT</h1>
            <p>Professional Tools & Methods Collection</p>
            <div class="stats">
                <i class="fas fa-users"></i>
                Total Users: <span id="userCount"><?php echo number_format($total_users); ?></span>
            </div>
        </div>

        <!-- Tools Grid -->
        <div class="tools-grid">
            <?php foreach ($tools as $tool): ?>
            <div class="tool-card">
                <img src="<?php echo htmlspecialchars($tool['image']); ?>" 
                     alt="<?php echo htmlspecialchars($tool['title']); ?>" 
                     class="tool-image"
                     loading="lazy"
                     decoding="async"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                     crossorigin="anonymous">
                <div style="display:none; width:100%; height:80px; background:#f5f5f5; border-bottom:1px solid #667eea; align-items:center; justify-content:center; color:#999; font-size:0.9em;">
                    Image Error
                </div>
                <div class="tool-content">
                    <h3 class="tool-title"><?php echo htmlspecialchars($tool['title']); ?></h3>
                    <div class="download-info">
                        <span class="download-count" id="count-<?php echo md5($tool['title']); ?>">
                            <i class="fas fa-download"></i> <?php echo number_format($tool['download_count']); ?>
                        </span>
                    </div>
                    <a href="<?php echo htmlspecialchars($tool['download']); ?>" 
                       class="download-btn" 
                       target="_blank"
                       data-tool-title="<?php echo htmlspecialchars($tool['title']); ?>"
                       onclick="trackDownload('<?php echo htmlspecialchars($tool['title']); ?>')">
                        <i class="fas fa-download"></i> DOWNLOAD
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-links">
                <a href="https://whatsapp.com/channel/0029VavHzv259PwTIz1XxJ09" 
                   class="footer-link" 
                   target="_blank">
                    <i class="fab fa-whatsapp"></i>
                    JOIN WHATSAPP
                </a>
                <a href="mailto:oldstudio786fff@gmail.com" 
                   class="footer-link">
                    <i class="fas fa-envelope"></i>
                    SUPPORT EMAIL
                </a>
            </div>
            <p class="powered-by">POWERED BY <strong>OLD-STUDIO</strong></p>
        </div>
    </div>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Real-time counter update (every 60 seconds)
        function updateCounter() {
            fetch('total_user.php')
                .then(response => response.text())
                .then(count => {
                    const userCount = document.getElementById('userCount');
                    const current = parseInt(userCount.textContent.replace(/,/g, ''));
                    const newCount = parseInt(count);
                    
                    if (newCount > current) {
                        // Animate counter increase
                        let currentNum = current;
                        const interval = setInterval(() => {
                            currentNum++;
                            userCount.textContent = currentNum.toLocaleString();
                            
                            if (currentNum >= newCount) {
                                clearInterval(interval);
                            }
                        }, 50);
                    }
                })
                .catch(error => console.error('Counter update error:', error));
        }

        // Update counter every 60 seconds
        setInterval(updateCounter, 60000);

        // Track download function
        function trackDownload(toolTitle) {
            // Send request to track download
            fetch('download_track.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'tool_title=' + encodeURIComponent(toolTitle)
            })
            .then(response => response.text())
            .then(newCount => {
                // Update the count display for this tool
                const countElement = document.getElementById('count-' + md5(toolTitle));
                if (countElement) {
                    countElement.innerHTML = `<i class="fas fa-download"></i> ${parseInt(newCount).toLocaleString()}`;
                }
            })
            .catch(error => console.error('Download tracking error:', error));
        }

        // Simple MD5 function for element IDs
        function md5(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }
            return Math.abs(hash).toString(16);
        }

        // Admin menu function
        function openAdminMenu() {
            window.location.href = 'admin/login.php';
        }
    </script>
</body>
</html>
