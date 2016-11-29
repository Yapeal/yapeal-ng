# Deeper Depths Of Configuration

In this document I'll try to detail the changes between
[Yapeal's settings](https://github.com/Yapeal/yapeal) and the new ones
found in Yapeal-ng. I'll try to go through things in the same order as
the sections appear in `config/yapeal.yaml` for Yapeal but since some of
them have been renamed, removed, or otherwise changed I may jump around
a little bit. BTW the title is a hint to a lot of the changes since many
of the old settings have homes in a deeper level of structure than seen
before.

## At The Top - Yapeal:

Nothing really new here both have the same Yapeal 'root' for lack of a
better word that everything else falls under. There is one default
setting that was added here to help track which version of Yapeal you
have installed much like was tried a few time in the distant past when
Yapeal used an `ini` file for config but the setting was later dropped.
Not really something you'll change so on to the Cache section.

### Yapeal:Cache Section

This section has been completely deprecated and is no longer used in
Yapeal-ng. That does not mean that caching has been removed from it just
that all the settings that were here now have replacements which
actually give a higher level of control and in some cases along with
some other changes will allow dynamically changes to them as well if for
some reason you find that useful in your application.

#### Yapeal:Cache:cacheDir setting

This setting was always used with caching of the Eve Api XML files in
the filesystem.  In Yapeal-ng you'll find it under
Yapeal:Filesystem:Cache:cacheDir now.

#### Yapeal:Cache:fileSystemMode setting

This setting is also moved to Yapeal:Filesystem:Cache: but in an
enhanced form with separate retrieve and preserve settings. Some might
question the need to split this into two settings but think of the
`yc N:C` where you want to force the retrieve to the Eve API server and
additionally only preserve to the filesystem cache. Before I needed to
do some tricks in how I wired up stuff and needed to add an extra cache
step to the overall processing just so Yapeal didn't try doing the DB
stuff. Now along with the new Yapeal:EveApi:Cache:preserve and
Yapeal:Network:Cache:retrieve settings this and other things have become
much easier to do without any of the mostly duplicate code that was
required before.

### Yapeal:Database Section

Everything here can be found in the Sql section for Yapeal-ng but in a
deeper and changed form. Something I have started doing is try changing
Yapeal-ng from being a MySql only solution for PHP but to a more
multiple database platform one. This is somewhat of a painful process.
Even though I used things like PDO, using mostly SQL-92 standard for the SQL,
and some other stuff to try isolating the DB interact from the rest of the
logic Yapeal-ng never the less ended up being more MySql only that it should
have been.

I'm writing this right after I made a lot of the changes to this section
in the config to start the painful process of correcting things so other
platforms can be used. In fact a part of the reason I decided I needed
to do some documentation was these changes. I'm going to cover the
settings in groups starting with ones that are just a straight forward
move from the old Database section to the new Sql section followed by
the now legacy backwards compatibility settings then finally the current
and hopefully completely future proofed new per platform way of doing
things. Depending on when you are reading this the legacy settings may
have already been completely deprecated. I'll try to remember to come
back and add a note here when I do.

#### Yapeal:Database:platform setting

This is the same as before the only different is that where before it
could have been left out really and not changed more than ten lines of
code in all of Yapeal it now is used in a great many places and has
become critical to how Yapeal-ng does things so it can be be used with
multiple database platforms. This is the only directly copied setting as
all the others are either legacy settings or only available as
current/future settings.

#### Yapeal:Database:database setting

This is directly copied to Yapeal:Sql:database but is considered a
legacy setting maintained temporarily to allow an easier transition to
the per platform settings. The replacement mysql platform setting for
this is Yapeal:Sql:Platforms:mysql:schema. The default value for schema
is `{Yapeal.Sql.database}` allowing the legacy BC stuff to happen but it
is strongly suggested you use the new setting to save some unneeded
indirection. I'll try to better explain the reason for the changes here.
There are two separate things going on here with the name change
and the per platform stuff as well. I'll start with the name change
first.

To understand the name change you need to understand that what everyone
thinks of and calls a database is known as a schema in things like the
SQL-92 standard. There are a couple of additional layers above as well
but really don't matter for us here or in most case to the platform
developers as well since they have been largely ignored beyond some of
the clustering stuff which though using the same naming don't really
relate at all. In MySql the schema and database commands are just
aliases from one to the other. Other platforms have either done the same
thing or where they are different many people have just ignored schema
and used the non-standard database instead. In the past Yapeal and
Yapeal-ng have done this as well. Now with trying the transition to
multiple platform support I've decided that schema is better as slowly
but surely all the platforms seem to be moving towards the standards and
the use of schema.

The per platform part is basically to allow for settings that are need
or very useful to have but don't apply at all or well to other platforms
to have a place to be plus it it allows you to have settings for multiple
platforms ready to use and by just changing the platform setting you
can switch database back ends. The last reason it probably more useful
to me for testing while developing Yapeal-ng than for any application
developer but there maybe a use case there as well for someone else.

#### Yapeal:Database:hostName setting

Direct copy into Yapeal:Sql:hostName for legacy BC use but you should
update to the Yapeal:Sql:Platforms:mysql:hostName setting instead. The
value here currently also point to the legacy BC setting.

#### Yapeal:Database:password setting

Direct copy into Yapeal:Sql:password for legacy and you should replace
the default value instead as above. Interesting thing to note is that
not all of the platforms that PDO supports have password or users. The
most common example would be SQLite since it's usually just a local
file somewhere and has no need for a password.

#### Yapeal:Database:userName setting

Same as password above.

#### Yapeal:Database:tablePrefix setting

Same as others above

#### Yapeal:Database:engine setting

Other database platforms only have one 'engine' unlike MySql which lets
you switch them out. As I'm assume few people ever used this setting as
well it does not have a legacy BC value or setting.

#### Yapeal:Database:class setting

This setting is another one few if any people will have tried changing
but on the off chance someone did there is no legacy BC setting and it
was moved to Yapeal:Sql:Handlers:connection. All of the old bare class
references like this one have been move under new per section Handlers
'subsections' for lack of a better word.

#### Yapeal:Database Summary

So that covers all of the moved only, moved with legacy, and move with
no legacy settings but you will find there are a few additional settings
that have been added and the important ones for application developers
will be covered back in [Configuring Yapeal](docs/ConfiguringYapeal.md).

### Yapeal:Error Section

No real changes here except for moving the bare class reference to
Yapeal:Error:Handlers:class and exposing more of the inner works of
Yapeal-ng under Handlers as well to allow additional customizing (Ways
for developers to shoot themselves in the foot ;-) if they choose to).

### Yapeal:Log Section

Like Error section above not any changes here but the addition of
Handlers 'subsection' and the moving of class setting under it.

### Yapeal:Network Section

Again not a lot of changes that effect most application developers just
the same exposing more of the previously internal stuff to transfer more
control to the configuration instead of hiding it away in some buried
code inside Yapeal-ng like was done in Yapeal. The main
[Configuring Yapeal](docs/ConfiguringYapeal.md) should cover everything
here for you.

## Sections Wrap Up

So that the end of all the sections and settings that were available in
Yapeal and the changes regarding them in Yapeal-ng. If you have been
exploring on your own or read the main configuring docs you may have
realized that there are several additional sections to Yapeal-ng's
config file that aren't covered here. As stated this document is meant
only to help application developers get up to speed with the changed to
the settings between Yapeal and Yapeal-ng and the
[Configuring Yapeal](docs/ConfiguringYapeal.md) docs should cover
everything else you should need to know about all the settings.

If you have any suggestions please let me know directly, or open an
issue, or even do a pull request on GitHub would be great and I'll have
a look at making the improvements to this and the other documents.
