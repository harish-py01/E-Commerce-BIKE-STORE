<?php
// admin/product_images.php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
require_once __DIR__ . '/../includes/config.php';

// Ensure upload directory exists
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

// Helper: Score an image candidate
function score_image_candidate($url, $brand, $product_name, $category) {
    $score = 0;
    $url_lower = strtolower($url);
    $brand_lower = strtolower($brand);
    $prod_lower = strtolower($product_name);
    
    // +30: Exact brand match in URL
    if ($brand_lower !== '' && strpos($url_lower, $brand_lower) !== false) {
        $score += 30;
    }
    
    // +20: Exact category match in URL
    $cat_words = explode(' ', strtolower($category));
    foreach ($cat_words as $cw) {
        if (strlen($cw) > 3 && strpos($url_lower, $cw) !== false) {
            $score += 10;
        }
    }

    // -50: Unrelated generic keywords often found in bad matches
    $bad_words = ['generic', 'placeholder', 'dummy', 'vector', 'logo'];
    foreach ($bad_words as $bw) {
        if (strpos($url_lower, $bw) !== false) {
            $score -= 50;
        }
    }
    
    // Add points based on product name words
    $prod_words = explode(' ', $prod_lower);
    $matches = 0;
    foreach ($prod_words as $pw) {
        if (strlen($pw) > 2 && strpos($url_lower, $pw) !== false) {
            $matches++;
        }
    }
    if ($matches > 0) {
        $score += ($matches * 10);
    }
    
    // Base score for simply being found via a highly specific search query
    $score += 30;

    return $score;
}

// Helper: Download and Validate Image
function process_image_download($img_url, $product_id, $product_name) {
    global $conn;
    
    $ext = '.jpg';
    if (stripos($img_url, '.png') !== false) $ext = '.png';
    if (stripos($img_url, '.webp') !== false) $ext = '.webp';
    
    $safe_name = slugify($product_name) . '_' . time() . '_' . rand(100,999) . $ext;
    $temp_path = sys_get_temp_dir() . '/' . $safe_name;

    // Download
    $ch = curl_init();
    $fp = fopen($temp_path, 'wb');
    curl_setopt($ch, CURLOPT_URL, $img_url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if ($http_code !== 200) {
        @unlink($temp_path);
        return ['success' => false, 'error' => "HTTP $http_code"];
    }

    // Validate MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $temp_path);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime_type, $allowed_mimes)) {
        @unlink($temp_path);
        return ['success' => false, 'error' => "Invalid MIME: $mime_type"];
    }

    // Validate image dimensions
    $img_size = @getimagesize($temp_path);
    if ($img_size === false || $img_size[0] < 50 || $img_size[1] < 50) {
        @unlink($temp_path);
        return ['success' => false, 'error' => "Invalid image dimensions"];
    }
    
    // Duplicate Detection Hash
    $hash = md5_file($temp_path);
    // (In a full implementation we'd check hash against existing files here)

    // Save final
    $final_path = UPLOAD_PATH . $safe_name;
    if (!rename($temp_path, $final_path)) {
        return ['success' => false, 'error' => "Filesystem move failed"];
    }

    $db_path = 'uploads/products/' . $safe_name;
    
    // Update DB
    $stmt = $conn->prepare("UPDATE products SET product_image = ? WHERE id = ?");
    $stmt->bind_param("si", $db_path, $product_id);
    if ($stmt->execute()) {
        return ['success' => true, 'db_path' => $db_path, 'filename' => $safe_name];
    }
    
    @unlink($final_path);
    return ['success' => false, 'error' => "Database update failed"];
}

// Log Import
function log_image_import($pid, $pname, $query, $url, $domain, $dl_status, $val_status, $filename, $error) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO product_image_imports (product_id, product_name, search_query, selected_image_url, source_domain, download_status, validation_status, filename, error_message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssss", $pid, $pname, $query, $url, $domain, $dl_status, $val_status, $filename, $error);
    $stmt->execute();
}

