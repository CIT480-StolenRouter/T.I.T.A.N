import hashlib
import os
import psycopg2 # type: ignore
from psycopg2 import Error # type: ignore

def hash_with_salt(input_string):
    #Hashes a string with a randomly generated salt and returns both the salt and hashed value.

    #16 byte salt
    salt = os.urandom(16)
    #Encode input string to bytes
    input_bytes = input_string.encode('utf-8')
    #Concatonate salt and input string bytes
    salted_input = salt + input_bytes
    #Create SHA256 hash object
    hasher = hashlib.sha256()
    #Update the hash object with the salted input
    hasher.update(salted_input)
    #Get hexadecimal representation of the hash
    hashed_value = hasher.hexdigest()
    #Return the salt and hashed value
    return salt.hex(), hashed_value

def run():
        db_name = ("mydb")
        db_user = ("postgres")
        db_pass = ("Peligr0!")
        db_host = ("100.111.190.113")
        db_port = 5433

        print("\n")

        #Connect to database
        print ("*Connecting...*")
        conn = None
        cursor = None
        
        try:
            conn = psycopg2.connect(f"dbname='{db_name}' user='{db_user}' host='{db_host}' password='{db_pass}' host='{db_host}' port='{db_port}'")
            print ("*Connected!*", "\n")
            print (conn, "\n")

            #Create a cursor to perform database ops
            cursor = conn.cursor()

            #Construct INSERT INTO
            print("Construct insert statement...")
            print("")
            
            while True:
                 db_table = input("Table to INSERT INTO: ")
                 if db_table == "users":
                    col1 = "name"
                    col2 = "email"
                    col3 = "salt"
                    col4 = "password_hash"
                    break
                 else:
                    print("Invalid table, try again.")
        
            dbInsert_fname = input("Enter your first name: ")
            dbInsert_lname = input("Enter your last name: ")
            dbInsert_email = input("Enter your email: ")
            dbInsert_password = input("Create your password: ")
            salt, hashed_password = hash_with_salt(dbInsert_password)

            #Build SQL command and Execute
            postgres_insert_query = f"""
            INSERT INTO {db_table} ({col1}, {col2}, {col3}, {col4})
            VALUES (%s, %s, %s, %s);
            """
            
            record_to_insert = (f"{dbInsert_fname} {dbInsert_lname}", dbInsert_email, salt, hashed_password)
            cursor.execute(postgres_insert_query, record_to_insert)


            #Commit changes to the DB
            conn.commit()
            count = cursor.rowcount
            print(count, "Record inserted successfully into users table!")

        except (Exception, Error) as error:
            print("Error while connecting to PostgreSQL", error)
        finally:
            if cursor is not None:
                cursor.close()
            if conn is not None:
                conn.close()
            print("PostgreSQL connection is closed")

if __name__ == "__main__":
    run()





#postgres_insert_query = (f"INSERT INTO {db_table} ({col1}, {col2}, {col3}, {col4}) VALUES ('{dbInsert_fname + " " + dbInsert_lname}', '{dbInsert_email}', '{dbInsert_password}')")
            