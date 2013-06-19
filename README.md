StatusWolf
==========

Configurable operations dashboard designed to bring together the
disparate datasources that operations teams need to manage and present
them in a flexible and beautiful way.

The current version provides ad-hoc searching of data, with OpenTSDB as
the initially available data source. It allows for searching multiple
metrics, week-over-week display, projection modelling based on historical
data, and anomaly detection in the current data.

## Ad-Hoc Search Interface
![Ad-Hoc Search](https://cloud.box.com/s/a8aqjs34mpa65de6bp90)

## Standard Graph with Right Axis
![Standard Graph](https://cloud.box.com/s/t1v30ygv65jq6uismjqu)

## Projection Model with Anomaly Detection
![Anomaly Detection](https://cloud.box.com/s/wybbtdmr09qee8gibicf)

## Requirements
### PHP
Pear Auth
Pear Log

    pear install Auth
    pear install Log

If you wish to authenticate to a local database, you'll also need
Pear MDB2 and the appropriate driver, e.g MDB2#mysqli

    pear install MDB2
    pear install MDB2#mysqli

## StatusWolf uses:
Bootstrap & Bootstrap Date/Time Picker (customized for StatusWolf)
[http://twitter.github.io/bootstrap/]

JQuery
[http://jquery.com]

JQuery Autocomplete plugin
[http://www.devbridge.com/projects/autocomplete/jquery/]

Dygraphs (customized for StatusWolf)
[http://dygraphs.com]

Date.js
[http://www.datejs.com]

KLogger
[https://github.com/katzgrau/KLogger]

PolynomialRegression
[http://www.drque.net/Projects/PolynomialRegression/]