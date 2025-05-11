<?php
$mysqli = new mysqli("localhost", "root", "", "agrihire");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = strtolower(trim($_POST["message"]));

    // Prepare the SQL query correctly
    $query = $mysqli->prepare("SELECT answer FROM chatbot_responses WHERE LOWER(question) = LOWER(?) ORDER BY RAND() LIMIT 1");
    $query->bind_param("s", $user_input);
    $query->execute();
    $query->bind_result($response);
    
    if ($query->fetch()) {
        echo $response;
    } else {
        echo "I'm not sure. Please contact support!";
    }
}
?>