// --- AJAX HANDLERS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    if ($action === 'get_missing_batch') {
        // Find products missing images
        $limit = (int)($_POST['limit'] ?? 5);
        $query = "SELECT p.id, p.name as product_name, b.name as brand_name, c.name as cat_name 
                  FROM products p
                  LEFT JOIN brands b ON p.brand_id = b.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE (p.product_image IS NULL OR p.product_image = '' OR p.product_image = 'default_product.jpg')
                  LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'batch' => $res]);
        exit;
    }

    if ($action === 'process_batch_item') {
        $pid = (int)$_POST['product_id'];
        $pname = trim($_POST['product_name']);
        $brand = trim($_POST['brand_name']);
        $cat = trim($_POST['cat_name']);
        
        $search_query = trim("$brand $pname $cat motorcycle spare part");
        
        // 1. Search
        $url = "https://html.duckduckgo.com/html/?q=" . urlencode($search_query);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $html = curl_exec($ch);
        curl_close($ch);
        
        $candidates = [];
        if ($html) {
            preg_match_all('/<img[^>]+src="([^">]+)"/', $html, $matches);
            if (!empty($matches[1])) {
                foreach (array_slice($matches[1], 0, 15) as $img_url) {
                    if (strpos($img_url, 'http') === 0 && strpos($img_url, 'duckduckgo') === false) {
                        $score = score_image_candidate($img_url, $brand, $pname, $cat);
                        $candidates[] = ['url' => $img_url, 'score' => $score];
                    }
                }
            }
        }
        
        if (empty($candidates)) {
            log_image_import($pid, $pname, $search_query, '', '', 'FAILED', 'NO_RESULTS', '', 'No image candidates found');
            echo json_encode(['status' => 'failed', 'message' => 'No candidates found']);
            exit;
        }

        // Sort by score DESC
        usort($candidates, function($a, $b) { return $b['score'] <=> $a['score']; });
        $best = $candidates[0];
        $domain = parse_url($best['url'], PHP_URL_HOST);

        if ($best['score'] >= 60) {
            // Auto download
            $res = process_image_download($best['url'], $pid, $pname);
            if ($res['success']) {
                log_image_import($pid, $pname, $search_query, $best['url'], $domain, 'SUCCESS', 'VALID', $res['filename'], '');
                echo json_encode(['status' => 'success', 'message' => 'Downloaded automatically']);
            } else {
                log_image_import($pid, $pname, $search_query, $best['url'], $domain, 'FAILED', 'INVALID', '', $res['error']);
                echo json_encode(['status' => 'failed', 'message' => $res['error']]);
            }
        } else {
            // Needs Review
            log_image_import($pid, $pname, $search_query, $best['url'], $domain, 'SKIPPED', 'NEEDS_REVIEW', '', "Confidence too low ({$best['score']})");
            echo json_encode(['status' => 'review', 'message' => 'Needs manual review']);
        }
        exit;
    }

    if ($action === 'search_images') {
        // Manual search from UI modal
        $query = trim($_POST['query'] ?? '');
        $url = "https://html.duckduckgo.com/html/?q=" . urlencode($query . " motorcycle spare part");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $html = curl_exec($ch);
        curl_close($ch);
        
        $results = [];
        if ($html) {
            preg_match_all('/<img[^>]+src="([^">]+)"/', $html, $matches);
            if (!empty($matches[1])) {
                foreach (array_slice($matches[1], 0, 10) as $img_url) {
                    if (strpos($img_url, 'http') === 0 && strpos($img_url, 'duckduckgo') === false) {
                        $domain = parse_url($img_url, PHP_URL_HOST);
                        $results[] = ['url' => $img_url, 'domain' => $domain, 'preview' => $img_url];
                    }
                }
            }
        }
        echo json_encode(['success' => true, 'results' => $results]);
        exit;
    }

    if ($action === 'download_image') {
        $img_url = filter_var($_POST['image_url'] ?? '', FILTER_VALIDATE_URL);
        $product_id = (int)$_POST['product_id'];
        $product_name = trim($_POST['product_name']);
        
        $res = process_image_download($img_url, $product_id, $product_name);
        $domain = parse_url($img_url, PHP_URL_HOST);
        
        if ($res['success']) {
            log_image_import($product_id, $product_name, 'MANUAL', $img_url, $domain, 'SUCCESS', 'VALID', $res['filename'], '');
            echo json_encode(['success' => true, 'message' => 'Image saved successfully!', 'new_image_url' => base_url($res['db_path'])]);
        } else {
            log_image_import($product_id, $product_name, 'MANUAL', $img_url, $domain, 'FAILED', 'INVALID', '', $res['error']);
            echo json_encode(['success' => false, 'error' => $res['error']]);
        }
        exit;
    }
}

// --- NORMAL PAGE RENDERING ---
$page_title = "Product Image Manager";
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';

// Handle manual file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_upload'])) {
    $product_id = (int)$_POST['upload_product_id'];
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['product_image']['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);
        
        if (in_array($mime_type, ['image/jpeg', 'image/png', 'image/webp'])) {
            $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $safe_name = "manual_upload_" . $product_id . "_" . time() . "." . $ext;
            if (move_uploaded_file($file_tmp, UPLOAD_PATH . $safe_name)) {
                $db_path = 'uploads/products/' . $safe_name;
                $stmt = $conn->prepare("UPDATE products SET product_image = ? WHERE id = ?");
                $stmt->bind_param("si", $db_path, $product_id);
                $stmt->execute();
                set_flash('success', 'Manual image uploaded successfully.');
                log_image_import($product_id, 'UPLOAD', 'UPLOAD', '', '', 'SUCCESS', 'VALID', $safe_name, '');
            }
        } else {
            set_flash('danger', 'Invalid file format.');
        }
    }
    header("Location: product_images.php");
    exit;
}

