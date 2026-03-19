<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    if ($id > 0) {
        $conn->query("DELETE FROM employees WHERE id=$id");
    }
}

$conn->close();
header("Location: index.php?success=deleted");
exit;