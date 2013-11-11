StatusWolf
==========

> ## Version 0.8 now available
>
> This version brings some significant changes to StatusWolf, the
> biggest being the switch from the dygraphs.js library to D3.js
> for drawing the graphs. This change is to allow future development
> (coming soon) that utilizes many different graph types for data
> rather than just line graphs.
>
> Brief list of updates for version 0.8:
> * Switched to D3.js for graphing.
> * Mouse over names in the legend to highlight that line in the graph.
> * Click on name in the legend to hide that line in the graph,
>   click again to show it.
> * Alt-Click (Option-Click for OS X) a name in the legend to hide all
>   other lines on the graph.
> * Click and drag on the graph to zoom in, click once to zoom back out.
> * Anomaly detection has been moved out of StatusWolf proper to be
>   a separate PHP library (look for that to be open-sourced soon).
> * Can now specify 2 or 3 column layout for the dashboard interface, that
>   choice will be saved with the dashboard as you create and save new
>   dashboards or re-save existing dashboards.
> * OpenTSDB searching will now recognize tags specified in OpenTSDB
>   URL-style formet - e.g. 'metric.name{tag1=val,tag2=val}' in addition
>   to the space-delimited format - e.g 'metric.name tag1=val tag2=val'.
> * New function to set the tags for searches in all Graph Widgets on
>   a dashboard.
> * New function to add a tag (or tags) for the searches in all Graph
>   Widgets on a dashboard.
>
> For new installation details, see:
> [StatusWolf Installation](https://github.com/box/StatusWolf/wiki/Installation)
>
> For upgrades to existing installations, see:
> [Version 0.8 Upgrade Note](https://github.com/box/StatusWolf/wiki/Version-0.8-Upgrade-Note)

Configurable operations dashboard designed to bring together the
disparate datasources that operations teams need to manage and present
them in a flexible and beautiful way.

The current version provides ad-hoc searching of data, with OpenTSDB as
the initially available data source. It allows for searching multiple
metrics, week-over-week display, and anomaly detection in the current data.
