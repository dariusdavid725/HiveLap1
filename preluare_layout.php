<?php
include "../db.php";

$layout_id = 16; // Poți seta dinamic sau hardcodat
$sql = "SELECT layout_data, cols, rows FROM layouts WHERE id = $layout_id LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "layout" => json_decode($row["layout_data"]),
        "cols" => (int)$row["cols"],
        "rows" => (int)$row["rows"]
    ]);
} else {
    echo json_encode(["error" => "Layout inexistent"]);
}

$conn->close();
?>
