# Configuration Files

Yapeal-ng has a sophisticated system of finding useful configurations
files that at first can seem overwhelming without explanation. I will
try to clarify here how it works by going over them all in the order
Yapeal-ng will process them.

Except if stated otherwise all paths are relative to the base directory
where Yapeal-ng is install.

## lib/Configuration/yapeal_defaults.yaml

The name pretty much tells it all here. It contains the most complete
set of required and optional defaults that all the other files will be
adding to or overriding as needed. This file MUST BE consider read only
as any modifications by application developers is not supported.

## config/yapeal-example.yaml

Yapeal-ng does NOT process this file it is provided as a commented
reference file for application developers use. One common way to use it
is to copy and rename it into one of the following file locations that
Yapeal-ng does looks for its settings and then edit them as needed.

## config/yapeal.yaml

This file is normally only used and seen by developers of Yapeal-ng
itself. Application developers SHOULD NOT be using this file as would be
under the vendor/ directory which is considered read only. If you are
manually install Yapeal-ng from a zip file into your application for
some reason you might try using this file but installing from a zip file
WOULD BE consider unsupported since using composer is vastly superior.
The next file is better suited for application developers and is fully
supported.

## .../config/yapeal.yaml

The file Yapeal-ng looks for next is consider optional internally just
as are all configuration files. Unlike the last two files where the
actual location is known because of the direct relative path this one is
not. Yapeal-ng decide to look for this file based on if it finds itself
some where under a vendor/ directory. Normally when you add Yapeal-ng to
your application's composer.json file and give a `composer up`
command it'll download Yapeal-ng and all the other packages into a
vendor/ directory at the same level as the composer.json file. So
Yapeal-ng ends up being in
`/path/to/your/amazing/app/vendor/yapeal/yapeal-ng/` and will look
for a `/path/to/your/amazing/app/config/yapeal.yaml` file to load
additional settings from. So as long as you have this path and file in
your application you can put any Yapeal-ng settings here and it will
automatically find them. This is the recommended way for an application
to provide any custom settings need for Yapeal-ng.

## -c/--configFile

If you are using any of the commands from `bin/yc` including
'Yapeal:AutoMagic' you can use the `-c`/`--configFile` option to have it
process any accessible Yaml file on the local system.

## Summary

Just to make it clear how all of the config files would look in a
directory list here a simplified one for you using a typical directory
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
                cache/
                config/
                    ...
                    yapeal.yaml
                    yapeal-example.yaml
                docs/
                lib/
                    Configuration/
                        ...
                        yapeal_defaults.yaml
```
