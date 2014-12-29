#PatentsView API Design and Technical Notes

The PatentsView API (PV API) is written largely in PHP, makes use of the [Slim Framework](http://slimframework.com "Slim Framework") and log4php, and was developed for the Windows platform. Documentation for using the API can be found [here](http://54.88.226.197/api). Documentation for building and deploying the API can be found [here](https://github.com/CSSIP-AIR/PatentsView-API/wiki/API-Site-Setup-Instructions). This document will describe the general design and operation of the API, as well as some pertinent technical notes.

##Source Code and Source Database
The code for the API is in [GitHub](https://github.com/CSSIP-AIR/PatentsView-API). It uses a database built using code found [here](https://github.com/CSSIP-AIR/PatentsView-DB), [here](https://github.com/CSSIP-AIR/PatentsProcessor), and [here](https://github.com/CSSIP-AIR/InventorDisambiguator) in GitHub, and uses data from the USPTO bulk granted patent and patent application files from USPTO and distributed by [Reed Technology](http://www.uspto.gov/products/catalog/index.jsp).

##Source File Walkthrough
###public_html Folder 
The `public_html` folder holds the files that are public-facing: currently

* `doc.html`
* `index.php`
* `web.config`.

***index.php***

The entry into the API is `public_html\index.php`. This file registers and instantiates the Slim framework and passes execution to `app\routes\query.php`, which contains the Slim route definitions and handlers.

***doc.html***

Created from `doc\doc.md` by using `doc\make_doc.py`, this provides documentation for API users.

***web.config***

This is the standard IIS web.config file, and contains the basic configuration settings for the API web site.

###doc Folder
Contains the documentation (`doc.md`) for API users, in Markdown format, and a Python script (`make_doc.py`) for converting `doc.md` to HTML.

###DBScripts Folder

This folder contains MySQL scripts to create the schema objects for the PVSupport database.

###logs Folder

No code files are actually put into this folder, but it needs to exist so that when the running code attempts to create log files it succeeds.

###tests Folder

Contains the unit tests for the API. Note that some of these unit tests are data dependent: if the data in the database change, then it is possible some of the unit tests will fail.

###app Folder

This folder contains the bulk of the code for the API.

***routes\query.php***

This file contains the Slim framework routes. It defines the routes available for GET and POST, and has some basic validation of the parameters (e.g. to make sure they are valid JSON format).

It also encodes the results into JSON or XML prior to returning them in the response object.

You will need to edit this file to add a new routes for a new or existing primary entity.

***entitySpecs.php***

`entitySpecs.php` contains the metadata used by the API to map API fields to the MySQL database.

You will see that there are two metadata structures for each primary entity. Primary entities are: patents, inventors, assignees, cpc subsections, uspc mainclasses, and locations; these correspond 1-to-1 with the API primary routes. There is one metadata structure called `*_ENTITY_SPECS` and one called `*_FIELD_SPECS`.

**`*_ENTITY_SPECS`** has an entry for the primary entity and one for each subentity; the one for the primary entity must be listed first. These define the name of the entity, what field is used for the key for the entity, and the SQL join statement for the entity. More details about the use of these are in the code.

**`*_FIELD_SPECS`** has an entry for each field that is available in any part of the API for this primary entity, the SQL expression to obtain the value for the field, and flags that dictate the use of the field.

This should be the only file that you need to change when adding or modifying fields or subentities. All the rest of the code uses this metadata when building SQL statements and formatting the API results. You will also modify this file when adding whole new primary entities, as well as adding new Slim routes in `routes\query.php`. 

***executeQuery.php***

`executeQuery.php` contains the `executeQuery` function, which orchestrates the parsing of the API call into the querying of the database and taking those results and putting them into nested PHP arrays. There is not much code in this file - it simply provides order to those steps and a little glue between them.

***QueryParser.php***

`QueryParser.php` contains the class that performs the parsing of the API call query parameters into the needed data structures to perform the querying. Primarily it will build a SQL *where* clause from query parameters, while also recording which fields are referenced in that clause. It performs many error checks to ensure that the fields specified in the query parameters are valid for the query operation and that the syntax of the query parameter is correct. The end result is a string that contains the *where* clause and a list of fields used in that clause.

This file should be the only one to change in order to define new query operators; e.g. if a *between* operator is desired in the future.

Be sure to reference the API documentation in the `doc\doc.md` file to ensure that the code in this file is in sync  with the syntax in the documentation.

***DatabaseQuery.php***

`DatabaseQuery.php` contains the class that executes the database statements in order to obtain the results for the specified query.

The general model for querying the database is:

* Check to see if the query has been run before and its results cached in the PV support database.
  * If not, then build and execute an *insert select* statement that will insert into the PV support dastabase the primary entity IDs that meet the query criteria.
* Build and execute *select* statements to get one page of results. There will be a *select* issued for the primary entity and one each for the subentities. They will use the cached results in the PV support database (which are ordered primary entity IDs) so that the expensive *where* clause does not have to be issued repeatedly.
* The results will be an array of arrays, with one root array for each entity-type, and the subarrays containing the individual entities.    

***convertDBResultsToNestedStructure.php***

`convertDBResultsToNestedStructure.php` contains the function to convert the DB results into the structure for returning from the API. The input is an array of arrays, where there is one root array for each entity type involved in the results, and within that an array of entity data objects. The output will be an array of primary entities, and within each primary entity will be its fields and an array of subentities with their fields.

The code in this is rather tight in order to get better performance. It also relies heavily on PHP *dynamic variables*, aka *variable variables*. This makes the code tougher to read; but without that there would be multiple blocks of essentially the same code, with the only difference being variable names, and thus would be harder to maintain and keep each block consistent. When debugging, you can add watch variables to see the contents of these dynamic variables.   

***ErrorHandler.php***

Basic logging of errors and setting the HTTP response object with status codes and messages. Uses *log4php*.

***config-sample.php***

Configuration settings for the API for defining the MySQL connection parameters and API limits.

Note that the API actually looks for and uses the file `config.php`. That file is not and should not be checked into GitHub. The reason for that is to avoid putting database credentials into a public code repository. So on operating environments, this file (`config-sample.php`) would need to be copied to `config.php`, and then change the settings in `config.php` to match that operating environment. 

##Technical Notes

* Cached results
  * The first time a query is run, its results are cached in the PV support database. The results will be the primary entities primary key and the sequence of those IDs to correspond to the sort order specified in the API.
  * A CRC32 hash is used to determine if the query has been run before. That hash uses the primary entity field name, the query parameter, and the sort parameter.
  * Subsequent requests of the same query will use the cached results. This provides significant performance improvement for repeated queries, as in the case when paging through results, since the most expensive part (determining the result set) is only run once.
  * This cache must be cleared out when:
    * The PV database is updated with new or modified data.
    * The API is updated to a new version.
    * At a regular interval to keep it from growing too large.
      * This interval is TBD and depends on the growth rate, the storage space available on the DB server, and the performance of inserting into the cache tables.
      * An alternative to completely clearing the cache results is to use a sliding window based on either date or total number of cached results.  
* Only one level of entity hierarchy
  * The design model for this whole API is based on the limitation that the results from the API will only have one-level of hierarchy of subentities under the primary entity. For e.g., if patents are the primary entity and inventors are a subentity, the inventor subentity cannot also have subentities (like locations). If that model needs to be changed, then much of the API code will need to be changed as well.
  * Part of the reason for choosing this model was the requirement by USPTO that the API results, when formatted as XML, could be imported directly into an Excel spreadsheet. While Excel can handle multiple levels in the XML hierarchy, it begins to get very difficult to understand and make use of the data in Excel.  

