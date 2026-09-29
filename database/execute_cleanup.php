<?php
require_once __DIR__ . '/../includes/db.php';

echo "<h2>Starting Final Cleanup</h2>";

// Delete the specific orphaned model identified in the plan
$model_name = 'CB350';
$stmt = $conn->prepare("DELETE FROM models WHERE name = ?");
$stmt->bind_param("s", $model_name);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Successfully removed orphaned model: " . htmlspecialchars($model_name) . "<br>";
} else {
    echo "Model " . htmlspecialchars($model_name) . " not found or already removed.<br>";
}

echo "<strong>Note:</strong> Empty categories (Controls, Drivetrain, Transmission) were preserved as they are structural parent categories, perfectly in accordance with the approved plan.<br>";

echo "<h2>Cleanup Complete</h2>";
?>
