<?php
define('FLAG', 'flag{php_include_is_dangerous_2026_AetherBreach_polyglot}');

$upload_dir = 'uploads/';
$error = null;
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['image']['name'])) {
    $file = $_FILES['image'];
    $filename = basename($file['name']);
    $upload_path = $upload_dir . $filename;

    $head = file_get_contents($file['tmp_name'], false, null, 0, 256);
    if (strlen($head) < 10) {
        $error = "File is empty or too small.";
    } else {
        $info = @getimagesizefromstring($head);
        if (!$info || $info[2] !== IMAGETYPE_JPEG) {
            $error = "Only valid JPEG images accepted (first 256 bytes checked).";
        }
    }

    if (!$error) {
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0777, true);
            @chmod($upload_dir, 0777);
        }

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $content = file_get_contents($upload_path);
            $is_php_intent = false;

            $prefix = substr($content, 0, 4096);
            if (stripos($prefix, '<?php') !== false || stripos($prefix, '<?=') !== false) {
                $is_php_intent = true;
            } else {
                $tokens = @token_get_all($prefix);
                foreach ($tokens as $token) {
                    if (is_array($token) && $token[0] === T_OPEN_TAG) {
                        $is_php_intent = true;
                        break;
                    }
                }
            }

            ob_start();
            @include $upload_path;
            $raw_output = ob_get_clean();

            if ($is_php_intent) {
                $output = $raw_output;
            } else {
                $output = <<<HTML
<div style="background: var(--secondary); border: 1px solid var(--border); border-radius: 12px; padding: 24px; font-family: monospace;">
<h4 style="color: var(--accent); margin-top: 0;">AetherVision Deep Scan Report</h4>
<p><strong>Status:</strong> Complete (quantum coherence achieved)</p>
<p><strong>Entropy signature:</strong> 7.82 bits/px – natural image</p>
<p><strong>Detected classes:</strong> landscape, atmospheric effects, high-frequency detail</p>
<p><strong>Security envelope:</strong> <span style="color: #ef4444;">Protected by immutable FLAG constant</span></p>
<p style="color: var(--text-muted); font-size: 0.85rem;">The FLAG is embedded deep in the engine and should be unreachable from upload context.</p>
</div>
HTML;
            }

            echo $output;
            exit;
        } else {
            $error = "Failed to store file.<br><small>Target: <code>" . htmlspecialchars($upload_path) . "</code><br>Directory permissions issue?</small>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AetherVision AI – Profile Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f172a;
            --secondary: #1e293b;
            --accent: #3b82f6;
            --accent-light: #60a5fa;
            --gold: #f59e0b;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --border: rgba(148, 163, 184, 0.1);
        }
        * { font-family: 'Inter', sans-serif; }
        body { background: var(--primary); color: var(--text); min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; }
        .card { background: var(--secondary); border: 1px solid var(--border); border-radius: 16px; }
        .card-header { background: linear-gradient(135deg, var(--accent), var(--accent-light)); border-radius: 16px 16px 0 0 !important; padding: 20px 28px; }
        .card-body { padding: 28px; }
        .upload-box { border: 2px dashed var(--accent); border-radius: 12px; padding: 40px; text-align: center; background: rgba(59, 130, 246, 0.05); transition: all 0.3s; }
        .upload-box:hover { border-color: var(--accent-light); background: rgba(59, 130, 246, 0.1); }
        .btn-primary { background: var(--accent); border: none; font-weight: 600; padding: 12px 32px; }
        .btn-primary:hover { background: var(--accent-light); }
        .error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; padding: 16px; border-radius: 8px; margin-top: 20px; }
        .info-box { background: rgba(59, 130, 246, 0.1); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-top: 24px; }
        .back-link { color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 24px; transition: color 0.2s; }
        .back-link:hover { color: var(--text); }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-link"><i class="bi bi-arrow-left"></i> Back to Home</a>
        
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;"><i class="bi bi-image me-2"></i>Profile Settings</h3>
                <p style="margin: 8px 0 0 0; opacity: 0.9;">Upload your profile avatar</p>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="upload-box">
                        <i class="bi bi-cloud-arrow-up" style="font-size: 3rem; color: var(--accent);"></i>
                        <p style="margin: 16px 0 8px 0; font-weight: 500;">Upload JPEG Image</p>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">Drag and drop or click to select</p>
                        <input type="file" name="image" accept=".jpg,.jpeg" required class="form-control" style="max-width: 300px; margin: 0 auto;">
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-2"></i>Upload Avatar</button>
                    </div>
                </form>

                <?php if ($error): ?>
                    <div class="error"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="info-box">
                    <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Security note:</strong> Only the first 256 bytes are inspected for JPEG validity.
                        The FLAG constant is engine-level and cannot be reached… right?
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
