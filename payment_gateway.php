<?php
// payment_gateway.php - Online Payment Gateway Checkout Screen
require_once __DIR__ . '/includes/functions.php';

require_customer();

$customer_id = get_customer_id();
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    set_flash('danger', 'Invalid order specified.');
    header('Location: ' . base_url('my_orders.php'));
    exit();
}

// Fetch Order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ? LIMIT 1");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    set_flash('danger', 'Order not found.');
    header('Location: ' . base_url('my_orders.php'));
    exit();
}

// If already paid, redirect to invoice
if ($order['payment_status'] === 'Paid') {
    set_flash('info', 'This order has already been paid.');
    header('Location: ' . base_url('order_success.php?id=' . $order_id));
    exit();
}

// Fetch Order Items
$items_stmt = $conn->prepare("SELECT oi.*, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Secure Online Payment - Order #" . $order['order_number'];
require_once __DIR__ . '/includes/header.php';
?>

<style>
.gateway-card {
    background: #ffffff;
    border: none;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.payment-nav-link {
    color: #495057;
    font-weight: 600;
    border-radius: 10px !important;
    padding: 12px 18px;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef !important;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.payment-nav-link:hover {
    background-color: #f8f9fa;
    color: #dc3545;
}
.payment-nav-link.active {
    background-color: #dc3545 !important;
    color: #ffffff !important;
    border-color: #dc3545 !important;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}
.bank-option-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.bank-option-card:hover, .bank-option-card.selected {
    border-color: #dc3545;
    background-color: #fff5f5;
}
.upi-app-btn {
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
}
.upi-app-btn:hover, .upi-app-btn.selected {
    border-color: #198754;
    background: #f0fff4;
}
.security-badge {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: #fff;
    border-radius: 12px;
}
.otp-input {
    letter-spacing: 12px;
    font-size: 24px;
    font-weight: 700;
    text-align: center;
}
@keyframes bikeZoom {
    0% { transform: translateX(-150px) rotate(0deg) scale(0.5); opacity: 0; }
    40% { transform: translateX(-20px) rotate(-15deg) scale(1.1); opacity: 1; }
    60% { transform: translateX(20px) rotate(-15deg) scale(1.1); opacity: 1; }
    100% { transform: translateX(150px) rotate(0deg) scale(0.5); opacity: 0; }
}
.bike-anim {
    display: inline-block;
    animation: bikeZoom 1.5s ease-in-out infinite;
    color: var(--primary-color, #dc3545);
}
</style>

<div class="container my-4">
    <!-- Top Security Bar -->
    <div class="security-badge p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2 shadow-sm">
        <div class="d-flex align-items-center">
            <i class="fas fa-lock text-success fs-3 me-3"></i>
            <div>
                <h6 class="mb-0 fw-bold text-white"><i class="fas fa-shield-alt text-success me-1"></i> BikeStore SSL 256-Bit Encrypted Payment Gateway</h6>
                <small class="text-light opacity-75">Your payment transaction is 100% secure and PCI-DSS Compliant</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-dark px-3 py-2 fw-semibold"><i class="fas fa-clock text-danger me-1"></i> Session Expires in: <span id="paymentTimer">09:59</span></span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Order Summary Column -->
        <div class="col-lg-4">
            <div class="gateway-card p-4 mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-receipt text-danger me-2"></i> Order Summary</span>
                    <span class="badge bg-danger fs-6">#<?php echo e($order['order_number']); ?></span>
                </h5>

                <div class="mb-3 pe-1" style="max-height: 220px; overflow-y: auto;">
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                            <img src="<?php echo base_url('uploads/' . e($item['image'])); ?>" alt="img" class="img-thumbnail me-3" style="width: 45px; height: 45px; object-fit: contain;">
                            <div class="flex-grow-1">
                                <h6 class="mb-0 small fw-semibold text-dark"><?php echo e($item['product_name']); ?></h6>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?> × <?php echo format_price($item['price']); ?></small>
                            </div>
                            <span class="fw-bold text-dark small"><?php echo format_price($item['total']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="bg-light p-3 rounded-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Customer Name:</span>
                        <span class="fw-semibold small text-dark"><?php echo e($order['shipping_name']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Phone:</span>
                        <span class="fw-semibold small text-dark"><?php echo e($order['shipping_phone']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Deliver To:</span>
                        <span class="fw-semibold small text-dark text-end ms-2"><?php echo e($order['shipping_city']); ?>, <?php echo e($order['shipping_state']); ?></span>
                    </div>
                </div>

                <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark fs-6">Total Payable Amount:</span>
                        <span class="fw-bold text-danger fs-3"><?php echo format_price($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Trust Badges -->
            <div class="gateway-card p-3 text-center">
                <p class="small text-muted fw-semibold mb-2">GUARANTEED SAFE CHECKOUT</p>
                <div class="d-flex justify-content-center align-items-center gap-3 fs-3 text-muted">
                    <i class="fab fa-cc-visa text-primary" title="Visa"></i>
                    <i class="fab fa-cc-mastercard text-danger" title="MasterCard"></i>
                    <i class="fab fa-cc-amex text-info" title="American Express"></i>
                    <i class="fas fa-university text-secondary" title="Net Banking"></i>
                    <i class="fas fa-qrcode text-dark" title="UPI Apps"></i>
                </div>
            </div>
        </div>

        <!-- Payment Options & Form Column -->
        <div class="col-lg-8">
            <div class="gateway-card p-4">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4">Choose Online Payment Method</h5>

                <div class="row g-3">
                    <!-- Nav Tabs Left Column -->
                    <div class="col-md-4">
                        <div class="nav flex-column nav-pills" id="paymentTab" role="tablist" aria-orientation="vertical">
                            <button class="nav-link payment-nav-link active" id="tab-card-btn" data-bs-toggle="pill" data-bs-target="#tab-card" type="button" role="tab">
                                <span><i class="fas fa-credit-card me-2"></i> Credit / Debit Card</span>
                                <i class="fas fa-chevron-right small"></i>
                            </button>
                            <button class="nav-link payment-nav-link" id="tab-upi-btn" data-bs-toggle="pill" data-bs-target="#tab-upi" type="button" role="tab">
                                <span><i class="fas fa-qrcode me-2"></i> UPI / QR Code</span>
                                <i class="fas fa-chevron-right small"></i>
                            </button>
                            <button class="nav-link payment-nav-link" id="tab-netbanking-btn" data-bs-toggle="pill" data-bs-target="#tab-netbanking" type="button" role="tab">
                                <span><i class="fas fa-university me-2"></i> Net Banking</span>
                                <i class="fas fa-chevron-right small"></i>
                            </button>
                            <button class="nav-link payment-nav-link" id="tab-wallet-btn" data-bs-toggle="pill" data-bs-target="#tab-wallet" type="button" role="tab">
                                <span><i class="fas fa-wallet me-2"></i> Digital Wallets</span>
                                <i class="fas fa-chevron-right small"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab Content Right Column -->
                    <div class="col-md-8">
                        <div class="tab-content border rounded-4 p-4 bg-light bg-opacity-50" id="paymentTabContent">
                            
                            <!-- TAB 1: CREDIT / DEBIT CARD -->
                            <div class="tab-pane fade show active" id="tab-card" role="tabpanel">
                                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-credit-card text-danger me-2"></i> Pay with Credit or Debit Card</h6>
                                <form id="cardPaymentForm" onsubmit="event.preventDefault(); initiateGatewayPayment('Card');">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-secondary small">Card Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0" id="cardBrandIcon"><i class="fas fa-credit-card text-muted"></i></span>
                                            <input type="text" id="cardNumber" class="form-control border-start-0 ps-0" placeholder="4532 •••• •••• 8892" maxlength="19" required autocomplete="off">
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">Supported: Visa, MasterCard, RuPay, Maestro, Amex</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-secondary small">Cardholder Name</label>
                                        <input type="text" id="cardName" class="form-control" placeholder="Name as printed on card" value="<?php echo e($order['shipping_name']); ?>" required>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold text-secondary small">Expiry Date</label>
                                            <input type="text" id="cardExpiry" class="form-control" placeholder="MM / YY" maxlength="5" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold text-secondary small">CVV / CVC</label>
                                            <div class="input-group">
                                                <input type="password" id="cardCvv" class="form-control" placeholder="•••" maxlength="4" required>
                                                <span class="input-group-text bg-white" title="3 or 4 digits on back of card"><i class="fas fa-question-circle text-muted"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" id="saveCard" checked>
                                        <label class="form-check-label text-muted small" for="saveCard">
                                            Securely save card details for 1-click future payment
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill fw-bold py-3 shadow-sm">
                                        <i class="fas fa-lock me-2"></i> Pay <?php echo format_price($order['total_amount']); ?> Now
                                    </button>
                                </form>
                            </div>

                            <!-- TAB 2: UPI / QR CODE -->
                            <div class="tab-pane fade" id="tab-upi" role="tabpanel">
                                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-qrcode text-success me-2"></i> Pay using Official UPI / Scan QR</h6>
                                
                                <!-- Custom Dynamic QR Code Display -->
                                <div class="text-center bg-white p-3 border rounded-3 mb-3 shadow-xs">
                                    <p class="small fw-bold text-dark mb-1"><i class="fas fa-user-check text-primary me-1"></i> Payee: Grish</p>
                                    <div class="d-inline-block p-2 border border-2 border-success rounded-3 bg-light shadow-sm">
                                        <?php 
                                            $upi_id = "grishamudavanan-2@okicici";
                                            $payee_name = "BikeStore";
                                            $amount = number_format($order['total_amount'], 2, '.', '');
                                            $upi_string = "upi://pay?pa={$upi_id}&pn={$payee_name}&am={$amount}&cu=INR";
                                            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($upi_string);
                                        ?>
                                        <img src="<?php echo $qr_url; ?>" alt="Dynamic UPI QR Code" class="img-fluid rounded" style="max-width: 220px; height: auto;">
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-light text-dark border px-3 py-2 font-monospace fs-6">
                                            <i class="fas fa-at text-success me-1"></i> grishamudavanan-2@okicici
                                        </span>
                                    </div>
                                    <p class="small text-muted mt-2 mb-0"><i class="fas fa-shield-alt text-success me-1"></i> Scan to pay with any UPI App (GPay, PhonePe, Paytm, BHIM)</p>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="upi-app-btn selected" onclick="selectUpiApp('okicici')">
                                            <i class="fab fa-google text-primary fs-4 mb-1"></i>
                                            <div class="fw-semibold small">Google Pay</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="upi-app-btn" onclick="selectUpiApp('phonepe')">
                                            <i class="fas fa-mobile-alt text-purple fs-4 mb-1" style="color: #5f259f;"></i>
                                            <div class="fw-semibold small">PhonePe</div>
                                        </div>
                                    </div>
                                </div>

                                <form id="upiPaymentForm" onsubmit="event.preventDefault(); initiateGatewayPayment('UPI');">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-secondary small">Virtual Payment Address (VPA) / UPI ID</label>
                                        <div class="input-group">
                                            <input type="text" id="upiId" class="form-control fw-semibold" placeholder="grishamudavanan-2@okicici" value="grishamudavanan-2@okicici" required>
                                            <button class="btn btn-outline-success fw-bold" type="button" onclick="verifyUpiId()"><i class="fas fa-check me-1"></i> Verified</button>
                                        </div>
                                        <div id="upiVerifyNotice" class="small text-success mt-1"><i class="fas fa-check-circle me-1"></i> UPI ID Verified: Grish (ICICI Bank)</div>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold py-3 shadow-sm">
                                        <i class="fas fa-paper-plane me-2"></i> Pay <?php echo format_price($order['total_amount']); ?> via UPI
                                    </button>
                                </form>
                            </div>
                                        <i class="fas fa-paper-plane me-2"></i> Pay <?php echo format_price($order['total_amount']); ?> via UPI
                                    </button>
                                </form>
                            </div>

                            <!-- TAB 3: NET BANKING -->
                            <div class="tab-pane fade" id="tab-netbanking" role="tabpanel">
                                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-university text-primary me-2"></i> Pay via Net Banking</h6>
                                <form id="netbankingForm" onsubmit="event.preventDefault(); initiateGatewayPayment('NetBanking');">
                                    <p class="small text-muted mb-2 fw-semibold">Popular Banks</p>
                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <div class="bank-option-card selected" onclick="selectBank('HDFC Bank', this)">
                                                <i class="fas fa-building text-primary fs-4 mb-1"></i>
                                                <div class="small fw-bold">HDFC</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bank-option-card" onclick="selectBank('ICICI Bank', this)">
                                                <i class="fas fa-landmark text-danger fs-4 mb-1"></i>
                                                <div class="small fw-bold">ICICI</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bank-option-card" onclick="selectBank('State Bank of India', this)">
                                                <i class="fas fa-university text-info fs-4 mb-1"></i>
                                                <div class="small fw-bold">SBI</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-secondary small">Or Select Other Bank</label>
                                        <select id="otherBankSelect" class="form-select" onchange="document.getElementById('selectedBankName').value = this.value;">
                                            <option value="HDFC Bank">HDFC Bank</option>
                                            <option value="ICICI Bank">ICICI Bank</option>
                                            <option value="State Bank of India">State Bank of India</option>
                                            <option value="Axis Bank">Axis Bank</option>
                                            <option value="Kotak Mahindra Bank">Kotak Mahindra Bank</option>
                                            <option value="Punjab National Bank">Punjab National Bank</option>
                                            <option value="Bank of Baroda">Bank of Baroda</option>
                                            <option value="IDFC FIRST Bank">IDFC FIRST Bank</option>
                                        </select>
                                        <input type="hidden" id="selectedBankName" value="HDFC Bank">
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold py-3 shadow-sm">
                                        <i class="fas fa-external-link-alt me-2"></i> Proceed to Bank Portal
                                    </button>
                                </form>
                            </div>

                            <!-- TAB 4: DIGITAL WALLETS -->
                            <div class="tab-pane fade" id="tab-wallet" role="tabpanel">
                                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-wallet text-warning me-2"></i> Pay using Digital Wallet</h6>
                                <form id="walletForm" onsubmit="event.preventDefault(); initiateGatewayPayment('Wallet');">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-secondary small">Choose Wallet Service</label>
                                        <select id="walletSelect" class="form-select">
                                            <option value="Paytm Wallet">Paytm Wallet</option>
                                            <option value="PhonePe Wallet">PhonePe Wallet</option>
                                            <option value="Amazon Pay Balance">Amazon Pay</option>
                                            <option value="Mobikwik Wallet">Mobikwik</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-secondary small">Registered Mobile Number</label>
                                        <input type="text" id="walletMobile" class="form-control" value="<?php echo e($order['shipping_phone']); ?>" placeholder="10 digit mobile number" required>
                                    </div>

                                    <button type="submit" class="btn btn-warning text-dark btn-lg w-100 rounded-pill fw-bold py-3 shadow-sm">
                                        <i class="fas fa-check-circle me-2"></i> Link & Pay <?php echo format_price($order['total_amount']); ?>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Simulation Helper Notice -->
                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 text-muted small">
                    <div>
                        <i class="fas fa-vial text-warning me-1"></i> <strong>Testing Payment Gateway Mode:</strong> Use any dummy card details or OTP <code>123456</code>.
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="simulateFailedPayment()">
                            <i class="fas fa-times-circle me-1"></i> Test Card Failure
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- 3D SECURE OTP / AUTHORIZATION MODAL -->
<div class="modal fade" id="paymentProcessingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6" id="modalHeaderTitle">
                    <i class="fas fa-shield-alt text-success me-2"></i> 3D Secure Authorization
                </h5>
                <span class="badge bg-success">256-Bit SSL</span>
            </div>
            <div class="modal-body p-4 text-center">
                <!-- Step 1: Processing Loader -->
                <div id="modalStepLoader" class="py-4">
                    <div class="spinner-border text-danger mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Connecting to Payment Gateway...</h5>
                    <p class="text-muted small">Please do not refresh or close this window.</p>
                </div>

                <!-- Step 2: OTP Verification Form -->
                <div id="modalStepOtp" class="d-none py-2">
                    <div class="mb-3">
                        <div class="d-inline-flex p-3 rounded-circle bg-danger bg-opacity-10 text-danger mb-2">
                            <i class="fas fa-key fs-2"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Enter Bank OTP</h5>
                        <p class="text-muted small">An authentication code has been sent to your registered mobile number ending in <strong>•••• <?php echo substr($order['shipping_phone'], -4); ?></strong>.</p>
                    </div>

                    <div class="mb-3">
                        <input type="text" id="otpCodeInput" class="form-control form-control-lg otp-input" placeholder="••••••" maxlength="6" value="123456" autocomplete="off">
                        <small class="text-muted d-block mt-2">Default Demo OTP: <span class="badge bg-secondary">123456</span></small>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-danger btn-lg rounded-pill fw-bold" onclick="submitGatewayOtp('success')">
                            <i class="fas fa-lock me-1"></i> Confirm & Authorize Payment
                        </button>
                        <button type="button" class="btn btn-light rounded-pill text-muted small" onclick="submitGatewayOtp('fail')">
                            Simulate Incorrect OTP / Bank Reject
                        </button>
                    </div>
                </div>

                <!-- Step 3: Success Screen -->
                <div id="modalStepSuccess" class="d-none py-4">
                    <div class="mb-3 position-relative" style="height: 80px; overflow: hidden;">
                        <i class="fas fa-motorcycle bike-anim" style="font-size: 4rem; position: absolute; left: 40%; top: 10px;"></i>
                    </div>
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Payment Successful!</h4>
                    <p class="text-muted small mb-2">Ref ID: <span id="successTxnId" class="fw-bold text-dark"></span></p>
                    <p class="text-muted small">Redirecting to order summary invoice...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 justify-content-center py-2">
                <small class="text-muted"><i class="fas fa-lock text-success me-1"></i> Verified by Visa | Mastercard ID Check | RuPay Secure</small>
            </div>
        </div>
    </div>
</div>

<script>
let currentSelectedMethod = 'Card';
let activeOrderTotal = <?php echo (float)$order['total_amount']; ?>;
let activeOrderId = <?php echo (int)$order['id']; ?>;
let csrfToken = "<?php echo generate_csrf_token(); ?>";

// Format Card Number & Detect Brand
document.getElementById('cardNumber').addEventListener('input', function(e) {
    let val = e.target.value.replace(/\D/g, '');
    let formatted = '';
    for (let i = 0; i < val.length; i++) {
        if (i > 0 && i % 4 === 0) formatted += ' ';
        formatted += val[i];
    }
    e.target.value = formatted;

    // Detect Icon
    let brandIcon = document.getElementById('cardBrandIcon');
    if (val.startsWith('4')) {
        brandIcon.innerHTML = '<i class="fab fa-cc-visa text-primary fs-5"></i>';
    } else if (val.startsWith('5') || val.startsWith('2')) {
        brandIcon.innerHTML = '<i class="fab fa-cc-mastercard text-danger fs-5"></i>';
    } else if (val.startsWith('3')) {
        brandIcon.innerHTML = '<i class="fab fa-cc-amex text-info fs-5"></i>';
    } else if (val.startsWith('6')) {
        brandIcon.innerHTML = '<i class="fas fa-credit-card text-success fs-5"></i>'; // RuPay
    } else {
        brandIcon.innerHTML = '<i class="fas fa-credit-card text-muted"></i>';
    }
});

// Format Expiry MM/YY
document.getElementById('cardExpiry').addEventListener('input', function(e) {
    let val = e.target.value.replace(/\D/g, '');
    if (val.length >= 2) {
        e.target.value = val.substring(0, 2) + '/' + val.substring(2, 4);
    } else {
        e.target.value = val;
    }
});

function selectBank(bankName, el) {
    document.querySelectorAll('.bank-option-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selectedBankName').value = bankName;
    document.getElementById('otherBankSelect').value = bankName;
}

function selectUpiApp(appName) {
    document.getElementById('upiId').value = 'user@' + appName;
    verifyUpiId();
}

function verifyUpiId() {
    let upiVal = document.getElementById('upiId').value.trim();
    if (upiVal.includes('@')) {
        document.getElementById('upiVerifyNotice').classList.remove('d-none');
    } else {
        alert('Please enter a valid UPI ID (e.g. name@upi)');
    }
}

// Payment Timer Countdown
let timerDuration = 599; // 9:59
let timerInterval = setInterval(function() {
    let minutes = Math.floor(timerDuration / 60);
    let seconds = timerDuration % 60;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;
    document.getElementById('paymentTimer').textContent = minutes + ':' + seconds;
    if (--timerDuration < 0) {
        clearInterval(timerInterval);
        alert('Payment session expired. Please retry.');
        window.location.href = "<?php echo base_url('my_orders.php'); ?>";
    }
}, 1000);

// Initiate Payment Process
function initiateGatewayPayment(method) {
    currentSelectedMethod = method;
    let modalEl = new bootstrap.Modal(document.getElementById('paymentProcessingModal'));
    
    // Reset Modal Steps
    document.getElementById('modalStepLoader').classList.remove('d-none');
    document.getElementById('modalStepOtp').classList.add('d-none');
    document.getElementById('modalStepSuccess').classList.add('d-none');
    
    modalEl.show();

    // Simulate 1.5 second bank connection then show OTP
    setTimeout(function() {
        document.getElementById('modalStepLoader').classList.add('d-none');
        document.getElementById('modalStepOtp').classList.remove('d-none');
    }, 1200);
}

function submitGatewayOtp(action) {
    let otpVal = document.getElementById('otpCodeInput').value.trim();
    if (action === 'success' && (otpVal.length !== 6 || isNaN(otpVal))) {
        alert('Please enter a valid 6-digit OTP code.');
        return;
    }

    // Show processing spinner
    document.getElementById('modalStepOtp').classList.add('d-none');
    document.getElementById('modalStepLoader').classList.remove('d-none');

    // Build Payload
    let cardNum = document.getElementById('cardNumber').value.replace(/\s/g, '');
    let cardLast4 = cardNum.length >= 4 ? cardNum.substring(cardNum.length - 4) : '8892';
    let upiId = document.getElementById('upiId').value;
    let bankName = document.getElementById('selectedBankName').value;
    let walletName = document.getElementById('walletSelect').value;

    let formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('order_id', activeOrderId);
    formData.append('method', currentSelectedMethod);
    formData.append('action', action);
    formData.append('card_last4', cardLast4);
    formData.append('card_brand', 'Visa');
    formData.append('upi_id', upiId);
    formData.append('bank_name', bankName);
    formData.append('wallet_name', walletName);

    fetch('<?php echo base_url("process_online_payment.php"); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('modalStepLoader').classList.add('d-none');
            document.getElementById('modalStepSuccess').classList.remove('d-none');
            document.getElementById('successTxnId').textContent = data.transaction_id || 'TXN-OK';

            setTimeout(function() {
                window.location.href = data.redirect_url;
            }, 2500); // Increased time slightly to let animation play
        } else {
            let modalObj = bootstrap.Modal.getInstance(document.getElementById('paymentProcessingModal'));
            if (modalObj) modalObj.hide();

            Swal.fire({
                icon: 'error',
                title: 'Payment Failed',
                text: data.message || 'Transaction declined by issuer bank.',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(err => {
        let modalObj = bootstrap.Modal.getInstance(document.getElementById('paymentProcessingModal'));
        if (modalObj) modalObj.hide();

        Swal.fire({
            icon: 'error',
            title: 'Gateway Error',
            text: 'Unable to communicate with payment processor.',
            confirmButtonColor: '#dc3545'
        });
    });
}

function simulateFailedPayment() {
    initiateGatewayPayment('Card');
    setTimeout(function() {
        submitGatewayOtp('fail');
    }, 1500);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
