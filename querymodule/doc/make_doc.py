import json, markdown, openpyxl, os, re, string, sys

import xml.etree.ElementTree as ET

OUTPUT_DIRECTORY = "../public_html"


def sheet_data(ws):
    ary = []
    header = None

    for row in ws.iter_rows():
        values = [cell.value for cell in row]

        if not header:
            header = values
            continue

        if not any(values):
            continue

        ary.append(dict(zip(header, values)))

    return ary


def make_field_list_html(field_list):

    def field_list_columns(row):
        column_names = ["API Field Name", "Group", "Type", "Query", "Return", "Sort"]
        cols = [(re.sub(" ", "_", s.lower()), s) for s in column_names]

        return dict((k, row[v]) for k, v in cols)

    s = "<tr><td>{api_field_name}</td><td>{group}</td><td>{type}</td><td>{query}</td><td>{return}</td><td>{sort}</td></tr>"
    return "\n".join(s.format(**field_list_columns(row)) for row in field_list)


def make_documentation_html(outdir):
    tpl = string.Template("""
          <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
              "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
          <html xmlns="http://www.w3.org/1999/xhtml">
          <head>
          <title>PatentsView Query Module API</title>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
          <link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" />
          </head>
          <body>
          $content
          </body>
          </html>
          """)

    field_lists = {}
    wb = openpyxl.load_workbook("API field lists.xlsx", data_only=True, use_iterators=True)

    if not os.path.exists(os.path.join(outdir, "field_lists")):
        os.makedirs(os.path.join(outdir, "field_lists"))

    keep_sections = ["patent", "inventor", "assignee", "cpc subsection", "uspc", "nber subcat", "location"]

    for ws in wb.worksheets:
        if ws.title not in keep_sections:
            continue

        result = sheet_data(ws)
        field_lists[ws.title] = make_field_list_html(result)

        with open(os.path.join(outdir, "field_lists", "{}.json".format(ws.title)), "w") as f:
            print(json.dumps(result), file=f)

    md = string.Template(open("doc.md").read())
    md2 = md.substitute(patent_field_list=field_lists["patent"],
                        inventor_field_list=field_lists["inventor"],
                        assignee_field_list=field_lists["assignee"],
                        cpc_subsection_field_list=field_lists["cpc subsection"],
                        uspc_field_list=field_lists["uspc"],
                        nber_subcat_field_list=field_lists["nber subcat"],
                        location_field_list=field_lists["location"])
    html = markdown.markdown(md2)

    with open(os.path.join(outdir, "doc.html"), "w") as f:
        s = tpl.substitute(content=html)
        print(s, file=f)


def main(outdir):
    make_documentation_html(outdir)


if __name__ == "__main__":
    main(OUTPUT_DIRECTORY)
