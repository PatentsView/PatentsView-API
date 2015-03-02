import markdown, openpyxl, os, string, sys

import xml.etree.ElementTree as ET

OUTPUT_DIRECTORY = "test"


def make_field_list_json(outdir):
    pass


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

    md = open("doc.md").read()
    html = markdown.markdown(md)

    with open(os.path.join(outdir, "doc.html")) as f:
        print(tpl.substitute(content=html), file=f)


def main():
    make_field_list_json(OUTPUT_DIRECTORY)
    make_documentation_html(OUTPUT_DIRECTORY)


if __name__ == "__main__":
    main()
