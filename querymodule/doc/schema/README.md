Create an empty sqlite3 database using `fake_schema.sql`.

    sqlite3 fake.db < fake_schema.sql

Run [schemacrawler](http://schemacrawler.sourceforge.net/) to generate the SVG.

    sc -server=sqlite -database=fake.db -password= -infolevel=standard -portablenames -command=graph -outputformat=svg

    Rename the output to `schema.svg`
