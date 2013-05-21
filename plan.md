# Refactor code to rename project to StatusWolf

* &check; <del>Create new git repository for StatusWolf (github.com/box private repo?)</del>
* Move files to StatusWolf project as classes are implemented
	* Change file names
	* Change directory structure
	* Update all code to remove references to Cumulus, replace with SW
* Remove legacy Cumulus2 repos

# Phase 1: Ad Hoc search interface

## 1a: Implement basic session management

* <span style="color: red;">&otimes;</span> <del>http://scache.nanona.fi/introduction.html</del>
* Integrate config class with session management
* Implement session config caching via Redis
	* Possible to implement db clustering for use with multiple dashboard servers? e.g. each dashboard also has a
    db instance so if your session is redirected from one dashboard to another your session data is still
    available.
* Add authentication classes - LDAP, local, MySQL, Redis
	* App will require Redis for caching, so default auth db should probably be in Redis as well.

## &check; <del>1b: Add class autoloader</del>

* <del>look at ops-php-anthology, Bart, Cake, etc. - implement best method</del>

## 1c: Build Router class to parse URL path

* Document path-to-Controller mapping
* Create Router class to implement that mapping
* index.php exists solely to hand off to the Router
	* Check session data in index.php or at Router to load constants and base config?

## 1d: Ad-hoc search widget, week-over-week or anomaly graphs

* Build with objective to interface with multiple datasources, not just OpenTSDB
* First datasource will be OpenTSDB
* Option to generate week-over-week or anomaly detection graphs for a metric
	* Logic to limit graphs to single series (or may 2 to 3 series') to keep graphs
   readable

# Implement Prod/Dev branching for git repository

* http://nvie.com/posts/a-successful-git-branching-model/
* Create first alpha version master branch (v.01?)

## 1e: Multiple metric queries

* Extend ad-hoc interface to allow searching multiple metrics

## 1f: Optimizations for OpenTSDB searching

* Asynchronous javascript queries
	* Find method to kill in-progress queries if the options change
* Break up large queries (> 4 hours?) into 1-hour chunks, live updates to the
  graph as data comes in.
