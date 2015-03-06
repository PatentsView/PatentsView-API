import json, markdown, openpyxl, os, re, string, sys

import xml.etree.ElementTree as ET

# OUTPUT_DIRECTORY = "../public_html"
OUTPUT_DIRECTORY = "test"


def sheet_data(ws):
    ary = []
    header = None

    def encode(s):
        if s:
            return s.encode("iso-8859-1", "xmlcharrefreplace").decode("utf-8")
        else:
            return None

    for row in ws.iter_rows():
        values = [encode(cell.value) for cell in row]

        if not header:
            header = values
            continue

        if not any(values):
            continue

        ary.append(dict(zip(header, values)))

    return ary

def snake_case(s):
    return "_".join(w.lower() for w in s.split())


def make_field_list_header(field_list_column_names):
    cells = ["<th>{}</th>".format(s) for s in field_list_column_names]
    return "<tr>" + "".join(cells) + "</tr>"

def make_row_template(column_names):
    cells = ["<td>{{{}}}</td>".format(snake_case(s)) for s in column_names]
    return "<tr>" + "".join(cells) + "</tr>"


def make_field_list_html(column_names, field_list):
    lines = ['<table class="table table-striped documentation-fieldlist">']
    lines.append(make_field_list_header(column_names))

    def field_list_columns(row):
        cols = [(snake_case(s), s) for s in column_names]
        return dict((k, row[v]) for k, v in cols)

    # s = "<tr><td>{api_field_name}</td><td>{group}</td><td>{common_name}</td><td>{type}</td><td>{query}</td><td>{return}</td><td>{sort}</td><td>{description}</td></tr>"
    s = make_row_template(column_names)

    for row in field_list:
        lines.append(s.format(**field_list_columns(row)))

    lines.append("</table>")
    return "\n".join(lines)


def make_documentation_html(outdir):
    with open("page.html") as f:
        page_tpl = string.Template(f.read())

    field_lists = {}
    wb = openpyxl.load_workbook("API field lists.xlsx", data_only=True, use_iterators=True)

    if not os.path.exists(os.path.join(outdir, "field_lists")):
        os.makedirs(os.path.join(outdir, "field_lists"))

    # keep_sections = ["patent", "inventor", "assignee", "cpc subsection", "uspc", "nber subcat", "location"]
    keep_sections = ["inventor"]
    field_list_column_names = ["API Field Name", "Group", "Common Name", "Type", "Sort", "Description"]

    for ws in wb.worksheets:
        if ws.title not in keep_sections:
            continue

        title = snake_case(ws.title)
        result = sheet_data(ws)

        # create the JSON file

        fname = os.path.join(outdir, "field_lists", "{}.json".format(title))

        with open(fname, "w") as f:
            print(json.dumps(result), file=f)

        # build the documentation page for this section

        fname = os.path.join("{}.html".format(title))
        with open(fname) as f:
            body_tpl = string.Template(f.read())

        field_list = make_field_list_html(field_list_column_names, result)
        fname = os.path.join(outdir, "{}.html".format(title))

        with open(fname, "w") as f:
            body = body_tpl.substitute(field_list=field_list)
            page = page_tpl.substitute(body=body)
            print(page, file=f)


def main(outdir):
    make_documentation_html(outdir)


if __name__ == "__main__":
    main(OUTPUT_DIRECTORY)
