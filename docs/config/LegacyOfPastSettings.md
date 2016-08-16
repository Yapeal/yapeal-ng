# Legacy Of Past Settings

Yapeal-ng through it's inherited history with the original Yapeal has
had a very varied configuration file format. Yapeal started out with an
'ini' file which later had a 'XML' file added to it for logging
settings, etc. In and around the application visible formats there has
been some exploring of other formats like Json which weren't made public
in many case. In the end of course we get to where it is now using Yaml
in both the legacy Yapeal and current Yapeal-ng with no plans to change
the format again since with a few custom enhancements that are used now
it can express everything that IMHO anyone could need in a config file
and many things you probably shouldn't use one to do.

This history has lead in many cases of some very simple translations
from one format to another with minimal enhancement to the structuring
of settings visible to application developer. This has had it's pluses
of course making it usually just a matter of a little copying and
pasting between the old and new config file in most cases but there are
a few minuses as well.

On the minus side since in many ways everything has stayed much like the
original 'ini' file to be more BC friendly it has limited how much depth
that has been offered to the application developer to access parts of
Yapeal-ng to customize it to better integrate with their application and
needs.
