<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo "Neautorizat.";
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (
  !$data || 
  !isset($data['layoutName']) || 
  !isset($data['warehouseWidth']) || 
  !isset($data['warehouseHeight']) || 
  !isset($data['cols']) || 
  !isset($data['rows']) || 
  !isset($data['roboti']) || 
  !isset($data['elements'])
) {
  http_response_code(400);
  echo "Date invalide.";
  exit;
}

$userId = $_SESSION['user_id'];
$layoutName = $data['layoutName'];
$width = (int)$data['warehouseWidth'];
$height = (int)$data['warehouseHeight'];
$cols = (int)$data['cols'];
$rows = (int)$data['rows'];
$roboti = (int)$data['roboti'];
$layoutData = json_encode($data['elements'], JSON_UNESCAPED_UNICODE);

$stmt = $conn->prepare("SELECT id FROM layouts WHERE user_id = ? AND layout_name = ?");
$stmt->bind_param("is", $userId, $layoutName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $stmt->close();

  $update = $conn->prepare("UPDATE layouts SET warehouse_width=?, warehouse_height=?, cols=?, `rows`=?, roboti=?, layout_data=? WHERE id=?");
  $update->bind_param("iiiiisi", $width, $height, $cols, $rows, $roboti, $layoutData, $row['id']);
  if ($update->execute()) {
    echo "Layout actualizat cu succes.";
  } else {
    echo "Eroare actualizare: " . $update->error;
  }
  $update->close();
} else {
  $stmt->close();

  $insert = $conn->prepare("INSERT INTO layouts (user_id, layout_name, warehouse_width, warehouse_height, cols, `rows`, roboti, layout_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $insert->bind_param("isiiiiss", $userId, $layoutName, $width, $height, $cols, $rows, $roboti, $layoutData);
  if ($insert->execute()) {
    echo "Layout salvat cu succes.";
  } else {
    echo "Eroare salvare: " . $insert->error;
  }
  $insert->close();
}

$conn->close();
