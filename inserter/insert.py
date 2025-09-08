import hashlib
import os
import psycopg2  # type: ignore
from psycopg2 import Error  # type: ignore

# sudo apt install -y libpq-dev python3-dev build-essential
# pip install psycopg2-binary


def hash_with_salt(input_string: str) -> tuple[str, str]:
    #Hash a string with a random 16-byte salt and return (salt_hex, sha256_hex)
    salt = os.urandom(16)
    hasher = hashlib.sha256()
    hasher.update(salt + input_string.encode("utf-8"))
    return salt.hex(), hasher.hexdigest()


def run():
    db_name = "mydb"
    db_user = "postgres"
    db_pass = "projecttitan"
    db_host = "100.111.190.113"
    db_port = 5433
    # Access host machine at port 5433
    # Host machine then forwards to its Database VM on port 5432

    print("\n*Connecting...*") # status checkpoint
    conn = None
    cursor = None

    try:    # Connect to database with credentials
        conn = psycopg2.connect(
            dbname=db_name,
            user=db_user,
            password=db_pass,
            host=db_host,
            port=db_port,
        )
        print("*Connected!*\n") # status checkpoint

        # Ask once; only allow the known table to avoid injection
        # Eventuall allow more tables, for now, we focus on inserting into 'empusers' table
        while True:
            db_table = input("Table to INSERT INTO: ").strip()
            if db_table == "empusers": #Check for 'empusers' table input
               
                # Hard-code the safe identifiers after validation
                table = "empusers" # postgre table name
                cols = ( #postegre table column names
                    "emp_firstname",
                    "emp_lastname",
                    "emp_email",
                    "emp_phonenum",
                    "emp_passwordhash",
                    "emp_passwordsalt",
                )
                break
            else:
                print("Invalid table, try again (hint: empusers).")

        # Prompt user input for empuser creation. Strip white spaces
        # Additionally, we store salt value and corn beef password
        dbInsert_fname = input("Enter your first name: ").strip()
        dbInsert_lname = input("Enter your last name: ").strip()
        dbInsert_email = input("Enter your email: ").strip()
        dbInsert_phoneNumber = input("Enter your phone number: ").strip()
        dbInsert_password = input("Create your password: ")
        salt_hex, hashed_password = hash_with_salt(dbInsert_password)

        # Ensure phone column in DB is wide enough (VARCHAR(25)
        # ALTER TABLE empusers ALTER COLUMN emp_phonenum TYPE varchar(25);

        # Build SQL (parameterized) and execute
        # -- Creates a list of string "%s" for as many elements in cols (above)
        # -- LOOKS LIKE ["%s", "%s", "%s", ...]
        # -- ", ".join --> Joins this list into a single string w/ comma seperation
        # -- LOOKS LIKE "%s, %s, %s, ..."
        placeholders = ", ".join(["%s"] * len(cols))

        # Placeholder "%," as length of cols for column value input
        # Joins cols same way as above ^^
        # -- ["emp_firstname", "emp_lastname", "emp_email", ...]
        # -- TURNS INTO -- "emp_firstname, emp_lastname, emp_email ..."
        column_list = ", ".join(cols)

        # SQL insert statement -- INSERT INTO empusers (list of all columns from tuple) VALUES ("%," for placeholding cols)
        insert_sql = f"INSERT INTO {table} ({column_list}) VALUES ({placeholders});"

        record_to_insert = (
            dbInsert_fname,
            dbInsert_lname,
            dbInsert_email,
            dbInsert_phoneNumber,
            hashed_password,
            salt_hex,
        )

        # psycopg2 execute code
        cursor = conn.cursor()
        # Insert statement + what inserting
        cursor.execute(insert_sql, record_to_insert)
        conn.commit()
        
        print(f"{cursor.rowcount} record inserted successfully into {table}!") # status check

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
     