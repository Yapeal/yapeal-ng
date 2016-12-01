# Running Yapeal

Typically Yapeal-ng will be run from a cron job (Linux/Unix) or a scheduled
service (Windows). Below some basic instructions are given for setting it up
like this. Some additional ideas on other ways to integrate it with your
application code will also be given to help easy your way should you find they
are needed.

## Cron job (Linux/Unix)

These instructions assume the application you are developing will be either
directly running on your host machine or in a VM(Virtual Machine) that allows
you to add your own cron jobs either through direct edit of the cron file
or via crontab. I'll assume you've already read the cron and crontab man pages
and understand the basics about them and just give a basic example here you can
use as a template.

```bash
* * * * * php path/to/yapeal-ng/bin/yc Y:A &>/dev/null &
```

This will run the Yapeal: AutoMagic command using to default php in the
background with both the standard and error redirected to the null device.
You will probably want to add stuff to it to suppress getting e-mails every
minute from the system and to limit how many jobs you allow to run in parallel.

