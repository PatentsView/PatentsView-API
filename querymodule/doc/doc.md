# PatentsView Query Module API
## Table of Contents

<ul>
<li>
<a href="#patents_query">Patents Query</a>
<ul>
<li>
<a href="#query_string_format">Query String Format</a> 
<ul>
<li><a href="#query_string_syntax">Syntax</a> </li>
<li><a href="#single_criterion">Single Criterion</a> </li>
<li><a href="#joining_criteria">Joining Criteria</a> </li>
<li><a href="#comparison_operators">Comparison Operators</a> </li>
<li><a href="#negation">Negation</a> </li>
<li><a href="#value_arrays">Value Arrays</a> </li>
<li><a href="#complex_combinations">Complex Combinations</a> </li>
<li><a href="#formats">Formats</a> </li>
</ul>
</li>
<li>
<a href="#field_list_format">Field List Format</a> 
</li>
<li>
<a href="#options_parameter">Options Parameter</a> 
<ul>
<li><a href="#pagination">Pagination</a> </li>
<li><a href="#coinventor">Coinventor</a> </li>
<li><a href="#query_string_example">Example</a> </li>
</ul>
</li>
<li>
<a href="#sort_parameter">Sort Parameter</a> 
</li>
<li>
<a href="#results_format">Results Format</a> 
<ul>
<li><a href="#results_format_syntax">Syntax</a> </li>
<li><a href="#results_example">Example</a> </li>
</ul>
</li>
<li>
<a href="#response_status_codes">Response Status codes</a> 
</li>
<li>
<a href="#patent_field_list">Patent Field List</a> 
</li>
</ul>
</li>
<li>
<a href="#inventors_query">Inventors Query</a> 
<ul>
<li><a href="#inventor_field_list">Inventor Field List</a> </li>
</ul>
</li>
<li>
<a href="#assignees_query">Assignees Query</a> 
<ul>
<li><a href="#assignee_field_list">Assignee Field List</a> </li>
</ul>
</li>
</ul>

## <a name="patents_query"></a> Patents Query

<code>***GET*** /querymodule/v0/patents/query?q{,f,o,s}</code>

<code>***POST*** /querymodule/v0/patents/query</code>

This will search for patents matching the query string (`q`) and returning the data fields listed in the field string (`f`) sorted by the fields in the sort string (`s`) using options provided in the option string (`o`).

The HTTP GET request method is the preferred access mechanism; however when the query parameters exceed a reasonable size (around 2,000 characters), then the POST method can be used. When using the POST method, the query parameters should be embedded within a JSON string within the request body.

<table>
<tr>
<th>Name</th>
<th>Description</th>
<th>Details</th>
</tr>

<tr>
<td><code>q</code></td>
<td>JSON formatted object containing the query parameters. See below for details on formatting this object.</td>
<td> string, required<br/> example: <code>q={"inventor_last_name":"Whitney"}</code></td>
</tr>

<tr>
<td><code>f</code></td>
<td>JSON formatted array of fields to include in the results. If not provided, defaults to the set of fields used in the query parameter plus the patent number.</td>
<td>string, optional <br/> example: <code>f=["patent_number", "date"]</code></td>
</tr>

<tr>
<td><code>s</code></td>
<td>JSON formatted array of objects to sort the results. if not provided, defaults to the unique, internal patent identifier.</td>
<td>string, optional <br/> example: <code>s=[{"patent_number":"desc"}]</code></td>
</tr>

<tr>
<td><code>o</code></td>
<td>JSON formatted object of options to modify the query or results.  Available options are:
<ul>
<li>coinventors &mdash; Whether coinventor data should be shown when using inventor fields in the query. Defaults to true if not provided.  <em>Not yet implemented.</em></li>
<li>page &mdash; return only the Nth page of results. Defaults to 1.</li>
<li>per_page &mdash; the size of each page to return. Defaults to 25.</li>
</ul>
</td>
<td>string, optional <br/> example: <code>o={"coinventors": true, "page": 2, "per_page": 50}</code> </td>
</tr>

