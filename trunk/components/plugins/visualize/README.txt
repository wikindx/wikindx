********************************************************************************
**                                 Visualize                                  **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5.8.3 and up.
Results may be unexpected if used with a lower version.

Create various visualizations from WIKINDX data.

Makes use of JpGraph: https://jpgraph.net/

The module registers itself in the 'plugin1' menu.

Unzip this file (with any directory structure) into plugins/visualize/.
Thus, plugins/visualize/index.php etc.

********************************************************************************

CHANGELOG:

2021-05-15 : FEA : add visualization of total resource views per month and per year.
2021-04-18 : CHG : change of the compatibility version (10) (removal of the database prefix).
2020-12-21 : CHG : make PHP includes independent of the web server layout (#244).
2020-12-21 : FIX : display error.
2020-12-21 : CHG : handle multiple tabs.
2020-12-21 : CHG : update jpGraph from 4.2.10 to 4.3.4.
2020-12-21 : CHG : reformat source code to the prefered if/then/else style.
2020-07-11 : CHG : relicencing under ISC License terms.

v1.5, 2020
1. WIKINDX compatibility version 7.

v1.4, 2020
1. Add documentation.

v1.3, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.
2. Fix CSS/JS includes.

v1.2 ~ October 2019
1.  Update of jpGraph to 4.2.10

v1.1 ~ October 2019
1.  Initial release
