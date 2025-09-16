import os
import subprocess
from selector.select import run as run_select # type: ignore
from inserter.insert import run as run_insert # type: ignore

def main():
    choice = input("What do you want to run? (Select/Insert): ").strip().lower()
    if choice == "select".lower():
        run_select()
    elif choice == "insert".lower():
        run_insert()
    else:
        print("Nothing to do.")

if __name__ == "__main__":
    main()