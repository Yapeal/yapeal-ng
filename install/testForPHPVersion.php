#!/usr/bin/php -Cq
<?php
/**
 * Contains code that test version of PHP run script.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
 *
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2009, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
 * @since      revision 921
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
// Make CGI work like CLI.
if (PHP_SAPI != 'cli') {
  ini_set('implicit_flush', '1');
  ini_set('register_argc_argv', '1');
  defined('STDIN') || define('STDIN', fopen('php://stdin', 'r'));
  defined('STDOUT') || define('STDOUT', fopen('php://stdout', 'w'));
  defined('STDERR') || define('STDERR', fopen('php://stderr', 'w'));
};
/**
 * @internal Only let this code be ran directly.
 */
if (basename(__FILE__) != basename($_SERVER['PHP_SELF'])) {
  $mess = 'Including of ' . $argv[0] . ' is not allowed' . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error');
  exit(1);
};
if (version_compare(PHP_VERSION,"5.2.1","<")) {
  fwrite(STDOUT, "old");
} else if (version_compare(PHP_VERSION,"5.3.2",">")) {
  fwrite(STDOUT, "untested");
} else {
  fwrite(STDOUT, "tested");
};
exit(0);
?>
