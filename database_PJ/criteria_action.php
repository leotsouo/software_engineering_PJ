<?php
require_once 'db_connection.php';

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';

switch ($action) {
    case 'add':
        if ($name) {
            $stmt = $pdo->prepare("INSERT INTO criteria (name) VALUES (:name)");
            $stmt->execute(['name' => $name]);
        }
        break;

    case 'edit':
        if ($id && $name) {
            $stmt = $pdo->prepare("UPDATE criteria SET name = :name WHERE id = :id");
            $stmt->execute(['name' => $name, 'id' => $id]);
        }
        break;

    case 'delete':
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM criteria WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
        break;
}
