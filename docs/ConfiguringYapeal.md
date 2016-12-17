# Configuring Yapeal


Older versions of Yapeal-ng used a mix of 'ini' style and XML
configurations files. To make configuring Yapeal-ng easier than using
XML configuration files but allow more complex configuration structures
than 'ini' files allowed Yapeal-ng is now using an 'yaml' file.

You can think of [Yaml](http://www.yaml.org/) as a super-set of
[Json](http://www.json.org/) but made to be more human friendly. It also
was designed for things like configuration files where Json was created
for information transfer between computer applications like a web
browser and a web site.

NOTE:

    All of the following examples will be for Linux style command line
    interface. Windows user in most cases will simply need to change any
     '/' into '\' on paths if the command does NOT seem to work.

## Configuration files

Instead of having everything here about configuration files please read
[Configuration Files](config/ConfigurationFiles.md) which covers
where Yapeal-ng looks for them and it's processing order etc.

I've written a little about the history of configuration files and their
settings for those that might be interested. You can find it in
[Legacy Of Past Settings](config/LegacyOfPastSettings.md).

If you are a previous user of the original Yapeal project that Yapeal-ng
is meant to replace you'll want to check out
[Deeper Depths Of Configuration](config/DeeperDepthsOfConfiguration.md).
It should explain the settings changes you'll need to be aware of.

Next we go over the more interesting settings of the configuration an
application developer can or should change before using Yapeal-ng in
production with their application.

### Sql Section

NOTE:

    In the follow text I use the term 'schema' for what most people know
    as a database. In standards like SQL-92 the correct term is schema
    and I have chosen to use it as well here and most places throughout
    Yapeal-ng. Vendors are slowly move to this term also so expect to start
    seeing it more often.

Located in the `Sql` section of `yapeal.yaml` is where most of the
settings that need to be change can be found.

At the time I'm writing this Yapeal-ng is being changed from a MySQL
only library to something that can support additional platforms. For
the examples below I'll assume that the `platform` settings is it's
default value of `mysql`.

The next two settings we will talk about are the `userName` and
`password` ones. Normally the user that Yapeal-ng uses only needs
typical insert, update, delete, and select access to the data in the
schema tables but during the creation of the schema and it's tables
Yapeal-ng will need create and drop access to both as well. It is
recommended that the user added to the yapeal.yaml file only has the
table data access it needs during normal operation and that a separate
user be used only during creation which has the required additional
schema access. Later I'll show how to override the userName and
password given in the config file while creation of the database is
done.

Next we have the `schema` setting which gives the name of the schema
where Yapeal-ng will look for it's tables. This schema can contain
additional tables used else where in your application but you will need
to take care NOT to create tables that have the same names as the ones
Yapeal-ng uses to store the Eve API data or any of it's admin tables.

If you find there is a conflict between the table names of your
application and Yapeal-ng there is another setting called `tablePrefix`
that can be useful. Yapeal-ng will prefix the string from this setting
to all the table names for all of it's operations automatically for you.
If this setting is going to be used it must be done during schema
creation as well since the tables must be created with the prefixed
names.

I'll give an example here to make it easier to understand how the
settings work.

Let say you have a `config/yapeal.yaml` file that has these settings:

```yaml
Yapeal:
...
    Sql:
        platform: mysql
        Platforms:
            mysql:
                schema: yapeal-ng
                password: secret
                tablePrefix: ''
                userName: YapealUser
...
```

And you have the following schema table SQL:

```
CREATE TABLE "{schema}"."{tablePrefix}eveErrorList" (
    "errorCode" SMALLINT(4) UNSIGNED NOT NULL,
    "errorText" TEXT,
    PRIMARY KEY ("errorCode")
);
```

The resulting SQL will look something like this:

```
CREATE TABLE "yapeal-ng"."eveErrorList" (
    "errorCode" SMALLINT(3) UNSIGNED NOT NULL,
    "errorText" TEXT,
    PRIMARY KEY ("errorCode")
);
```

with the `{schema}` and `{tablePrefix}` replaced with the values. The
user name used for the connection will be `YapealUser` and the password
use will be `password`.

There are several other settings an application developer might need to
update as well in the SQL settings but they aren't typical used. For
more about them read the include comments in yapeal-example.yaml and if
you still need addition assistance just contact me through Github and
I'll be more than happy to help you figure out what settings you'll
need.

### Log Section

There are a couple settings here that as an application developer you'll
probably need to change. The first that MUST be changed when Yapeal-ng
is under the vendor/ directory is the `dir` setting. Normally this is
set to a `log/` directory inside of  Yapeal-ng itself and since anything
under `vendor/` should be _read only_ this is a bad place for it to be
writing a log file. Best thing to do is set it to the same directory you
use for logging errors in your application. In cases where your
application doesn't have a log directory you might redirect to a tmp
directory etc.

Another option if you already have PSR-3 compatible logging set up in
your application is to pass on the logging class to Yapeal-ng as well so
everything ends up being logged together. How to do so is beyond what I
will cover here but you can contact me directly for more information.

The only other setting anyone is likely to change here is the
`threshold` one. You may during development or at least during initial
deployment want to see some additional logging then switch to less verbose
level later. Main thing here is to make sure this setting is always
below the setting in `Yapeal.Error.threshold`. For example if the
setting in Error section is 400(ERROR) then the setting in Log must be
300(WARNING) or less.

Note that you can also change the log level temporarily use the
`-v` command line option. Using the option multiple times will increase
the detail seen. A single `-v` option is NOTICE, two is INFO, and three
gives full DEBUG. With no `-v` option you'll get the default WARNING.
You can also use the `-q` option to switch to ERROR but this may hide
problems and should especially not be used during development.

### Network Section

There are now several settings in the Network section which the comments
should explain for you. Most of them have to do with the User-Agent
header Yapeal-ng will use while connecting to the Eve API servers. There
is also one to change between the live server and the test server which
can be useful during testing when you want a little less variable data
source than what the live server provides.

## Command Line Tools

Originally the command line tools just did additional jobs outside of
Yapeal-ng's normal operations but as time has got on they have grown
both in complexity and numbers and are now more directly involved with
the addition of `yc Yapeal:AutoMagic` to replace the older
bin/yapeal.php script. All of these tools are accessed through `yc`
which is a Symfony console application so for those of you that have
used Symfony consoles before you can probably skip down a little bit to
the individual commands descriptions at this point but for those of you
that are new to Symfony console applications you might want to read this
very short intro to it.

### A Short Symfony Console Intro

Some of the most useful things that Symfony's console adds are standard
ways to find out about what namespaces and commands are available plus
get help about them. If you give no parameters or options it defaults to
give info about general options and a list of available namespaces and
their commands. Try `php vendor/bin/yc` to see an example. To see just
the commands in a singe namespace try using
`php vendor/bin/yc list Schema` for example. To see more help info
about any of the commands just use something like
`php yc help namespace:command`. For an example try
`php vendor/bin/yc help S:C`. This will give the help info for the
Schema:Creator command. Notice that usually you can shorten the
namespace and command to just the initial letter of each separated by a
':' like I did above. That the end of this short intro but it should be
enough to let you started using the Yapeal-ng console commands.


### Schema Creation Using `yc Schema:Creator`

Short form `yc S:C`.

_NOTE:_ This is for initial schema creation only. Use `yc S:U` command
which is explain later for updating the schema after updating Yapeal-ng
itself.

To keep the example simple I'll assume you have already set up
`config/yapeal.yaml` with the correct settings Yapeal-ng will need to
use during normal operation. I'll assume that the user does not have
`CREATE SCHEMA` and also may not have `CREATE TABLE` privileges. If
you looked at the help for the `yc S:C` command above you saw there are
`-u` and `-p` options that can be used. `-u` lets you set the user name
for the database connection and `-p` lets you set the password. Since
command line options override the settings from the configuration files
by simple added these options you can use a different user during
database initialization.

Here an example of doing that:

```
php vendor/bin/yc S:C -u root -p superSecret
```

### Schema Updating Using `yc S:U`

The command will look in the `lib/Sql/updates/` directory for any
`###.sql` files and compare them with the latest version it finds in the
`utilDatabaseVersion` table and apply any of the files with a newer
date-time stamp name. Note that this command needs `CREATE PROCEDURE`,
`DROP PROCEDURE` privileges in addition to the privileges like
`CREATE TABLE` used by `yc S:C`. It also need to be able to `CALL`
the procedures.

## Epilogue

Hopefully the above has been enough to get you start with using
Yapeal-ng. As always if you have additional questions you can ask on the
forum thread where I'm sure someone will be willing to help out.

If you have suggestions or ideas on improving these instructions please
let me know or since Yapeal-ng is hosted on GitHub just fork it, make
your changes and do a pull request and it'll be reviewed for inclusion
in future versions.
