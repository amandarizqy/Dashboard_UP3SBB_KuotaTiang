<?php
/**
 * One-time migration script to normalize kontrak.status values.
 * Run this by visiting http://your-host/migrate_status.php once, then delete the file.
 */
include 'koneksi.php';

$queries = [
    "UPDATE kontrak SET status = 'nonaktif' WHERE status = 'non-aktif';",
    "UPDATE kontrak SET status = 'nonaktif' WHERE status = '' OR status IS NULL;",
];

echo "<h2>Running status normalization</h2>";
foreach ($queries as $q) {
    if (mysqli_query($conn, $q)) {
        $aff = mysqli_affected_rows($conn);
        echo "<p>Query executed: <code>" . htmlspecialchars($q) . "</code> — Affected rows: <strong>$aff</strong></p>";
    } else {
        echo "<p style='color:red;'>Error executing: " . htmlspecialchars($q) . " — " . mysqli_error($conn) . "</p>";
    }
}

echo "<p>Done. Remove or secure this script after running.</p>";

?>
