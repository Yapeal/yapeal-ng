# Bootstrap

This file's only job is to find and insure Composer's class autoload has been
started. Below is a quick run through of how it goes about this.

  1. First thing it does is checks if the auto-loader class already exists or
  not and exits if it does.
  2. If the auto-loader does not exist it finds the Yapeal-ng base directory
  and normalizing it to Unix type path separators.
  3. Next it checks for a vendor/ directory in the path and shorten the path to
  the vendor/ parent directory if needed.
  4. It adds to the path the expected relative location of the Composer auto-loader.
  5. Temporarily turn off some error reporting.
  6. Try loading the auto-loader via include_once.
  7. Reset error reporting to old value.
  8. Unset all the global variables used up until this point to minimize
  clashes with any following code.
  9. If the auto-loader class still does not exist write an error message to
  either error or regular output, unset message variable, and return ignorable
  error code to caller.

Anyone is free to adapt this file to their own needs for their own projects.
Make sure if you do you update the copyright info etc.