// Filters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$where = "1=1";
$params = [];
$types = "";

if ($filter === 'missing') {
    $where .= " AND (p.product_image IS NULL OR p.product_image = '' OR p.product_image = 'default_product.jpg')";
} elseif ($filter === 'existing') {
    $where .= " AND (p.product_image IS NOT NULL AND p.product_image != '' AND p.product_image != 'default_product.jpg')";
} elseif ($filter === 'review') {
    $where .= " AND p.id IN (SELECT product_id FROM product_image_imports WHERE validation_status = 'NEEDS_REVIEW')";
}

if (!empty($search)) {
    $where .= " AND (p.name LIKE ? OR b.name LIKE ? OR c.name LIKE ?)";
    $st = "%$search%";
    $params = [$st, $st, $st];
    $types = "sss";
}

$query = "SELECT p.id, p.name as product_name, p.product_image, p.stock, b.name as brand_name, c.name as cat_name 
          FROM products p
          LEFT JOIN brands b ON p.brand_id = b.id
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE $where ORDER BY p.id DESC LIMIT 200";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<div id="page-content-wrapper">
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container-fluid">
            <button class="btn btn-primary" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <h4 class="ms-3 mb-0">Product Image Manager</h4>
        </div>
    </nav>

    <div class="container-fluid p-4">
        <?php echo display_flash(); ?>

        <!-- Batch Processor UI -->
        <div class="card mb-4 border-primary shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-robot"></i> Automatic Batch Processor</h5>
                <button id="btnStartBatch" class="btn btn-light btn-sm fw-bold">Fetch Missing Images</button>
            </div>
            <div class="card-body" id="batchProgressContainer" style="display:none;">
                <p>Processing missing images automatically in batches of 5...</p>
                <div class="progress mb-3" style="height: 25px;">
                    <div id="batchProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;">0%</div>
                </div>
                <div class="d-flex justify-content-around fw-bold">
                    <span class="text-primary">Processed: <span id="sProcessed">0</span></span>
                    <span class="text-success">Success: <span id="sSuccess">0</span></span>
                    <span class="text-warning">Review: <span id="sReview">0</span></span>
                    <span class="text-danger">Failed: <span id="sFailed">0</span></span>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search product, brand, category..." value="<?php echo e($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="filter" class="form-select">
                            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Products</option>
                            <option value="missing" <?php echo $filter === 'missing' ? 'selected' : ''; ?>>Missing Images</option>
                            <option value="existing" <?php echo $filter === 'existing' ? 'selected' : ''; ?>>Existing Images</option>
                            <option value="review" <?php echo $filter === 'review' ? 'selected' : ''; ?>>Needs Review ⚠️</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                        <a href="product_images.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Product Details</th>
                            <th>Brand / Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                            <?php 
                                $has_image = !empty($p['product_image']) && $p['product_image'] !== 'default_product.jpg';
                                $img_path = $has_image ? base_url($p['product_image']) : 'https://via.placeholder.com/60x60?text=No+Image';
                                $sq = trim($p['brand_name'] . ' ' . $p['product_name'] . ' ' . $p['cat_name']);
                            ?>
                            <tr>
                                <td>
                                    <img src="<?php echo e($img_path); ?>" class="rounded" style="width: 60px; height: 60px; object-fit: cover;" id="img-preview-<?php echo $p['id']; ?>">
                                </td>
                                <td>
                                    <strong><?php echo e($p['product_name']); ?></strong><br>
                                    <small class="text-muted">ID: <?php echo $p['id']; ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo e($p['brand_name']); ?></span>
                                    <span class="badge bg-info text-dark"><?php echo e($p['cat_name']); ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="openSearchModal(<?php echo $p['id']; ?>, '<?php echo e($sq); ?>', '<?php echo e($p['product_name']); ?>')">
                                        <i class="fas fa-search"></i> <?php echo $filter==='review' ? 'Review Candidates' : 'Find Image'; ?>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="openUploadModal(<?php echo $p['id']; ?>)">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($products)): ?>
                            <tr><td colspan="4" class="text-center py-4">No products found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Candidates: <span id="searchProductName" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="searchLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3">Searching real product images...</p>
                </div>
                <div class="row" id="searchResultsContainer"></div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manual Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="upload_product_id" id="uploadProductId">
                <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
            </div>
            <div class="modal-footer">
                <button type="submit" name="manual_upload" value="1" class="btn btn-success">Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentProductId = 0;