<tr>
<td><code>format</code></td>
<td>Specifies the response data format. If not provided, defaults to JSON. Available options are:
<ul>
<li><code>json</code></li>
<li><code>xml</code></li>
</ul>
</td>
<td>string, optional <br/> example: <code>format=xml</code></td>
</tr>

</table>

An example of a complete API call using the GET verb is:

    https://api.patentsview.org/api/v0/patents/query?q={"_gte":{"patent_date":"2007-01-04"}}&amp;f=["patent_number","patent_date"]

An example of the equivalent API call using the POST verb is:

    https://api.patentsview.org/api/v0/patents/query

with the body containing:

    {"q":{"_gte":{"patent_date":"2007-01-04"}},"f":["patent_number","patent_date"]}

### <a name="query_string_format"></a> Query String Format

The query string is always a single JSON object: `{}`, with properties and contained objects that determine the criteria for the query. 

Note: To aid in understanding the structure of the queries below and while creating your own, it is helpful to use JSON validators and visual parsers, like [http://www.jsoneditoronline.org/](http://www.jsoneditoronline.org/) and [http://jsonlint.com/](http://jsonlint.com/). Clicking on the <span class="fa fa-external-link"></span> icons below display the JSON in JSON Editor Online.

#### <a name="query_string_syntax"></a> Syntax

    q={criterion}
    criterion
        pair
        "_eq" : {simple_pair}
        "_neq" : {simple_pair}
        "_gt" : {simple_pair}
        "_gte" : {simple_pair}
        "_lt" : {simple_pair}
        "_lte" : {simple_pair}
        "_begins" : {simple_pair}
        "_contains" : {simple_pair}
        "_text_all" : {simple_pair}
        "_text_any" : {simple_pair}
        "_text_phrase" : {simple_pair}
        "_not" : {criterion}
        "_and" : [{criterion},…]
        "_or" : [{criterion},…]
    pair
        simple_pair
        "field" : [value,…]
    simple_pair
        "field" : value

#### <a name="single_criterion"></a> Single Criterion

The basic criterion, which checks for equality, has the format: `{<field>:<value>}`, where `<field>` is the name of a database field and `<value>` is the value the field will be compared to for equality (see “[Field List]()” for a list of fields and their value data types). For example, this query string will return the patent with the patent number of 7861317:

`q={"patent_number":"7861317"}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22patent_number%22:7861317}"></a>

#### <a name="joining_criteria"></a> Joining Criteria

There can be multiple criteria within a query by using a join operator (`_and`, `_or`) and putting the criteria in an array using square brackets (“`[`“ and “`]`”). The following has multiple criteria, and will return patents that have “Whitney” as an inventor and a grant date of January 4, 2007:

`q={"_and":[{"inventor_last_name":"Whitney"},{"patent_date":"2007-01-04"}]}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json=={%22_and%22:[{%22inventor_last_name%22:%22Whitney%22},{%22patent_date%22:%222007-01-04%22}]}"></a>

#### <a name="comparison_operators"></a> Comparison Operators

Comparison operators can be used to compare a field to a value using comparators besides just equality. The available comparison operators are:

* Integer, float, date, and string
    * `_eq` – equal to
    * `_neq` – not equal to
    * `_gt` – greater than
    * `_gte` – greater than or equal to
    * `_lt` – less than
    * `_lte` – less than or equal to
* String
    * `_begins` – the string begins with the value string
    * `_contains` – the string contains the value string
* Full text
    * `_text_all` – the text contains all the words in the value string
    * `_text_any` – the text contains any of the words in the value string
    * `_text_phrase` – the text contains all the exact phrase of the value string

To specify a comparison operator for a criterion, nest the element containing the criterion inside an element that uses the comparison operator. For example, this query string will return all patents that have a grant date on or after January 4, 2007:

`q={"_gte":{"patent_date":"2007-01-04"}}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_gte%22:{%22patent_date%22:%222007-01-04%22}}"></a>

Note that `q={"_eq":{"patent_date":"2007-01-04"}}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_eq%22:{%22patent_date%22:%222007-01-04%22}}"></a> is functionally equivalent to `q={"patent_date":"2007-01-04"}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22patent_date%22:%222007-01-04%22}"></a>.

#### <a name="negation"></a> Negation

