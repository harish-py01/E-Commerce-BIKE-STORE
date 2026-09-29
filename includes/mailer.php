<?php
// includes/mailer.php
require_once __DIR__ . '/smtp_config.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends an order confirmation email to the customer
 * 
 * @param int $order_id The ID of the order
 * @param string $customer_email The customer's email address
 * @param string $customer_name The customer's full name
 * @param string $order_number The generated order number
 * @param float $total_amount The total amount of the order
 * @return bool True if email sent successfully, false otherwise
 */
function send_order_confirmation($order_id, $customer_email, $customer_name, $order_number, $total_amount) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        if (SMTP_ENCRYPTION === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (SMTP_ENCRYPTION === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($customer_email, $customer_name);
        $mail->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Order Confirmation - #" . $order_number;
        
        $body = "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: #dc3545; color: #fff; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>Thank You for Your Order!</h2>
            </div>
            <div style='padding: 20px;'>
                <p>Hi <strong>{$customer_name}</strong>,</p>
                <p>We've received your order and it is currently being processed. Here are the details:</p>
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                    <p style='margin: 5px 0;'><strong>Order Number:</strong> {$order_number}</p>
                    <p style='margin: 5px 0;'><strong>Order Total:</strong> ₹" . number_format($total_amount, 2) . "</p>
                </div>
                <p>You can track your order status by logging into your account and visiting the <a href='http://localhost/Bike_store-main/my_orders.php' style='color: #dc3545; text-decoration: none;'>My Orders</a> page.</p>
                <p>If you have any questions, feel free to reply to this email.</p>
                <br>
                <p>Best Regards,</p>
                <p><strong>Bike Store Team</strong></p>
            </div>
            <div style='background-color: #f1f1f1; color: #666; text-align: center; padding: 10px; font-size: 12px;'>
                &copy; " . date('Y') . " Bike Spare Parts E-Commerce Store. All Rights Reserved.
            </div>
        </div>
        ";
        
        $mail->Body = $body;
        $mail->AltBody = "Hi {$customer_name},\n\nThank you for your order!\nOrder Number: {$order_number}\nTotal Amount: ₹" . number_format($total_amount, 2) . "\n\nBest Regards,\nBike Store Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error in real world, but for now just return false
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
