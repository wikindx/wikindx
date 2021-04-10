+++
title = "Release Notes"
date = 2021-01-29T00:08:41+01:00
weight = 4
disableToc = false
+++

***Focus**: bug fixes, maintenance*

## Important information

The storage (file to db) and format (php_serialize) of sessions change. All sessions will be lost when upgrading to this version.

## Bugs fixes

* Allow missing directory for SQL on upgrade (but not creation).

## Maintenance

* Use the php_serialize handler for session serialization which can managed corner cases.
* Store sessions in db [#274].
* Restore garbage collection of sessions (except during an upgrade).
* Remove the barrier against double session launching from plugins. The change of includes must have corrected this problem.