Negation does the opposite of the specified comparison. To specify the negation operator for a criterion, nest the element containing the criterion inside an element that uses the negation operator: `_not`. For example, this query string will return all patents that are not design patents:

`q={"_not":{"patent_type":"design"}}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_not%22:{%22patent_type%22:%22design%22}}"></a>

#### <a name="value_arrays"></a> Value Arrays

If the value of a criterion is an array, then the query will accept a match of any one of the array values. For example, this query will return all patents that have “Whitney” or “Hopper” as an inventor:

`q={"inventor_last_name":["Whitney","Hopper"]}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22inventor_last_name%22:[%22Whitney%22,%22Hopper%22]}"></a>

Note that this is functionally equivalent to: `q={"_or":[{"inventor_last_name":"Whitney"},{"inventor_last_name":"Hopper"}]}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_or%22:[{%22inventor_last_name%22:%22Whitney%22},{%22inventor_last_name%22:%22Hopper%22}]}"></a>

#### <a name="complex_combinations"></a> Complex Combinations

These elements, criteria, arrays, and operators can be combined to define robust queries. Here are a few examples: 

* Patents with a grant date in 2007.
    * `q={"_and":[{"_gte":{"patent_date":"2007-01-04"}},{"_lte":{"patent_date":"2007-12-31"}}]}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_and%22:[{%22_gte%22:{%22patent_date%22:%222007-01-04%22}},{%22_lte%22:{%22patent_date%22:%222007-12-31%22}}]}"></a>
* Patents with an inventor with the last name of “Whitney” or “Hopper” and not a design patent and with a grant date in 2007.
    * `q={"_and":[{"inventor_last_name":["Whitney","Hopper"]},{"_not":{"patent_type":"design"}},{"_gte":{"patent_date":"2007-01-04"}},{"_lte":{"patent_date":"2007-12-31"}}]}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_and%22:[{%22inventor_last_name%22:[%22Whitney%22,%22Hopper%22]},{%22_not%22:{%22patent_type%22:%22design%22}},{%22_gte%22:{%22patent_date%22:%222007-01-04%22}},{%22_lte%22:{%22patent_date%22:%222007-12-31%22}}]}"></a>
* Patents with an inventor with the last name of “Whitney” or “Hopper” or with a title that contains “cotton” or “gin” or “COBOL”.
    * `q={"_or":[{"inventor_last_name":["Whitney","Hopper"]},{"_text_any":{"patent_title":"COBOL cotton gin"}}]}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_or%22:[{%22name_last%22:[%22Whitney%22,%22Hopper%22]},{%22_contains%22:{%22title%22:%22cotton%20gin%22}},{%22_contains%22:{%22title%22:%22COBOL%22}}]}"></a>
