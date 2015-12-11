# SugarCRM XHProf Viewer

SugarCRM XHProf viewer is an extended viewer based on the standard xhprof viewer by Facebook
that shows some additional information like sql and elastic queries, their timing and stack traces.

## Installation
* Download the latest .tgz package here: https://github.com/sugarcrm/xhprof-viewer/releases/latest
* Extract it to the web root of your web server
* In `config.php` specify the directory where profile files are located

## Development
If you want to hack on this tool just clone the repo and run `npm install && bower install`.
