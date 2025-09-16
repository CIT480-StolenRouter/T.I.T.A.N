import os # type: ignore
import hashlib

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


dbInsert_password = input("Create your password: ")
salt, hashed_password = hash_with_salt(dbInsert_password)
print(salt)
print(hashed_password)