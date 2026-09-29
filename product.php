<?php
// product.php - Product Detail Page
require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: ' . base_url('shop.php'));
    exit();
}

// Fetch Product Details
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name, b.slug as brand_slug 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          JOIN brands b ON p.brand_id = b.id 
          WHERE p.slug = ? AND p.status = 1 LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $slug);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: ' . base_url('404.php'));
    exit();
}

$page_title = $product['name'] . " - Bike Store";
require_once __DIR__ . '/includes/header.php';

// Decode gallery images
$gallery = !empty($product['gallery']) ? json_decode($product['gallery'], true) : [];
if (empty($gallery)) {
    $gallery = [$product['image']];
}

// Fetch Compatible Models
$comp_stmt = $conn->prepare("SELECT m.name, b.name as brand_name FROM product_compatibility pc JOIN models m ON pc.model_id = m.id JOIN brands b ON m.brand_id = b.id WHERE pc.product_id = ? ORDER BY b.name ASC, m.name ASC");
$comp_stmt->bind_param("i", $product['id']);
$comp_stmt->execute();
$compatible_models = $comp_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Related Products from same category
$rel_stmt = $conn->prepare("SELECT p.*, b.name as brand_name FROM products p JOIN brands b ON p.brand_id = b.id WHERE p.category_id = ? AND p.id != ? AND p.status = 1 LIMIT 4");
$rel_stmt->bind_param("ii", $product['category_id'], $product['id']);
$rel_stmt->execute();
$related_products = $rel_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container my-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb p-3 rounded-3 shadow-sm" style="background-color: var(--dark-elevated);">
            <li class="breadcrumb-item"><a href="<?php echo base_url('index.php'); ?>" class="text-decoration-none text-muted"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo base_url('shop.php'); ?>" class="text-decoration-none text-muted">Shop</a></li>
            <li class="breadcrumb-item"><a href="<?php echo base_url('shop.php?category=' . $product['category_slug']); ?>" class="text-decoration-none text-muted"><?php echo e($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page"><?php echo e($product['name']); ?></li>
        </ol>
    </nav>

    <!-- Product Details Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 text-light" style="background-color: var(--dark-surface);">
        <div class="row g-4">
            <!-- Product Gallery / 3D Viewer Column -->
            <div class="col-lg-7">
                <div class="rounded-3 position-relative overflow-hidden mb-3" style="background-color: #0b0f19; min-height: 500px; border: 1px solid #1a2235;">
                    
                    <?php if (!empty($product['product_3d_model'])): ?>
                        <!-- 360 Badge -->
                        <div class="position-absolute top-0 start-0 m-3 z-3">
                            <span class="text-white d-flex align-items-center gap-2"><i class="fas fa-sync fa-spin"></i> 360°</span>
                        </div>
                        
                        <div id="threejs-container" class="w-100 h-100 position-absolute top-0 left-0">
                            <!-- Loading Indicator -->
                            <div id="threejs-loader" class="position-absolute top-50 start-50 translate-middle text-center z-3">
                                <div class="spinner-border text-danger mb-2" role="status"></div>
                                <div class="small text-muted fw-bold">Loading 3D Model...</div>
                                <div class="progress mt-2 mx-auto" style="height: 6px; width: 100px;">
                                    <div id="threejs-progress" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- UI Controls Vertical Right -->
                        <div class="position-absolute top-50 end-0 translate-middle-y me-3 z-3 d-flex flex-column gap-2">
                            <button class="btn btn-dark border-secondary rounded-3 p-2 text-center" style="width: 60px; height: 60px; font-size: 0.7rem;" title="360 View">
                                <i class="fas fa-cube fs-5 d-block mb-1 text-danger"></i> 360&deg; View
                            </button>
                            <button class="btn btn-dark border-secondary rounded-3 p-2 text-center" id="btn-zoom-in" style="width: 60px; height: 60px; font-size: 0.7rem;" title="Zoom In">
                                <i class="fas fa-search-plus fs-5 d-block mb-1"></i> Zoom In
                            </button>
                            <button class="btn btn-dark border-secondary rounded-3 p-2 text-center" id="btn-zoom-out" style="width: 60px; height: 60px; font-size: 0.7rem;" title="Zoom Out">
                                <i class="fas fa-search-minus fs-5 d-block mb-1"></i> Zoom Out
                            </button>
                            <button class="btn btn-dark border-secondary rounded-3 p-2 text-center active" id="btn-auto-rotate" style="width: 60px; height: 60px; font-size: 0.7rem;" title="Auto Rotate">
                                <i class="fas fa-sync fs-5 d-block mb-1"></i> Auto Rotate
                            </button>
                            <button class="btn btn-dark border-secondary rounded-3 p-2 text-center" id="btn-fullscreen" style="width: 60px; height: 60px; font-size: 0.7rem;" title="Fullscreen">
                                <i class="fas fa-expand fs-5 d-block mb-1"></i> Fullscreen
                            </button>
                        </div>
                        
                        <!-- Drag to rotate bottom -->
                        <div class="position-absolute bottom-0 start-50 translate-middle-x mb-4 z-3 text-muted small d-flex align-items-center gap-3">
                            <i class="fas fa-long-arrow-alt-left"></i>
                            <div class="border border-secondary rounded p-1"><i class="fas fa-mouse"></i></div>
                            <span style="letter-spacing: 1px; font-size: 0.7rem;" class="text-uppercase fw-bold">DRAG TO ROTATE</span>
                            <i class="fas fa-long-arrow-alt-right"></i>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 w-100 position-absolute top-0 left-0 p-4">
                            <img id="mainProductImg" src="<?php echo base_url('uploads/' . e($product['image'])); ?>" alt="<?php echo e($product['name']); ?>" class="img-fluid" style="max-height: 400px; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Thumbnails below -->
                <?php if (!empty($product['product_3d_model']) || count($gallery) > 1): ?>
                    <div class="d-flex align-items-center justify-content-center gap-3 position-relative">
                        <button class="btn btn-link text-muted p-0"><i class="fas fa-chevron-left fs-4"></i></button>
                        
                        <div class="d-flex gap-2 overflow-hidden" style="max-width: 80%;">
                            <?php if (!empty($product['product_3d_model'])): ?>
                                <div class="rounded p-1 border border-danger" style="background-color: #0b0f19; cursor: pointer; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-cube text-danger fs-3"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php foreach ($gallery as $idx => $img_name): ?>
                                <img src="<?php echo base_url('uploads/' . e($img_name)); ?>" 
                                     data-src="<?php echo base_url('uploads/' . e($img_name)); ?>" 
                                     alt="Thumbnail" 
                                     class="rounded border border-secondary p-1 <?php echo ($idx === 0 && empty($product['product_3d_model'])) ? 'border-danger' : ''; ?>" style="background-color: #0b0f19; width: 80px; height: 80px; object-fit: contain; cursor: pointer;">
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="btn btn-link text-muted p-0"><i class="fas fa-chevron-right fs-4"></i></button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Specs & Action Column -->
            <div class="col-lg-5 p-4 rounded-3 border border-secondary" style="background-color: #121826;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge px-2 py-1 rounded" style="background-color: #1952a8; color: white; font-size: 0.75rem; font-weight: 600;">YAMAHA</span>
                    <span class="badge px-2 py-1 rounded text-muted border border-secondary" style="background-color: transparent; font-size: 0.75rem; font-weight: 600;">R15 V4</span>
                </div>

                <h2 class="fw-bold text-white mb-2"><?php echo e($product['name']); ?></h2>
                
                <div class="mb-3 text-warning">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    <span class="text-muted ms-2 small">(4.8 / 5 from 24 reviews)</span>
                </div>
                
                <div class="text-muted small mb-3">SKU: BS-<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?></div>

                <div class="d-flex align-items-baseline gap-3 mb-3">
                    <span class="fs-2 fw-bold text-danger"><?php echo format_price($product['price']); ?></span>
                    <span class="text-muted text-decoration-line-through fs-5"><?php echo format_price($product['price'] * 1.15); ?></span>
                    <span class="badge bg-success">15% OFF</span>
                </div>

                <p class="text-muted mb-4 small" style="line-height: 1.6;">For <?php echo e($product['category_name']); ?></p>

                <div class="d-flex align-items-center mb-3" style="font-size: 0.85rem;">
                    <div class="text-warning me-2">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="text-white">4.7 <span class="text-muted">(128 Reviews)</span> <span class="text-muted mx-2">|</span> <span class="text-muted">256 Sold</span></span>
                </div>
                
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="fw-bold text-danger" style="font-size: 1.8rem;"><?php echo format_price($product['price']); ?></span>
                    <span class="text-muted text-decoration-line-through fs-6"><?php echo format_price($product['price'] * 1.22); ?></span>
                    <span class="text-danger fw-bold" style="font-size: 0.9rem;">22% OFF</span>
                </div>
                
                <div class="text-muted small mb-4">(Inclusive of all taxes)</div>

                <!-- Stock Status -->
                <div class="d-flex align-items-center gap-3 mb-4 border-bottom border-secondary pb-4" style="font-size: 0.85rem;">
                    <?php if ($product['stock'] > 10): ?>
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                    <?php elseif ($product['stock'] > 0): ?>
                        <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Low Stock</span>
                    <?php else: ?>
                        <span class="text-danger"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
                    <?php endif; ?>
                    <span class="text-muted">|</span>
                    <span class="text-muted">SKU: BS-<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>

                <!-- Highlights / Compatibility -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 text-white">Highlights</h6>
                    <ul class="list-unstyled text-muted small" style="line-height: 2;">
                        <li><i class="fas fa-check text-success me-2"></i> High quality material</li>
                        <li><i class="fas fa-check text-success me-2"></i> Precision cut for perfect fit</li>
                        <li><i class="fas fa-check text-success me-2"></i> Better heat dissipation</li>
                        <li><i class="fas fa-check text-success me-2"></i> Improved performance</li>
                    </ul>
                </div>
                
                <hr class="border-secondary my-4">

                <!-- Add to Cart Form -->
                <?php if ($product['stock'] > 0): ?>
                    <form action="<?php echo base_url('add_to_cart.php'); ?>" method="POST" class="mb-4">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <?php echo csrf_field(); ?>

                        <div class="d-flex align-items-center gap-3 mb-4">
                            <label class="text-muted small me-2">Quantity:</label>
                            <div class="input-group" style="width: 120px; border: 1px solid #2a3441; border-radius: 4px; overflow: hidden;">
                                <button type="button" class="btn btn-dark text-white border-0 py-1 px-3" style="background: #1a2235;">-</button>
                                <input type="number" name="quantity" class="form-control text-center text-white border-0 bg-transparent py-1" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                                <button type="button" class="btn btn-dark text-white border-0 py-1 px-3" style="background: #1a2235;">+</button>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-danger py-2 fw-bold w-50" style="background-color: #e51e25; border-color: #e51e25;">
                                <i class="fas fa-shopping-cart me-2"></i> ADD TO CART
                            </button>
                            <button type="submit" name="buy_now" value="1" class="btn text-white py-2 fw-bold w-50" style="background-color: #0b0f19; border: 1px solid #1a2235;">
                                BUY NOW
                            </button>
                        </div>
                        
                        <div class="d-flex gap-4 mt-4 text-muted" style="font-size: 0.85rem;">
                            <button type="button" class="btn btn-link text-muted text-decoration-none p-0 fw-semibold hover-text-white transition-all"><i class="far fa-heart me-1"></i> Add to Wishlist</button>
                            <button type="button" class="btn btn-link text-muted text-decoration-none p-0 fw-semibold hover-text-white transition-all"><i class="fas fa-exchange-alt me-1"></i> Compare</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-secondary text-center rounded-3 p-3">
                        <i class="fas fa-info-circle me-2"></i> This product is currently unavailable for order.
                    </div>
                <?php endif; ?>

                <div class="pt-4 mt-2 border-top border-secondary">
                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.65rem;">
                        <div class="text-center"><i class="fas fa-truck fs-5 d-block mb-1 text-white"></i> Free Shipping<br>On orders above ₹999</div>
                        <div class="text-center"><i class="fas fa-undo fs-5 d-block mb-1 text-white"></i> 7 Days Return<br>No questions asked</div>
                        <div class="text-center"><i class="fas fa-shield-alt fs-5 d-block mb-1 text-white"></i> Genuine Parts<br>100% authentic</div>
                        <div class="text-center"><i class="fas fa-lock fs-5 d-block mb-1 text-white"></i> Secure Payment<br>100% secure</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Product Information Tabs -->
    <div class="card border-0 rounded-0 mb-5 text-light" style="background-color: #121826; border-top: 1px solid #1a2235 !important; border-bottom: 1px solid #1a2235 !important;">
        <ul class="nav nav-tabs border-bottom border-secondary mb-0 pt-2 px-4 gap-4" id="productTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active bg-transparent text-white border-0 rounded-0 pb-3 fw-bold" style="border-bottom: 2px solid #e51e25 !important; font-size: 0.85rem;" data-bs-toggle="tab" data-bs-target="#tab-desc" type="button" role="tab">DESCRIPTION</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link bg-transparent text-muted border-0 rounded-0 pb-3 fw-bold hover-text-white" style="font-size: 0.85rem;" data-bs-toggle="tab" data-bs-target="#tab-specs" type="button" role="tab">SPECIFICATIONS</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link bg-transparent text-muted border-0 rounded-0 pb-3 fw-bold hover-text-white" style="font-size: 0.85rem;" data-bs-toggle="tab" data-bs-target="#tab-compat" type="button" role="tab">COMPATIBILITY</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link bg-transparent text-muted border-0 rounded-0 pb-3 fw-bold hover-text-white" style="font-size: 0.85rem;" data-bs-toggle="tab" data-bs-target="#tab-reviews" type="button" role="tab">REVIEWS (128)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link bg-transparent text-muted border-0 rounded-0 pb-3 fw-bold hover-text-white" style="font-size: 0.85rem;" data-bs-toggle="tab" data-bs-target="#tab-shipping" type="button" role="tab">SHIPPING & RETURNS</button>
            </li>
        </ul>
        <div class="tab-content px-4 py-4" id="productTabsContent" style="background-color: #0b0f19;">
            <div class="tab-pane fade show active text-muted small" id="tab-desc" role="tabpanel" style="line-height: 1.8;">
                <?php echo nl2br(e($product['description'])); ?>
            </div>
            <div class="tab-pane fade text-muted small" id="tab-specs" role="tabpanel">
                <p>Information not available.</p>
            </div>
            <div class="tab-pane fade text-muted small" id="tab-compat" role="tabpanel">
                <?php if (!empty($compatible_models)): ?>
                    <ul class="list-unstyled mb-0" style="line-height: 2;">
                        <?php foreach($compatible_models as $cm): ?>
                            <li><i class="fas fa-check text-success me-2"></i> <?php echo e($cm['brand_name'] . ' ' . $cm['name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Information not available.</p>
                <?php endif; ?>
            </div>
            <div class="tab-pane fade text-muted small" id="tab-reviews" role="tabpanel">
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
            <div class="tab-pane fade text-muted small" id="tab-shipping" role="tabpanel">
                <ul class="list-unstyled mb-0" style="line-height: 2;">
                    <li><i class="fas fa-truck text-danger me-2"></i> Express delivery within 3-5 business days.</li>
                    <li><i class="fas fa-undo text-danger me-2"></i> 14 days easy returns available for unused parts.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <section class="mt-5">
            <h3 class="fw-bold text-light mb-4">Related Spare Parts</h3>
            <div class="row g-4">
                <?php foreach ($related_products as $rel): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="product-card tilt-card">
                            <div class="card-img-wrap preserve-3d">
                                <img src="<?php echo base_url('uploads/' . e($rel['image'])); ?>" alt="<?php echo e($rel['name']); ?>" class="pop-out">
                            </div>
                            <div class="card-body">
                                <span class="text-muted small text-uppercase fw-medium"><?php echo e($rel['brand_name']); ?></span>
                                <a href="<?php echo base_url('product.php?slug=' . $rel['slug']); ?>" class="product-title mt-1"><?php echo e($rel['name']); ?></a>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="product-price"><?php echo format_price($rel['price']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php if (!empty($product['product_3d_model'])): ?>
<script type="importmap">
  {
    "imports": {
      "three": "https://unpkg.com/three@0.158.0/build/three.module.js",
      "three/addons/": "https://unpkg.com/three@0.158.0/examples/jsm/"
    }
  }
</script>
<script type="module">
  import * as THREE from 'three';
  import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
  import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

  const container = document.getElementById('threejs-container');
  const loaderEl = document.getElementById('threejs-loader');
  const progressBar = document.getElementById('threejs-progress');
  
  if (container) {
      const scene = new THREE.Scene();
      
      const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 100);
      
      const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
      renderer.setSize(container.clientWidth, container.clientHeight);
      renderer.setPixelRatio(window.devicePixelRatio);
      renderer.outputColorSpace = THREE.SRGBColorSpace;
      renderer.toneMapping = THREE.ACESFilmicToneMapping;
      container.appendChild(renderer.domElement);
      
      const controls = new OrbitControls(camera, renderer.domElement);
      controls.enableDamping = true;
      controls.dampingFactor = 0.05;
      
      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      controls.autoRotate = !prefersReducedMotion;
      controls.autoRotateSpeed = 2.0;

      // Lights (Realistic PBR setup)
      const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
      scene.add(ambientLight);
      
      const dirLight = new THREE.DirectionalLight(0xffffff, 1.2);
      dirLight.position.set(5, 10, 7.5);
      scene.add(dirLight);

      const dirLight2 = new THREE.DirectionalLight(0xffffff, 0.8);
      dirLight2.position.set(-5, 5, -7.5);
      scene.add(dirLight2);

      let initialCameraPosition = new THREE.Vector3();
      let initialTargetPosition = new THREE.Vector3();

      const loader = new GLTFLoader();
      const modelUrl = '<?php echo base_url("uploads/3d/" . e($product["product_3d_model"])); ?>';
      
      loader.load(
          modelUrl,
          function (gltf) {
              const model = gltf.scene;
              
              // Center and scale model based on bounding box
              const box = new THREE.Box3().setFromObject(model);
              const center = box.getCenter(new THREE.Vector3());
              const size = box.getSize(new THREE.Vector3());
              
              const maxDim = Math.max(size.x, size.y, size.z);
              const scale = 5 / maxDim;
              model.scale.setScalar(scale);
              
              model.position.sub(center.multiplyScalar(scale));
              
              scene.add(model);
              
              // Fit camera
              camera.position.set(0, 2, 8);
              initialCameraPosition.copy(camera.position);
              initialTargetPosition.copy(controls.target);
              
              loaderEl.style.display = 'none';
          },
          function (xhr) {
              if (xhr.lengthComputable) {
                  const percentComplete = (xhr.loaded / xhr.total) * 100;
                  progressBar.style.width = percentComplete + '%';
              }
          },
          function (error) {
              console.error('An error happened loading the 3D model:', error);
              loaderEl.innerHTML = '<div class="text-danger small fw-bold"><i class="fas fa-exclamation-triangle"></i> Failed to load 3D model</div>';
          }
      );

      function animate() {
          requestAnimationFrame(animate);
          controls.update();
          renderer.render(scene, camera);
      }
      animate();
      
      window.addEventListener('resize', () => {
          if(!document.fullscreenElement) {
              camera.aspect = container.clientWidth / container.clientHeight;
              camera.updateProjectionMatrix();
              renderer.setSize(container.clientWidth, container.clientHeight);
          }
      });

      document.getElementById('btn-auto-rotate').addEventListener('click', function() {
          controls.autoRotate = !controls.autoRotate;
          this.classList.toggle('active');
          this.classList.toggle('btn-outline-light');
          this.classList.toggle('btn-light');
          this.classList.toggle('text-dark');
      });
      
      document.getElementById('btn-reset-cam').addEventListener('click', () => {
          camera.position.copy(initialCameraPosition);
          controls.target.copy(initialTargetPosition);
      });
      
      document.getElementById('btn-zoom-in').addEventListener('click', () => {
          camera.position.z = Math.max(1, camera.position.z - 1);
      });
      
      document.getElementById('btn-zoom-out').addEventListener('click', () => {
          camera.position.z += 1;
      });
      
      document.getElementById('btn-fullscreen').addEventListener('click', () => {
          if (!document.fullscreenElement) {
              container.requestFullscreen().catch(err => {
                  console.error(err);
              });
          } else {
              document.exitFullscreen();
          }
      });
      
      document.addEventListener('fullscreenchange', () => {
          if (document.fullscreenElement) {
              camera.aspect = window.innerWidth / window.innerHeight;
              camera.updateProjectionMatrix();
              renderer.setSize(window.innerWidth, window.innerHeight);
          } else {
              camera.aspect = container.clientWidth / container.clientHeight;
              camera.updateProjectionMatrix();
              renderer.setSize(container.clientWidth, container.clientHeight);
          }
      });
  }
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
