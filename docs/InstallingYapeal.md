# Installing Yapeal

There are several ways to install Yapeal but in all cases you'll need to
have a copy of
[Composer](https://getcomposer.org/)
installed to install and update Yapeal-ng and it's dependencies.

NOTE:

    All of the following examples will be for Linux style command line
    interface. Windows user in most cases will simply need to change any
    '/' into '\' on paths if the command does NOT seem to work.

## Install - Composer Automated

Make sure you are in the base directory of your project where your
`composer.json` is and do:

```bash
$ composer require -o yapeal/yapeal-ng
```

After composer finishes Yapeal-ng should be in
`vendor/yapeal/yapeal-ng/`.

Next you will want to read the configure instructions in
[Configuring Yapeal](ConfiguringYapeal.md).

## Install - Composer Manually

If you are using Composer in your own project then add a require line
for Yapeal-ng to your `composer.json` file like any other package:

```json
{
    "require": {
        "yapeal/yapeal-ng": "dev-master"
    }
}
```

And do an update in Composer on command line:

```bash
$ composer update -o
```

Next you will want to read the configure instructions in
[Configuring Yapeal](ConfiguringYapeal.md).

## Install - Git

These instructions are only here to help new Yapeal-ng developers get
started it should NEVER be used by application developers or anyone else
that's not writing code in or hacking on Yapeal-ng itself.

You can clone Yapeal-ng using [Git](http://git-scm.com/) from the main
project page:

https://github.com/Yapeal/yapeal-ng

The following instruction assume you are manually running the commands
from a command line interface like Bash (Linux) or CMD (Windows).

An example Git clone command:

```bash
$ git clone https://github.com/Yapeal/yapeal-ng.git
```

This will create a `yapeal-ng/` directory in the current path where it
is run so make sure to `cd` to the path just above where you want
Yapeal-ng.

Now `cd` into the new directory with:

```bash
$ cd yapeal-ng
```

All the following instructions will be run from there.

After cloning the project the master branch is checked out by default so
if you are needing one of the other branches first you will wanted to
list the remote branches with:

```bash
$ git branch -r
```

Then you will need to checkout the correct branch with:

```bash
$ git checkout the-branch
```

Now you need to use Composer to install Yapeal-ng's dependencies by
running:

```bash
$ php /path/to/composer.phar install -o
```

This will create a vendor/ directory inside of Yapeal-ng and auto-update
Composer's auto-loader.

Next you will want to read the configure instructions in
[Configuring Yapeal](ConfiguringYapeal.md).

## Install - Zip (No Longer supported and removed)
