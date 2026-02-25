<?php
include 'koneksi.php';

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE kontrak SET status = '$status' WHERE id_kontrak = '$id'";
    mysqli_query($conn, $query);
}
?>