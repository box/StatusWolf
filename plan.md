# Refactor code to rename project to StatusWolf

* &#10003; <del>Create new git repository for StatusWolf (github.com/box private repo?)</del>
* &#10003; <del>Move files to StatusWolf project as classes are implemented</del>
	* <del>Change file names</del>
	* <del>Change directory structure</del>
	* <del>Update all code to remove references to Cumulus, replace with SW</del>
* Archive and remove legacy Cumulus2 repos

# Phase 1: Ad Hoc search interface

## 1a: Implement basic session management

* <span style="color: red;">&otimes;</span> <del>http://scache.nanona.fi/introduction.html</del>
* &#10003; <del>Integrate config class with session management</del>
* &#10003; <del>Implement session config caching via MySQL</del>
* Implement session config caching via Redis
	* Possible to implement db clustering for use with multiple dashboard servers? e.g. each dashboard also has a
    db instance so if your session is redirected from one dashboard to another your session data is still
    available.
* Add authentication classes - &#10003; LDAP, local, &#10003; MySQL, Redis
	* App will require Redis for caching, so default auth db should probably be in Redis as well.

## &#10003; <del>1b: Add class autoloader</del>

* <del>look at ops-php-anthology, Bart, Cake, etc. - implement best method</del>

## 1c: Build Router class to parse URL path

* &#10003; <del>Document path-to-Controller mapping</del>
* &#10003; <del>Create Router class to implement that mapping</del>
* &#10003; <del>index.php exists solely to hand off to the Router</del>
* Check session data in index.php or at Router to load constants and base config?

## 1d: Ad-hoc search widget, week-over-week or anomaly graphs

* &#10003; <del>Build with objective to interface with multiple datasources, not just OpenTSDB</del>
* &#10003; <del>First datasource will be OpenTSDB</del>
* &#10003; <del>Option to generate week-over-week or anomaly detection graphs for a metric</del>
	* <del>Logic to limit graphs to single series (or may 2 to 3 series') to keep graphs
   readable</del>

# Implement Prod/Dev branching for git repository

* http://nvie.com/posts/a-successful-git-branching-model/
* Create first alpha version master branch (v.01?)

## 1e: Multiple metric queries

* &#10003; <del>Extend ad-hoc interface to allow searching multiple metrics</del>

## 1f: Saved searches

* Save ad-hoc searches and enable link sharing, send via email so others can see the graph
* Option to save shared searches to a master list other users can see

## 1g: Optimizations for OpenTSDB searching

* Asynchronous javascript queries
	* Find method to kill in-progress queries if the options change
* Break up large queries (> 4 hours?) into 1-hour chunks, live updates to the
  graph as data comes in.