let currentProductName = '';

// --- BATCH PROCESSOR LOGIC ---
let batchStats = { processed: 0, success: 0, review: 0, failed: 0 };
let isProcessing = false;

document.getElementById('btnStartBatch').addEventListener('click', function() {
    if(isProcessing) return;
    if(!confirm("Start automated image fetcher? This will run in the background.")) return;
    
    isProcessing = true;
    this.disabled = true;
    this.textContent = 'Processing...';
    document.getElementById('batchProgressContainer').style.display = 'block';
    
    fetchNextBatch();
});

function fetchNextBatch() {
    let fd = new FormData();
    fd.append('ajax_action', 'get_missing_batch');
    fd.append('limit', 5);
    
    fetch('product_images.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.success && data.batch.length > 0) {
            processBatchItems(data.batch, 0);
        } else {
            finishBatch();
        }
    }).catch(err => { finishBatch(); });
}

function processBatchItems(batch, index) {
    if(index >= batch.length) {
        fetchNextBatch(); // get next 5
        return;
    }
    
    let item = batch[index];
    let fd = new FormData();
    fd.append('ajax_action', 'process_batch_item');
    fd.append('product_id', item.id);
    fd.append('product_name', item.product_name);
    fd.append('brand_name', item.brand_name || '');
    fd.append('cat_name', item.cat_name || '');
    
    fetch('product_images.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        batchStats.processed++;
        if(data.status === 'success') batchStats.success++;
        else if(data.status === 'review') batchStats.review++;
        else batchStats.failed++;
        
        updateStatsUI();
        processBatchItems(batch, index + 1); // Next item
    }).catch(err => {
        batchStats.processed++;
        batchStats.failed++;
        updateStatsUI();
        processBatchItems(batch, index + 1);
    });
}

function updateStatsUI() {
    document.getElementById('sProcessed').textContent = batchStats.processed;
    document.getElementById('sSuccess').textContent = batchStats.success;
    document.getElementById('sReview').textContent = batchStats.review;
    document.getElementById('sFailed').textContent = batchStats.failed;
    document.getElementById('batchProgressBar').style.width = '100%';
    document.getElementById('batchProgressBar').textContent = 'Processing...';
}

function finishBatch() {
    isProcessing = false;
    document.getElementById('btnStartBatch').textContent = 'Finished';
    document.getElementById('batchProgressBar').classList.remove('progress-bar-animated');
    document.getElementById('batchProgressBar').textContent = 'Complete';
    alert("Batch processing complete! Refresh page to see changes.");
}

// --- MODALS ---
function openUploadModal(id) {
    document.getElementById('uploadProductId').value = id;
    new bootstrap.Modal(document.getElementById('uploadModal')).show();
}

function openSearchModal(id, query, name) {
    currentProductId = id;
    currentProductName = name;
    document.getElementById('searchProductName').textContent = name;
    document.getElementById('searchLoading').classList.remove('d-none');
    document.getElementById('searchResultsContainer').innerHTML = '';
    
    let modal = new bootstrap.Modal(document.getElementById('searchModal'));
    modal.show();

    let fd = new FormData();
    fd.append('ajax_action', 'search_images');
    fd.append('query', query);

    fetch('product_images.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        document.getElementById('searchLoading').classList.add('d-none');
        if (data.success && data.results.length > 0) {
            let html = '';
            data.results.forEach(img => {
                html += `
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="${img.preview}" class="card-img-top p-2" style="height:200px; object-fit:contain;">
                        <div class="card-body text-center p-2">
                            <small class="text-muted d-block text-truncate mb-2" title="${img.domain}">${img.domain}</small>
                            <button class="btn btn-sm btn-primary w-100" onclick="downloadImage('${img.url}', this)">Select</button>
                        </div>
                    </div>
                </div>`;
            });
            document.getElementById('searchResultsContainer').innerHTML = html;
        }
    });
}

function downloadImage(url, btn) {
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    let fd = new FormData();
    fd.append('ajax_action', 'download_image');
    fd.append('image_url', url);
    fd.append('product_id', currentProductId);
    fd.append('product_name', currentProductName);

    fetch('product_images.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            document.getElementById('img-preview-' + currentProductId).src = data.new_image_url;
            bootstrap.Modal.getInstance(document.getElementById('searchModal')).hide();
        } else {
            alert('Error: ' + data.error);
            btn.innerHTML = 'Select';
            btn.disabled = false;
        }
    });
}
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
