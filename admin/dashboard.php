<?php
include_once('config.php');
requireLogin();

$tools = loadTools();
$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';
$message = '';
$error = '';

// Handle tool operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = isset($_POST['operation']) ? sanitize($_POST['operation']) : '';
    
    if ($operation === 'add') {
        $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
        $image = isset($_POST['image']) ? sanitize($_POST['image']) : '';
        $download = isset($_POST['download']) ? sanitize($_POST['download']) : '';
        
        if (empty($title) || empty($image) || empty($download)) {
            $error = 'All fields are required';
        } else {
            $result = addTool($title, $image, $download);
            if ($result['success']) {
                $message = $result['message'];
                $tools = loadTools();
            } else {
                $error = $result['message'];
            }
        }
    } elseif ($operation === 'delete') {
        $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
        
        if (empty($title)) {
            $error = 'Tool title is required';
        } else {
            $result = deleteTool($title);
            if ($result['success']) {
                $message = $result['message'];
                $tools = loadTools();
            } else {
                $error = $result['message'];
            }
        }
    } elseif ($operation === 'edit') {
        $oldTitle = isset($_POST['old_title']) ? sanitize($_POST['old_title']) : '';
        $newTitle = isset($_POST['title']) ? sanitize($_POST['title']) : '';
        $newImage = isset($_POST['image']) ? sanitize($_POST['image']) : '';
        $newDownload = isset($_POST['download']) ? sanitize($_POST['download']) : '';
        
        if (empty($oldTitle) || empty($newTitle) || empty($newImage) || empty($newDownload)) {
            $error = 'All fields are required';
        } else {
            $result = editTool($oldTitle, $newTitle, $newImage, $newDownload);
            if ($result['success']) {
                $message = $result['message'];
                $tools = loadTools();
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get tool to edit
$editTool = null;
if ($action === 'edit' && isset($_GET['title'])) {
    $editTitle = sanitize($_GET['title']);
    $editTool = getTool($editTitle);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN DASHBOARD - OLD-STUDIO TOOLKIT</title>
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
            padding: 12px;
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .dashboard-header {
            background: white;
            padding: 14px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .dashboard-header h1 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.35em;
        }

        .header-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-danger {
            background: #ff6b6b;
            color: white;
        }

        .btn-danger:hover {
            background: #ff5252;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #f5c6cb;
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 900px) {
            .content {
                grid-template-columns: 1fr;
            }
        }

        .section {
            background: white;
            padding: 14px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .section h2 {
            color: #333;
            margin-bottom: 12px;
            font-size: 1.3em;
            border-bottom: 2px solid #667eea;
            padding-bottom: 6px;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 600;
            font-size: 0.9em;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.2);
        }

        .tools-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .tool-item {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 6px;
            border-left: 4px solid #667eea;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .tool-info {
            flex: 1;
            min-width: 200px;
        }

        .tool-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .tool-actions {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.85em;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .form-actions button {
            flex: 1;
        }

        .back-link {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-cog"></i> ADMIN DASHBOARD</h1>
            <div class="header-actions">
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> BACK TO TOOLKIT
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> LOGOUT
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="message error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="content">
            <!-- Add/Edit Tool Section -->
            <div class="section">
                <h2>
                    <?php if ($action === 'edit' && $editTool): ?>
                        <i class="fas fa-edit"></i> EDIT TOOL
                    <?php else: ?>
                        <i class="fas fa-plus"></i> ADD NEW TOOL
                    <?php endif; ?>
                </h2>

                <form method="POST" action="">
                    <input type="hidden" name="operation" value="<?php echo ($action === 'edit' && $editTool) ? 'edit' : 'add'; ?>">
                    
                    <?php if ($action === 'edit' && $editTool): ?>
                    <input type="hidden" name="old_title" value="<?php echo htmlspecialchars($editTool['title']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title">TOOL NAME *</label>
                        <input type="text" id="title" name="title" placeholder="Enter tool name" 
                               value="<?php echo ($editTool) ? htmlspecialchars($editTool['title']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="image">THUMBNAIL URL *</label>
                        <input type="url" id="image" name="image" placeholder="Enter image URL" 
                               value="<?php echo ($editTool) ? htmlspecialchars($editTool['image']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="download">DOWNLOAD LINK *</label>
                        <input type="url" id="download" name="download" placeholder="Enter download URL" 
                               value="<?php echo ($editTool) ? htmlspecialchars($editTool['download']) : ''; ?>" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo ($action === 'edit' && $editTool) ? 'Update Tool' : 'Add Tool'; ?>
                        </button>
                        <?php if ($action === 'edit' && $editTool): ?>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> CANCEL
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tools List Section -->
            <div class="section">
                <h2><i class="fas fa-list"></i> MANAGE TOOLS (<?php echo count($tools); ?>)</h2>
                
                <div class="tools-list">
                    <?php if (empty($tools)): ?>
                    <p style="text-align: center; color: #999; padding: 20px;">NO TOOLS FOUND. ADD YOUR FIRST TOOL!</p>
                    <?php else: ?>
                        <?php foreach ($tools as $tool): ?>
                        <div class="tool-item">
                            <div class="tool-info">
                                <div class="tool-title"><?php echo htmlspecialchars($tool['title']); ?></div>
                                <small style="color: #666;">
                                    <i class="fas fa-link"></i> 
                                    <?php echo substr(htmlspecialchars($tool['download']), 0, 50) . '...'; ?>
                                </small>
                            </div>
                            <div class="tool-actions">
                                <a href="?action=edit&title=<?php echo urlencode($tool['title']); ?>" class="btn btn-small btn-primary">
                                    <i class="fas fa-edit"></i> EDIT
                                </a>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this tool?');">
                                    <input type="hidden" name="operation" value="delete">
                                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($tool['title']); ?>">
                                    <button type="submit" class="btn btn-small btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
