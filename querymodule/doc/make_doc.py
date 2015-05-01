BASE_URL = "http://www.dev.patentsview.org/api/"

import glob, jinja2, json, openpyxl, os, shutil, sys

import xml.etree.ElementTree as ET

def extract_field_list(ws):
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

    return sorted(ary, key=lambda x: (x[ "Group"], x["API Field Name"]))


def snake_case(s):
    return "_".join(w.lower() for w in s.split())


def make_documentation_html(outdir):
    env = jinja2.Environment(loader=jinja2.FileSystemLoader("."), trim_blocks=True, lstrip_blocks=True)

    # copy css

    if not os.path.exists(os.path.join(outdir, "css")):
        os.makedirs(os.path.join(outdir, "css"))

    shutil.copy("css/custom.css", os.path.join(outdir, "css"))

    # copy js

    if not os.path.exists(os.path.join(outdir, "js")):
        os.makedirs(os.path.join(outdir, "js"))

    for filename in glob.glob("js/*.js"):
        shutil.copy(filename, os.path.join(outdir, "js"))

    # copy images

    if not os.path.exists(os.path.join(outdir, "img")):
        os.makedirs(os.path.join(outdir, "img"))

    for filename in glob.glob("img/*.png"):
        shutil.copy(filename, os.path.join(outdir, "img"))
    
    field_lists = {}
    wb = openpyxl.load_workbook("API field lists.xlsx", data_only=True, use_iterators=True)

    if not os.path.exists(os.path.join(outdir, "field_lists")):
        os.makedirs(os.path.join(outdir, "field_lists"))

    keep_sections = ["patent", "inventor", "assignee", "cpc subsection", "uspc", "nber subcat", "location"]
    field_list_column_names = ["API Field Name", "Group", "Common Name", "Type", "Query", "Description"]

    for ws in wb.worksheets:
        if ws.title not in keep_sections:
            continue

        title = snake_case(ws.title)
        field_list = extract_field_list(ws)

        # create the JSON file

        fname = os.path.join(outdir, "field_lists", "{}.json".format(title))

        with open(fname, "w") as f:
            print(json.dumps(field_list), file=f)

        # build the documentation page for this section

        fname = os.path.join("{}.html".format(title))
        page_tpl = env.get_template(fname)

        fname = os.path.join(outdir, "{}.html".format(title))

        with open(fname, "w") as f:
            page = page_tpl.render(field_list_column_names=field_list_column_names, field_list=field_list,
                                   base_url=BASE_URL)
            print(page, file=f)

    # other pages

    with open("schema/schema.svg") as f:
        schema_svg = f.read() 
    for title in ["doc", "query_language"]:
        fname = os.path.join("{}.html".format(title))
        page_tpl = env.get_template(fname)

        fname = os.path.join(outdir, "{}.html".format(title))

        with open(fname, "w") as f:
            page = page_tpl.render(schema_svg=schema_svg, base_url=BASE_URL)
            print(page, file=f)



def main(outdir):
    make_documentation_html(outdir)


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("USAGE: python make_doc.py DIRECTORY")
        sys.exit(1)

    main(sys.argv[1])
