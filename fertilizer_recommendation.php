<!DOCTYPE html>
<html>
<head>
    <title>Fertilizer Recommendation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #4CAF50;
        }

        form {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .card {
            background-color: #e8f5e9;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .card h2 {
            margin-top: 0;
            color: #4CAF50;
        }

        .card p {
            margin: 5px 0;
        }

        .back-button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Fertilizer Recommendation</h1>
    <form method="post" action="">
        <label for="soil_type">Soil Type:</label><br>
        <input type="text" id="soil_type" name="soil_type" required><br><br>
        <label for="crop">Crop:</label><br>
        <input type="text" id="crop" name="crop" required><br><br>
        <input type="submit" value="Get Recommendation">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $soil_type = $_POST['soil_type'];
        $crop = $_POST['crop'];

        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "agrihire";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        function get_fertilizer_recommendation($soil_type, $crop) {
            global $conn;
            $stmt = $conn->prepare("SELECT recommendation FROM fertilizer_recommendations WHERE soil_type = ? AND crop = ?");
            $stmt->bind_param("ss", $soil_type, $crop);
            $stmt->execute();
            $stmt->bind_result($recommendation);
            $stmt->fetch();
            $stmt->close();
            return $recommendation;
        }

        $recommendation = get_fertilizer_recommendation($soil_type, $crop);

        if ($recommendation) {
            echo "<div class='card'>";
            echo "<h2>Fertilizer Recommendation</h2>";
            echo "<p>For <strong>$crop</strong> on <strong>$soil_type</strong> soil:</p>";
            echo "<p>$recommendation</p>";
            echo "<a href='http://localhost/knn' class='back-button'>Back</a>";
            echo "</div>";
        } else {
            echo "<div class='card'>";
            echo "<h2>No Recommendation Found</h2>";
            echo "<p>No recommendation found for <strong>$crop</strong> on <strong>$soil_type</strong> soil.</p>";
            echo "<a href='http://localhost/knn' class='back-button'>Back</a>";
            echo "</div>";
        }
    } else {
        echo "Please submit the form.";
    }
    ?>
</body>
</html>
