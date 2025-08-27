# app.py
from flask import Flask, render_template_string, request # type: ignore
import dbscript  # renamed module

app = Flask(__name__)

TEMPLATE = """
<form method="post">
  <button type="submit">Run Script</button>
</form>
{% if output %}<pre>{{ output }}</pre>{% endif %}
"""

@app.route("/", methods=["GET", "POST"])
def index():
    output = None
    if request.method == "POST":
        import io, sys
        buf, old_stdout = io.StringIO(), sys.stdout
        sys.stdout = buf
        try:
            dbscript.run()            # <-- call your function here
        finally:
            sys.stdout = old_stdout
        output = buf.getvalue()
    return render_template_string(TEMPLATE, output=output)

if __name__ == "__main__":
    app.run(debug=True)
