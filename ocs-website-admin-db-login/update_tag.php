
<?php
require_once 'db.php';

if (isset($_POST['bid']) && isset($_POST['tag'])) {
    $bid = intval($_POST['bid']);
    $tag = $_POST['tag'];
    
    // Basic validation
    if (in_array($tag, ['In-Progress', 'Delivered', 'Not-Delivered'])) {
        $conn->query("UPDATE TRANSACTION SET TTag = '$tag' WHERE BID = $bid");
        echo "Status updated successfully.";
    } else {
        echo "Invalid status value.";
    }
} else {
    echo "Invalid request.";
}
?>