* Patents with an inventor with the last name of “Whitney” and with “cotton gin” in the title, or with an inventor with the last name of “Hopper” and with “COBOL” in the title.
    * `q={"_or":[{"_and":[{"inventor_last_name":"Whitney"},{"_text_phrase":{"patent_title":"cotton gin"}}]},{"_and":[{"inventor_last_name":"Hopper"},{"_text_all":{"patent_title":"COBOL"}}]}]}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json=={%22_or%22:[{%22_and%22:[{%22inventor_last_name%22:%22Whitney%22},{%22_text_phrase%22:{%22patent_title%22:%22cotton%20gin%22}}]},{%22_and%22:[{%22inventor_last_name%22:%22Hopper%22},{%22_text_all%22:{%22patent_title%22:%22COBOL%22}}]}]}"></a>

#### <a name="formats"></a> Formats

Dates are expected to be in ISO 8601 date format: YYYY-MM-DD.

### <a name="field_list_format"></a> Field List Format

The field list parameter is a JSON array of the names of the fields to be returned by the query. If not provided, the API will return the fields used in the query criteria. See “[Field List](#patent_field_list)” for the fields available for the results. The following example would return the patent numbers, inventor names, and dates for patents that meet the query criteria:

    f=["patent_number","inventor_last_name","patent_date"]

### <a name="options_parameter"></a> Options Parameter

The options parameter is a JSON formatted object of options to modify the query or results. Available options are:

* `page` and `per_page` – customize how may patents to return per page and which page.
* `coinventors` - whether coinventor data should be shown when using inventor fields in the query. Defaults to `true` if not provided.
* TBD – other options, for example other one-to-many relationships like classes, etc.

#### <a name="pagination"></a> Pagination

By default the API will return the first 25 results. The `page` and `per_page` options can be used to customize the set of results that is returned.

* The `page` option is 1-based and omitting the `page` option will return the first page of results.
* The `per_page` option specifies the number of results per page; it defaults to 25 and has a maximum of 10,000.
* An example for specifying pagination in the options parameter is: `o={"page":2,"per_page":50}`

#### <a name="coinventor"></a> Coinventor

The `coinventor` option is provided to indicate whether coinventor data should be included in the results in a special case when:

1. an inventor field is used in the query, and
1. an inventor field (not necessarily the same one) is used in the output, and
1. the inventor field in the query encompassed by an “and” join operator.

For example, consider this query and output field list:

`q={"_and":[{"_gte":{"patent_date":"2007-01-04"}},{"inventor_last_name":"Whitney"}]}&f=["patent_number","patent_date","inventor_last_name"]` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_and%22:[{%22_gte%22:{%22patent_date%22:%222007-01-04%22}},{%22inventor_last_name%22:%22Whitney%22}]}&amp;f=[%22patent_number%22,%22patent_date%22,%22inventor_last_name%22]"></a>

The results will include all the patents that have a grant date on or after January 4, 2007 and with an inventor with the last name “Whitney”. By default or when `{"coinventors":true}`, the results will include all data for all inventors for the patents. However if `{"coinventors":false}`, the results will only include the inventor data for the inventor “Whitney”. Note that if the patent has multiple inventors that meet the inventor criteria, then each matching inventor will be included in the results. Also note that if the inventor field in the query is encompassed by an “or” join operator, then the coinventor parameter cannot be false.

##### <a name="query_string_example"></a> Example

Consider this example. Assume the database only has the following content:

Patents:

<table>
<tr>
<th>PATENT_NUMBER</th>
<th>NUMBER</th> 
<th>DATE</th>
</tr>

<tr>
<td>PAT1</td>
<td>7861317</td>
<td>1/21/2007</td>
</tr>
</table>

Inventors:

<table>
<tr>
<th>INVENTOR_ID</th><th>PATENT_NUMBER</th><th>NAME_FIRST</th><th>NAME_LAST</th>
</tr>
<tr>
<td>INV1</td><td>pat1</td><td>Grace</td><td>Hopper</td>
</tr>
<tr>
<td>INV2</td><td>pat1</td><td>Eli</td><td>Whitney</td>
</tr>
<tr>
<td>INV3</td><td>pat1</td><td>Willis</td><td>Carrier</td>
</tr>
</table>

Also assume this query:

`q={"_and":[{"_gte":{"patent_date":"2007-01-04"}},{"inventor_last_name":"Whitney"}]}&f=["patent_number","patent_date","inventor_last_name"]` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_and%22:[{%22_gte%22:{%22patent_date%22:%222007-01-04%22}},{%22inventor_last_name%22:%22Whitney%22}]}"></a>

The results would include the following (keeping in mind that coinventor data is included by default):

`{"patents":[{"patent_number":"pat1","patent_date":"2007-01-27","inventors":[{"inventor_last_name":"Hopper"},{"inventor_last_name":"Whitney"},{"inventor_last_name":"Carrier"}]}],"count":1,"total_found":1}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22patents%22:[{%22patent_number%22:%22pat1%22,%22patent_date%22:%222007-01-27%22,%22inventors%22:[{%22inventor_last_name%22:%22Hopper%22},{%22inventor_last_name%22:%22Whitney%22},{%22inventor_last_name%22:%22Carrier%22}]}],%22count%22:1,%22total_found%22:1}"></a>

However if the query were changed to exclude coinventor data, then the query and results would be as such:

