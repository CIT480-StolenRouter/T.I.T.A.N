import psycopg2 # type: ignore
from psycopg2 import Error # type: ignore

print ("Hello World")
#DB username: postgres
#DB password: Peligr0!
#hostname: 192.168.56.104
#DB name: mydb (\c mydb)
#Table name: users
'''
db_name = input("Enter name of database: ")
db_user = input("Enter database username: ")
db_host = input("Enter database host ip: ") 
db_pass = input("Enter database password: ")
db_table = input("Enter name of table to use within database: ",)
'''
def run():
        db_name = ("mydb")
        db_user = ("postgres")
        db_pass = ("Peligr0!")
        db_host = ("100.111.190.113")
        db_port = 5433

        print("\n")

        #Connect to database
        print ("*Connecting...*")
        try:
            conn = psycopg2.connect(f"dbname='{db_name}' user='{db_user}' host='{db_host}' password='{db_pass}' port='{db_port}'")
            print ("*Connected!*", "\n")
            print (conn, "\n")

            #Create a cursor to perform database ops
            cursor = conn.cursor()

            #print PostgreSQL details
            print('--PostgreSQL server information--')
            print (conn.get_dsn_parameters(), "\n")

            #Execute a SQL query
            #cursor.execute("SELECT version();")
            cursor.execute("SELECT * FROM users WHERE user_id >= 1;")

            #Fetch results
            record = cursor.fetchall()
            print("Your query result:\n")
            for row in record:
                 #row is tuple like
                 print(f"\nuser_id: {row[0]} | Email: {row[1]} | Name: {row[2]} | Salt: {row[3]} | Hash: {row[4]} | Role: {row[5]}")

        except (Exception, Error) as error:
            print("Error while connecting to PostgreSQL", error)
        finally:
            if (conn):
                cursor.close()
                conn.close()
                print("\nPostgreSQL connection is closed")


if __name__ == "__main__":
    run()
