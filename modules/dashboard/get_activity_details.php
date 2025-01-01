<?php
session_start();
include '../../config/db.php';

if (isset($_GET['id'])) {
    $log_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT users.fullname, activity_log.activity, activity_log.activity_description, activity_log.created_at 
                           FROM activity_log 
                           JOIN users ON activity_log.user_id = users.id 
                           WHERE activity_log.id = ?");
    $stmt->execute([$log_id]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($activity);
}
?>
