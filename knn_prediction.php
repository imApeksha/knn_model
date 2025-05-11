<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_query = $_POST['search_query'] ?? '';
    error_log("Search Query: " . $search_query);

    function get_knn_prediction($search_query) {
        $data_json = json_encode($search_query);
        $command = escapeshellcmd("py knn_model.py " . escapeshellarg($search_query));
        error_log("Executing command: " . $command);

        $output = shell_exec($command);
        if (!$output) {
            error_log("Python script execution failed.");
            return null;
        }

        $decoded_output = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            return null;
        }

        return $decoded_output;
    }

    $predictions = get_knn_prediction($search_query);
    if (!$predictions || !is_array($predictions)) {
        echo "KNN model did not return any predictions.";
        exit;
    }

    function get_worker_details($worker_ids) {
        $conn = new mysqli("localhost", "root", "", "agrihire");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $worker_details = [];
        $stmt = $conn->prepare("SELECT worker_id, name, city_id, state_id, status, work_profile, contactno FROM worker WHERE worker_id = ?");
        
        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        foreach ($worker_ids as $worker) {
            if (!isset($worker['ID'])) {
                error_log("Missing 'ID' key in worker array.");
                continue;
            }

            $worker_id = $worker['ID'];
            $stmt->bind_param("i", $worker_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $worker_details[] = $row;
                }
            } else {
                error_log("No details found for Worker ID: $worker_id");
            }
        }

        $stmt->close();
        $conn->close();
        return $worker_details;
    }

    $worker_details = get_worker_details($predictions);
    if (!empty($worker_details)) {
        echo "<!DOCTYPE html>
<html>
<head>
    <title>Worker Results</title>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 280px;
            padding: 20px;
            transition: transform 0.2s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h3 {
            color: #4CAF50;
            margin-top: 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            text-align: center;
        }
        .card p {
            margin: 10px 0;
            color: #555;
            font-size: 14px;
        }
        .card p span {
            font-weight: bold;
            color: #333;
        }
        .btn-back {
            display: block;
            width: 150px;
            margin: 30px auto 0;
            padding: 10px;
            text-align: center;
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-back:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h2>Nearest Workers for '" . htmlspecialchars($search_query) . "'</h2>
        <div class='cards'>";
        foreach ($worker_details as $worker) {
            echo "<div class='card'>";
            echo "<h3>" . htmlspecialchars($worker['name']) . "</h3>";
            echo "<p><span>Worker ID:</span> " . htmlspecialchars($worker['worker_id']) . "</p>";
            echo "<p><span>City ID:</span> " . htmlspecialchars($worker['city_id']) . "</p>";
            echo "<p><span>State ID:</span> " . htmlspecialchars($worker['state_id']) . "</p>";
            echo "<p><span>Status:</span> " . htmlspecialchars($worker['status']) . "</p>";
            echo "<p><span>Work Profile:</span> " . htmlspecialchars($worker['work_profile']) . "</p>";
            echo "<p><span>Contact Number:</span> " . htmlspecialchars($worker['contactno']) . "</p>";
            echo "</div>";
        }
        echo "    </div>
        <a class='btn-back' href='http://localhost/knn/'>Go Back</a>
    </div>
</body>
</html>";
    } else {
        echo "No worker details found for the predicted worker IDs.";
    }
} else {
    echo "Please submit the form.";
}
?>