`q={"_and":[{"_gte":{"patent_date":"2007-01-04"}},{"inventor_last_name":"Whitney"}]}&f=["patent_number","patent_date","inventor_last_name"]&o={"coinventors":false}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22_and%22:[{%22_gte%22:{%22patent_date%22:%222007-01-04%22}},{%22inventor_last_name%22:%22Whitney%22}]}"></a>

`{"patents":[{"patent_number":"pat1","patent_date":"2007-01-27","inventors":[{"inventor_last_name":"Whitney"}]}],"count":1,"total_found":1}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22patents%22:[{%22patent_number%22:%22pat1%22,%22patent_date%22:%222007-01-27%22,%22inventors%22:[{%22inventor_last_name%22:%22Whitney%22}]}],%22count%22:1,%22total_found%22:1}"></a>

### <a name="sort_parameter"></a> Sort Parameter

The sort parameter is a JSON formatted array of objects that specifies the sort order for the returned results. If empty or not provided, the default sort order will be ascending by patent number.

Each object in the array should be a pair, with the pair’s key is one of the patent fields, and the value is either “asc” or “desc”, to indicate ascending or descending sort, respectively. A couple examples should suffice for understanding:

* `s=[{"patent_num_claims":"desc"}`
    * Primary sort is by `patent_num_claims` in ascending order, so that patents with the most claims will be first, and those with least claims will be last.
* `s=[{"patent_date":"desc"},{"patent_number":"asc"}]`
    * Primary sort is by `patent_date` in descending order, secondarily by `patent_number` in ascending order.

### <a name="results_format"></a> Results Format

#### <a name="results_format_syntax"></a> Syntax

    {"patents":[patent[,...]], "count":count, "total_found":total_found}
    patent
        {[key_value_pair[,...]][,related_group[,...]]}
    related_group
        "entity_name":[related_entity[,...]]
    related_entity
        {key_value_pair[,...]}
    entity_name
        { inventors | assignees | applications | application_citations | cited_patents | citedby_patents | ipcs | uspcs }
    key_value_pair
        "field_name":value
            Where field_name is from the table of fields below.

#### <a name="results_example"></a> Example

`{"patents":[{"patent_number":"pat1","date":"2007-01-27","inventors":[{"inventor_last_name":"Hopper"},{"inventor_last_name":"Whitney"},{"inventor_last_name":"Carrier"}]}],"count":1,"total_found":1}` <a class="fa fa-external-link" href="http://jsoneditoronline.org/?json={%22patents%22:[{%22patent_number%22:%22pat1%22,%22date%22:%222007-01-27%22,%22inventors%22:[{%22inventor_last_name%22:%22Hopper%22},{%22inventor_last_name%22:%22Whitney%22},{%22inventor_last_name%22:%22Carrier%22}]}],%22count%22:1,%22total_found%22:1}"></a>

### <a name="response_status_codes" ></a> Response Status codes

