<?php
require_once '../config.php';

$dept = isset($_GET['dept']) ? $_GET['dept'] : '';
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

$conn = getDBConnection();

// Build query
$query = "SELECT id, name, email, department, status FROM members";
if ($dept) {
    $query .= " WHERE department LIKE '%$dept%'";
}

$result = $conn->query($query);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

if ($format === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="members_export.json"');
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="members_export.csv"');

    $output = fopen('php://output', 'w');

    // CSV Header
    fputcsv($output, ['ID', 'Name', 'Email', 'Department', 'Status']);

    // CSV Data
    foreach ($data as $row) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['department'],
            $row['status']
        ]);
    }

    fclose($output);
}
?>
