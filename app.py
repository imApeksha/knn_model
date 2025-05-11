from flask import Flask, request, jsonify
from flask_cors import CORS
import mysql.connector
import pandas as pd
from sklearn.preprocessing import LabelEncoder
from sklearn.neighbors import KNeighborsClassifier
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.neighbors import NearestNeighbors

app = Flask(__name__)  # Only one instance
CORS(app)  # Enable CORS


# Function to establish a fresh database connection
def get_db_connection():
    try:
        conn = mysql.connector.connect(
            host="sql209.infinityfree.com:3306",
            user="if0_38925154",
            password="56XGgIRWziQx",
            database="if0_38925154_agri_hire"
        )
        return conn
    except mysql.connector.Error as e:
        print(f"Database connection failed: {e}")
        return None


# Fetch fertilizer data
def fetch_fertilizer_data():
    conn = get_db_connection()
    if not conn:
        return pd.DataFrame()

    cursor = conn.cursor()
    cursor.execute("SELECT soil_type, crop, recommendation FROM fertilizer_recommendations")
    data = cursor.fetchall()
    conn.close()

    if not data:
        return pd.DataFrame(columns=["SoilType", "Crop", "Recommendation"])

    return pd.DataFrame(data, columns=["SoilType", "Crop", "Recommendation"])


# Train KNN model for fertilizer recommendation
def train_knn_model():
    df = fetch_fertilizer_data()
    if df.empty:
        print("No data found in database for training.")
        return None, None

    # Encode categorical data
    label_encoders = {col: LabelEncoder().fit(df[col]) for col in ["SoilType", "Crop", "Recommendation"]}
    df_encoded = df.apply(lambda col: label_encoders[col.name].transform(col) if col.name in label_encoders else col)

    # Train KNN model
    X = df_encoded[["SoilType", "Crop"]]
    y = df_encoded["Recommendation"]

    knn = KNeighborsClassifier(n_neighbors=3)
    knn.fit(X, y)
    

    return knn, label_encoders


# Load trained fertilizer recommendation model
knn_model, encoders = train_knn_model()


@app.route("/data", methods=["GET"])
def get_data():
    conn = get_db_connection()
    if not conn:
        return jsonify({"error": "Database connection failed"}), 500

    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM worker")  # Replace 'your_table_name' with your actual table name
        rows = cursor.fetchall()
        conn.close()

        if not rows:
            return jsonify({"message": "No data found"}), 404

        return jsonify(rows), 200
    except mysql.connector.Error as e:
        return jsonify({"error": f"Database query failed: {str(e)}"}), 500



@app.route("/recommend", methods=["POST"])
def recommend_fertilizer():
    if not knn_model or not encoders:
        return jsonify({"error": "Model not trained or database is empty"}), 500

    try:
        data = request.get_json()
        if not data:
            return jsonify({"error": "Invalid JSON or missing data"}), 400

        soil_type = data.get("soil_type")
        crop = data.get("crop")

        if not soil_type or not crop:
            return jsonify({"error": "Missing required parameters"}), 400

        # Encode user input
        soil_encoded = encoders["SoilType"].transform([soil_type])[0]
        crop_encoded = encoders["Crop"].transform([crop])[0]

        # Predict recommendation
        pred_encoded = knn_model.predict([[soil_encoded, crop_encoded]])[0]
        recommendation = encoders["Recommendation"].inverse_transform([pred_encoded])[0]

        return jsonify({"recommendation": recommendation})

    except ValueError as e:
        return jsonify({"error": f"No recommendation available: {str(e)}"}), 400


# Fetch worker data and train KNN model for worker profiles
def fetch_worker_data(search_query, city_name=None, k=5):
    conn = get_db_connection()
    cursor = conn.cursor()

    query = """
    SELECT w.worker_id, w.name, w.city_id, c.city, w.state_id, w.status, w.work_profile
    FROM worker w
    JOIN city c ON w.city_id = c.city_id
    WHERE w.work_profile LIKE %s
    """
    params = (f"%{search_query}%",)

    if city_name:
        query += " AND c.city = %s"
        params += (city_name,)

    cursor.execute(query, params)
    rows = cursor.fetchall()
    conn.close()

    if not rows:
        return {"error": "No workers found"}

    worker_profiles = [row[6] for row in rows]
    worker_ids = [row[0] for row in rows]

    vectorizer = TfidfVectorizer()  # Always initialize a new vectorizer
    profile_vectors = vectorizer.fit_transform(worker_profiles)

    knn = NearestNeighbors(n_neighbors=min(k, len(worker_profiles)), metric="cosine")
    knn.fit(profile_vectors)

    search_vector = vectorizer.transform([search_query])
    distances, indices = knn.kneighbors(search_vector)

    best_workers = [rows[i] for i in indices[0]]

    return [{"ID": row[0], "Name": row[1], "CityID": row[2], "CityName": row[3], "StateID": row[4], "Status": row[5], "WorkProfile": row[6]} for row in best_workers]


@app.route("/worker", methods=["POST"])
def search_worker():
    data = request.get_json()
    search_query = data.get("search_query")
    city_name = data.get("city_name")

    if not search_query:
        return jsonify({"error": "Missing required parameters"}), 400

    worker_data = fetch_worker_data(search_query, city_name)
    return jsonify(worker_data)


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
