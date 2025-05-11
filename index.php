<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriHire - Find Workers</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f0f2f5;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 350px;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #34495e;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 12px;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #218c53;
        }
    </style>
</head>
<body>
    <h1>AgriHire - Find Workers</h1>
    
    <div class="form-container">
        <form method="post" action="knn_prediction.php">
            <label for="search_query">Enter Work Profile:</label>
            <input type="text" id="search_query" name="search_query" required>
            <input type="submit" value="Find Workers">
        </form>
    </div>

    <h1>Fertilizer Recommendation</h1>

    <div class="form-container">
        <form method="post" action="fertilizer_recommendation.php">
            <label for="soil_type">Soil Type:</label>
            <input type="text" id="soil_type" name="soil_type" required>
            
            <label for="crop">Crop:</label>
            <input type="text" id="crop" name="crop" required>
            
            <input type="submit" value="Get Recommendation">
        </form>
    </div>





    <form method="post" id="chat-form">
    <input type="text" name="message" id="message" placeholder="Ask something...">
    <button type="submit">Send</button>
</form>
<div id="chat-response"></div>

<script>
document.getElementById("chat-form").addEventListener("submit", function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    
    fetch("chatbot.php", { method: "POST", body: formData })
    .then(response => response.text())
    .then(data => document.getElementById("chat-response").innerHTML = data);
});
</script>





    
</body>
</html>
