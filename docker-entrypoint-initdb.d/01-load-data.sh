#!/usr/bin/env bash
for filename in /cache-data/*.json
do
mongoimport -d pv_webapi_cache -c cache --file $filename --jsonArray
done