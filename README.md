StatusWolf
==========

Configurable operations dashboard designed to bring together the
disparate datasources that operations teams need to manage and present
them in a flexible and beautiful way.

> ## Announcing StatusWolf Version 0.9
> 
> You've heard this before, but this is a significant update to the
> StatusWolf code - the PHP backend has been completely rewritten
> using the [Silex PHP framework](http://silex.sensiolabs.org/).
> 
> Functionally this release is targeted at being on par with the
> previous version (but better :-)). To that end there aren't any
> big new features, but there has been work at improving performance
> and usability.
> 
> * Various UI tweaks
> * [Changed the interaction model for the D3 graphs](http://blackops.io/blog/2014/05/solving-d3-graph-interaction-in-statuswolf/).
> * Implemented downsampling using the [Largest-Triangle-Three-Buckets algorithm](http://blackops.io/blog/2014/05/time-series-graphs-and-downsampling/).
>     * Improves performance of the graphs and allows for display and/or downloading of the full, non-downsampled dataset.
> * Reworked the javascript widget model to begin to allow implementation of other types of widgets.
> * Updated the authentication model to allow anonymous access to view dashboards.
> * Automated upgrade from previous versions, simply put the new version in
>   place, grab the dependencies with Composer, and go - the first time you
>   connect StatusWolf will recognize that the updated code is in place and
>   upgrade your configs and database for you.
>
> One other note on this version - Anomaly detection has been disabled...
> This is primarily for performance reasons, I haven't had the time yet
> to really tackle refactoring anomaly detection, and the performance on
> any dataset of more than a few minutes span is abysmal. I'm also looking
> into anomaly detection theory for time-series metrics and am finding that
> the traditional methods applies (which were used by StatusWolf) are
> not well suited for time-series metric data. Next task for StatusWolf
> is determining the best method to use and implementing it in a way
> that delivers acceptable performance.
>
> The adhoc search interface is still available, but the splash-screen
> giant button interface to take you there has been removed. It's been
> retained to support any saved search links you may have in docs, etc.,
> but as StatusWolf moves to a multi-data-source, multi-widget-type
> ops dashboard the adhoc interface will be deprecated.

## Installing StatusWolf
Read [Installing StatusWolf v0.9](https://github.com/box/StatusWolf/wiki/Installing-StatusWolf-v0.9) for a new install,
or [Upgrading from v0.8.x to v0.9](https://github.com/box/StatusWolf/wiki/Upgrading-from-v0.8.x-to-v0.9) for
upgrading.

## Copyright and License

Copyright 2014 Box, Inc. All rights reserved.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
