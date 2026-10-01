<?php
// Admin Panel Configuration

// Change these two values to update admin login credentials.
define('ADMIN_USERNAME', 'void_systems_inc');
define('ADMIN_PASSWORD', 'void_toolkit3399');

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 3600); // 1 hour

// Tools data file path
define('TOOLS_FILE', __DIR__ . '/../tools.json');

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Load tools from JSON file
function loadTools() {
    if (!file_exists(TOOLS_FILE)) {
        return [];
    }
    
    $json_data = file_get_contents(TOOLS_FILE);
    $tools = json_decode($json_data, true);
    
    return is_array($tools) ? $tools : [];
}

// Save tools to JSON file
function saveTools($tools) {
    $json_data = json_encode($tools, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return file_put_contents(TOOLS_FILE, $json_data) !== false;
}

// Add a new tool
function addTool($title, $image, $download) {
    $tools = loadTools();
    
    // Check if tool already exists
    foreach ($tools as $tool) {
        if (strtolower($tool['title']) === strtolower($title)) {
            return ['success' => false, 'message' => 'Tool with this title already exists'];
        }
    }
    
    $newTool = [
        'title' => trim($title),
        'image' => trim($image),
        'download' => trim($download)
    ];
    
    $tools[] = $newTool;
    
    if (saveTools($tools)) {
        return ['success' => true, 'message' => 'Tool added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to save tool'];
    }
}

// Delete a tool by title
function deleteTool($title) {
    $tools = loadTools();
    $updated_tools = [];
    $found = false;
    
    foreach ($tools as $tool) {
        if ($tool['title'] !== $title) {
            $updated_tools[] = $tool;
        } else {
            $found = true;
        }
    }
    
    if (!$found) {
        return ['success' => false, 'message' => 'Tool not found'];
    }
    
    if (saveTools($updated_tools)) {
        return ['success' => true, 'message' => 'Tool deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete tool'];
    }
}

// Edit a tool
function editTool($oldTitle, $newTitle, $newImage, $newDownload) {
    $tools = loadTools();
    $found = false;
    
    foreach ($tools as &$tool) {
        if ($tool['title'] === $oldTitle) {
            $tool['title'] = trim($newTitle);
            $tool['image'] = trim($newImage);
            $tool['download'] = trim($newDownload);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        return ['success' => false, 'message' => 'Tool not found'];
    }
    
    if (saveTools($tools)) {
        return ['success' => true, 'message' => 'Tool updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to update tool'];
    }
}

// Get a tool by title
function getTool($title) {
    $tools = loadTools();
    
    foreach ($tools as $tool) {
        if ($tool['title'] === $title) {
            return $tool;
        }
    }
    
    return null;
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>
