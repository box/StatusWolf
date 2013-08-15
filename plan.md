# Phase 1: Ad Hoc search interface

* Done

# Phase 2: Dashboard interface

* Done

# Other:

* Optimizations for OpenTSDB searching
    * Asynchronous javascript queries
	    * Find method to kill in-progress queries if the options change
    * Break up large queries (> 4 hours?) into 1-hour chunks, live updates to the
      graph as data comes in.
