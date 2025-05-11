import mysql.connector
import json
import sys

def get_worker_data(search_query):
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="agrihire"
        )
        cursor = conn.cursor()

        query = """
        SELECT worker_id, name, city_id, state_id, status, work_profile
        FROM worker
        WHERE work_profile LIKE %s
        """
        cursor.execute(query, ('%' + search_query + '%',))
        rows = cursor.fetchall()

        worker_data = [{"ID": row[0], "Name": row[1], "CityID": row[2], "StateID": row[3], "Status": row[4], "WorkProfile": row[5]} for row in rows]

        cursor.close()
        conn.close()
        return worker_data

    except mysql.connector.Error as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

def main():
    search_query = sys.argv[1] if len(sys.argv) > 1 else "Plowing"
    worker_data = get_worker_data(search_query)
    print(json.dumps(worker_data, indent=4))

if __name__ == "__main__":
    main()
