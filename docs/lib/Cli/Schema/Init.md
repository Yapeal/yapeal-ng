# Yapeal-ng Schema Initialization Command

Command: `yc S:I`
Alias: `yc D:I`

Used normally after initial install of Yapeal-ng to create the database
and tables it needs.

## Explanation

This will be a brief explanation to give you some idea what this command
does.

### Configuration file(s) and the `--config` Option

The command starts out by finding the normal configuration file(s). See
[Configuration Files](../../../ConfigurationFiles.md) for more
information about them. If given the `-c`/`--config` option it will
also use the settings it finds in that file to set and/or override any
settings from the other config file(s).

### Addition Configuration file(s) settings

Since the command receives all the same settings that the rest of
Yapeal-ng does it will have all the normal logging etc you might expect
to have happen else where in Yapeal-ng.

### `-d, -o, -p, -t, -u` Database Options

It will then use any of these provided command line options to do
additionally setting or overriding of those the settings already found
in the config file(s) above.

### Database Connection

Using the settings it has from above the command tries connecting to the
database.

After connecting to the database server it will start looking for the
`Create*.sql` files in `lib/Sql/*/` directories and process them. The
order it processes the directories in is 'Database', 'Util', 'Account',
'Char', etc.

_NOTE:_
    In order to drop the database and all the tables and their data
    you must use the --dropSchema option and interactively confirm the
    action. This option is ignored when interact is not possible.

The first one `Database/CreateDatabase.sql` of course creates the
database and the others files do the same for the tables in each
section. The `CreateCustomTables.sql` is of special note in that it's
the only one that doesn't exist by default. It was added to make it
easier during development on Yapeal-ng to add back in the test API keys we
use. You can use it in your application to do the same thing but what
might be more useful is you can add any other tables your application
might need instead so they can be create at the same time as Yapeal's
tables.

In all of the sql files when they are load any `{database}` and
`{table_prefix}` strings are replaced with the value from the settings
and then it's broke up into individual SQL statements and sent to the
database server. Any bad statements will cause it to abort processing
all remaining SQL and return what is hopefully a useful error message.
