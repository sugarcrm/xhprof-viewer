# SugarCRM XHProf Viewer

SugarCRM XHProf viewer is an extended viewer based on the standard xhprof viewer by Facebook
that shows some additional information like sql and elastic queries, their timing and stack traces.

## Installation
* Download the latest .tgz package here: https://github.com/sugarcrm/xhprof-viewer/releases/latest
* Extract it to the web root of your web server
* By default the viewer is looking to /tmp/profile_files for xhprof data files. In order to change it create
a file `config_override.php` with the following content:
```php
<?php
$config['profile_files_dir'] = '<PROFILE FILES LOCATION>';
```

## Contributing
Everyone is welcome to contribute to this project! If you make a contribution, then the [Contributor Terms](CONTRIBUTOR_TERMS.pdf) apply to your submission.

Please check out our [Contribution Guidelines](CONTRIBUTING.md) for helpful hints and tips that will make it easier for us to accept your pull requests.

-----
Copyright (c) 2016 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
