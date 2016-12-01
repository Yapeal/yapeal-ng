# Configuration Files

Yapeal-ng has a sophisticated system of finding useful configurations
files that at first can seem overwhelming without explanation. I will
try to clarify here how it works by going over them all in the order
Yapeal-ng will process them. Note that all the config files are considered
optional and will be silently ignored if they can't be found.

Except if stated otherwise all paths are relative to the base directory
where Yapeal-ng is install.

## lib/Configuration/YapealDefaults.yaml

The name pretty much tells it all here. It contains the most complete
set of required and optional defaults that all the other files will be
adding to or overriding as needed. This file __MUST BE__ consider _read only_
as any modifications by application developers are likely to cause issues and
so is not supported.

## config/yapeal-example.yaml

Yapeal-ng does __NOT__ process this file it is provided as a commented
reference file for application developers' use. One common way to use it
is to copy and rename it into one of the following file locations that
Yapeal-ng does looks for its settings and then edit the settings there as
needed.

## The vendor/ directory effect

Yapeal-ng as part of its normal configuration process tries to determine if it
is installed under a vendor/ directory or not. If Yapeal-ng is under a vendor/
directory it assumes you are using Composer and the directory should be
consider _read only_ by Yapeal-ng. When vendor/ is detected its parent
directory is found and added as a setting. Additionally one of the following
two configuration file locations are added to the list of possible config files
to get settings from.

## config/yapeal.yaml

This file is only used and seen by developers of Yapeal-ng itself. Yapeal-ng
will only look for this file if it does _not_ finds itself under a vendor/
directory. The next file is better suited for application developers and is
fully supported when Yapeal-ng is required through Composer.

## .../config/yapeal.yaml

This optional file is added to the possible config file list only if Yapeal-ng
finds itself under a vendor/ directory. Unlike the last two files where the
actual location is known because of the direct relative path they have this one
is not. A little background info about how Composer works is probably helpful
here. Normally when you add Yapeal-ng to your application's composer.json file
and give a `composer up` command it'll download Yapeal-ng and all the other
packages into a vendor/ directory at the same level as the composer.json file.
So Yapeal-ng ends up being in
`/path/to/your/amazing/app/vendor/yapeal/yapeal-ng/` and will look
for a `/path/to/your/amazing/app/config/yapeal.yaml` file to load
additional settings from. So as long as you have this path and file in
your application you can put any Yapeal-ng related settings here and it will
automatically find them. This is the recommended way for an application
to provide any custom settings it needs for Yapeal-ng.

## -c/--configFile

If you are using any of the commands from `bin/yc` including
'Yapeal:AutoMagic' you can use the `-c`/`--configFile` option to have it
process any accessible Yaml file on the local system. Note that these settings
will not be overwritten by any matching settings in the other config files.

## `bin/yc` command options

Some of the command found in `bin/yc` have options for things like setting the
schema (database) user name or password for example. These command line options
have the highest priority in Yapeal-ng and will override the same setting(s)
found in any of the config files discussed above.

## Yapeal.Config.configFile special setting

This special setting can not be set through any of the above config files and
would have no effect on Yapeal-ng if found in one of them. The only time it is
looked for in Yapeal-ng is in `lib/Cli/ConfigWiring::wire()` coming from the
`ContainerInterface` object it is given. This has been added to give the 
application programmer a way through a code of setting a different location
than using `amazingApp/config/yapeal.yaml`. For example instead of using in the
more traditional cron job or scheduled task

## Summary

Just to make it clear how all of the config files would look in a
directory list here a simplified one for you showing a typical directory
structure for composer projects:

```
amazingApp/
    ...
    config/
        yapeal.yaml
    src/
    ...
    vendor/
        bin/
        ...
        yapeal/
            yapeal-ng/
                bin/
                ...
                config/
                    ...
                    yapeal.yaml
                    yapeal-example.yaml
                docs/
                lib/
                    Configuration/
                        ...
                        YapealDefaults.yaml
                ...
        ...
    ...
```