When the query parameters are all valid, the API will return results formatted per “[Results Format](#results_format)” with an HTTP status code of 200. The results will be in the body of the response.

An HTTP status code of 400 will be returned when the query parameters are not valid, typically either because they are not in valid JSON format, or a specified field or value is not valid. The “status reason” in the header will contain the error message. 

An HTTP status code of 500 will be returned when there is an internal error with the processing of the query. The “status reason” in the header will contain the error message.

### <a name="patent_field_list"></a> Patent Field List

<table>
<tr>
<th>API Field Name</th>
<th>Group</th>
<th>Type</th>
<th>Query</th>
<th>Return</th>
<th>Sort</th>
</tr>

<tr><td>app_country</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_date</td><td>applications</td><td>date</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_id</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_number</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_type</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>appcit_app_number</td><td>application_citations</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>appcit_category</td><td>application_citations</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>appcit_date</td><td>application_citations</td><td>date</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>appcit_kind</td><td>application_citations</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>appcit_name</td><td>application_citations</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>appcit_sequence</td><td>application_citations</td><td>integer</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_city</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_country</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_first_name</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_id</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_last_name</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_city</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_country</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_latitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_longitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_state</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_latitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_longitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_num_patents</td><td>assignees</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_organization</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_state</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_type</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_years_active</td><td>assignee</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>citedby_patent_category</td><td>citedby_patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>citedby_patent_date</td><td>citedby_patents</td><td>date</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>citedby_patent_kind</td><td>citedby_patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>citedby_patent_number</td><td>citedby_patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>citedby_patent_title</td><td>citedby_patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>cited_patent_category</td><td>cited_patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>cited_patent_date</td><td>cited_patents</td><td>date</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>cited_patent_kind</td><td>cited_patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>cited_patent_number</td><td>cited_patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>cited_patent_sequence</td><td>cited_patents</td><td>string</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>cited_patent_title</td><td>cited_patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_city</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_country</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_first_name</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_id</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_last_name</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_city</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_country</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_latitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_longitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_state</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_latitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_longitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_num_patents</td><td>inventors</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_state</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_years_active</td><td>inventors</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_action_date</td><td>ipcs</td><td>date</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_class**</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_classification_data_source</td><td>ipcs</td><td>string</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_classification_value</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_main_group</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_num_assignees</td><td>ipcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_num_inventors</td><td>ipcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_section</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_sequence</td><td>ipcs</td><td>integer</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_subclass</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_subgroup</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_symbol_position</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_version_indicator</td><td>ipcs</td><td>date</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_years_active</td><td>ipcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_abstract</td><td>patents</td><td>full text</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_country</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_date</td><td>patents</td><td>date</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_firstnamed_city</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_firstnamed_country</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_firstnamed_latitude</td><td>patents</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_firstnamed_longitude</td><td>patents</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_firstnamed_state</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_id</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_kind</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_num_cited_by_us_patents</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_num_combined_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_num_foreign_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_num_us_application_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_num_us_patent_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_num_claims</td><td>patents</td><td>integer</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_number</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_title</td><td>patents</td><td>full text</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>patent_type</td><td>patents</td><td>string</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>uspc_mainclass_id</td><td>uspcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_mainclass_title</td><td>uspcs</td><td>full text</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_num_assignees</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_num_inventors</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_num_patents</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_sequence</td><td>uspcs</td><td>integer</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_subclass_id</td><td>uspcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_subclass_title</td><td>uspcs</td><td>full text</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_years_active</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>

</table>

<table>
<tr><td>*</td> <td>= unique identifier</td></tr>
<tr><td>**</td> <td>= not yet implemented</td></tr>
</table>


## <a name="inventors_query"></a> Inventors Query

<code>***GET*** /querymodule/v0/inventors/query?q{,f,o,s}</code>

<code>***POST*** /querymodule/v0/inventors/query</code>

This will search for inventors matching the query string (`q`) and returning the data fields listed in the field string (`f`) sorted by the fields in the sort string (`s`) using options provided in the option string (`o`).

The HTTP GET request method is the preferred access mechanism; however when the query parameters exceed a reasonable size (around 2,000 characters), then the POST method can be used. When using the POST method, the query parameters should be embedded within a JSON string within the request body.

<table>

<tr><td>Name</td><td>Description</td><td>Details</td></tr>

<tr>
<td><code>q</code></td>
<td>JSON formatted object containing the query parameters. See below for details on formatting this object.</td>
<td>string, required <br/> example: <code>{"inventor_last_name":"Whitney"}</code></td>
</tr>

<tr>
<td><code>f</code></td>
<td>JSON formatted array of fields to include in the results. If not provided, defaults to the set of fields used in the query parameter.</td>
<td>string, optional <br/> example: <code>["patent_number", "date"]</code></td>
</tr>

<tr>
<td><code>s</code></td>
<td>JSON formatted array of objects to sort the results. If not provided, defaults to the unique, internal inventor identifier.</td>
<td>string, optional <br/> example: <code>[{"inventor_last_name":"desc"}]</code></td>
</tr>

<tr>
<td><code>o</code></td>
<td>JSON formatted object of options to modify the query or results. Available options are:
<ul>
<li>page &mdash; return only the Nth page of results. Defaults to 1.</li>
<li>per_page &mdash; the size of each page to return. Defaults to 25.</li>
</ul>
</td>
<td>string, optional <br/> example: <code>{"page": 2, "per_page": 50}</code></td>
</tr>

<tr>
<td><code>format</code></td>
<td>Specifies the response data format. If not provided, defaults to JSON. Available options are:
<ul>
<li>json</li>
<li>xml</li>
</ul>
</td>
<td>string, optional <br/> example: <code>format=xml</code></td>
</tr>

</table>

An example of a complete API call using the GET verb is:

    https://api.patentsview.org/api/v0/inventors/query?q={"inventor_last_name":"Young"}&f=["inventor_id","inventor_last_name","inventor_first_name","patent_number","patent_date"]

An example of the equivalent API call using the POST verb is:

    https://api.patentsview.org/api/v0/inventors/query

with the body containing:

    {"q":{"inventor_last_name":"Young"},"f":["inventor_id","inventor_last_name","inventor_first_name","patent_number","patent_date"]}

### <a name="inventor_field_list"></a> Inventor Field List

<table>

<tr><th>API Field Name</th><th>Group</th><th>Type</th><th>Query</th><th>Return</th><th>Sort</th></tr>

<tr><td>app_country</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_date</td><td>applications</td><td>date</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_id</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_number</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_type</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_city</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_country</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_first_name</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_id</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_last_name</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_city</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_country</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_latitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_longitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_lastknown_state</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_latitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_longitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_num_patents</td><td>assignees</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_organization</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_state</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_type</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_years_active</td><td>assignee</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_city</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_country</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_first_name</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_id</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_last_name</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_lastknown_city</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_lastknown_country</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_lastknown_latitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_lastknown_longitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_lastknown_state</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_latitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_longitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_num_patents</td><td>inventors</td><td>integer</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_state</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_years_active</td><td>inventors</td><td>integer</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>ipc_action_date</td><td>ipcs</td><td>date</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_class**</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_classification_data_source</td><td>ipcs</td><td>string</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_classification_value</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_main_group</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_num_assignees</td><td>ipcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_num_inventors</td><td>ipcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_section</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_sequence</td><td>ipcs</td><td>integer</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_subclass</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_subgroup</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_symbol_position</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_version_indicator</td><td>ipcs</td><td>date</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_years_active</td><td>ipcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_abstract</td><td>patents</td><td>full text</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_country</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_date</td><td>patents</td><td>date</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_city</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_country</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_latitude</td><td>patents</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_longitude</td><td>patents</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_state</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_id</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_kind</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_cited_by_us_patents</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_combined_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_foreign_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_us_application_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_us_patent_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_claims</td><td>patents</td><td>integer</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_number</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_title</td><td>patents</td><td>full text</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_type</td><td>patents</td><td>string</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_mainclass_id</td><td>uspcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_mainclass_title</td><td>uspcs</td><td>full text</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_num_assignees</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_num_inventors</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_num_patents</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_sequence</td><td>uspcs</td><td>integer</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_subclass_id</td><td>uspcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_subclass_title</td><td>uspcs</td><td>full text</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_years_active</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>

</table>

<table>
<tr><td>*</td> <td>= unique identifier</td></tr>
<tr><td>**</td> <td>= not yet implemented</td></tr>
</table>

## <a name="assignees_query"></a> Assignees Query

<code>***GET*** /querymodule/v0/assignees/query?q{,f,o,s}</code>

<code>***POST*** /querymodule/v0/assignees/query</code>

This will search for assignees matching the query string (`q`) and returning the data fields listed in the field string (f) sorted by the fields in the sort string (`s`) using options provided in the option string (`o`).

The HTTP GET request method is the preferred access mechanism; however when the query parameters exceed a reasonable size (around 2,000 characters), then the POST method can be used. When using the POST method, the query parameters should be embedded within a JSON string within the request body.

<table>

<tr><th>Name</th><th>Description</th><th>Details</th></tr>

<tr>
<td><code>q</code></td>
<td>JSON formatted object containing the query parameters. See below for details on formatting this object.</td>
<td>string, required <br/> example: <code>{"inventor_last_name":"Whitney"}</code></td>
</tr>

<tr>
<td><code>f</code></td>
<td>JSON formatted array of fields to include in the results. If not provided, defaults to the set of fields used in the query parameter.</td>
<td>string, optional <br/> example: <code>["patent_number", "date"]</code></td>
</tr>

<tr>
<td><code>s</code></td>
<td>JSON formatted array of objects to sort the results. If not provided, defaults to the unique, internal inventor identifier.	string, optional</td>
<td>example: <code>[{"assignee_organization":"desc"}]</code></td>
</tr>

<tr>
<td><code>o</code></td>
<td>JSON formatted object of options to modify the query or results. Available options are:
<ul>
<li>page – return only the Nth page of results. Defaults to 1.</li>
<li>per_page – the size of each page to return. Defaults to 25.</li>
</ul>
</td>
<td>string, optional <br/> example: <code>{"page": 2, "per_page": 50}</code></td>
</tr>

<tr>
<td><code>format</code></td>
<td>Specifies the response data format. If not provided, defaults to JSON. Available options are:
<ul>
<li><code>json</code></li>
<li><code>xml</code></li>
</ul>
</td>
<td>string, optional <br/> example: <code>format=xml</code></td>
</tr>

</table>

An example of a complete API call using the GET verb is:

    https://api.patentsview.org/api/v0/assignees/query?q={"_begins":{"assignee_organization":"Race"}}&f=["patent_number","patent_date","assignee_organization","assignee_id"]

An example of the equivalent API call using the POST verb is:

    https://api.patentsview.org/api/v0/assignees/query

with the body containing:

    {"q":{"_begins":{"assignee_organization":"Race"}},"f":["patent_number","patent_date","assignee_organization","assignee_id"]}

### <a name="assignee_field_list"></a> Assignee Field List

<table>

<tr><th>API Field Name</th><th>Group</th><th>Type</th><th>Query</th><th>Return</th><th>Sort</th></tr>
<tr><td>app_country</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_date</td><td>applications</td><td>date</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_id</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_number</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>app_type</td><td>applications</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_city</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_country</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_first_name</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>assignee_id</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_last_name</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_lastknown_city</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_lastknown_country</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_lastknown_latitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_lastknown_longitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_lastknown_state</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_latitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_longitude</td><td>assignees</td><td>float</td><td>N</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_num_patents</td><td>assignees</td><td>integer</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_organization</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_state</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_type</td><td>assignees</td><td>string</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>assignee_years_active</td><td>assignee</td><td>integer</td><td>Y</td><td>Y</td><td>Y</td></tr>
<tr><td>inventor_city</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_country</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_first_name</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_id</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_last_name</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_city</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_country</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_latitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_longitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_lastknown_state</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_latitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_longitude</td><td>inventors</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_num_patents</td><td>inventors</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_state</td><td>inventors</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>inventor_years_active</td><td>inventors</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_action_date</td><td>ipcs</td><td>date</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_class**</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_classification_data_source</td><td>ipcs</td><td>string</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_classification_value</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_main_group</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_num_assignees</td><td>ipcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_num_inventors</td><td>ipcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_section</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_sequence</td><td>ipcs</td><td>integer</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_subclass</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_subgroup</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_symbol_position</td><td>ipcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_version_indicator</td><td>ipcs</td><td>date</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>ipc_years_active</td><td>ipcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_abstract</td><td>patents</td><td>full text</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_country</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_date</td><td>patents</td><td>date</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_city</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_country</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_latitude</td><td>patents</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_longitude</td><td>patents</td><td>float</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_firstnamed_state</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_id</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_kind</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_cited_by_us_patents</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_combined_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_foreign_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_us_application_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_us_patent_citations</td><td>patents</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_num_claims</td><td>patents</td><td>integer</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_number</td><td>patents</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>patent_title</td><td>patents</td><td>full text</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>patent_type</td><td>patents</td><td>string</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_mainclass_id</td><td>uspcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_mainclass_title</td><td>uspcs</td><td>full text</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_num_assignees</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_num_inventors</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_num_patents</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_sequence</td><td>uspcs</td><td>integer</td><td>N</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_subclass_id</td><td>uspcs</td><td>string</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_subclass_title</td><td>uspcs</td><td>full text</td><td>Y</td><td>Y</td><td>N</td></tr>
<tr><td>uspc_years_active</td><td>uspcs</td><td>integer</td><td>Y</td><td>Y</td><td>N</td></tr>

</table>

<table>
<tr><td>*</td> <td>= unique identifier</td></tr>
<tr><td>**</td> <td>= not yet implemented</td></tr>
</table>
