#!/usr/bin/php -Cq
<?php
/**
 * Contains code used to test if user has privileges on a MySQL database.
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
 * @copyright  Copyright (c) 2008-2011, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @subpackage Install
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
// Only CLI.
if (PHP_SAPI != 'cli') {
  $mess = 'This script will only work with CLI version of PHP';
  die($mess);
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
// Used to over come path issues caused by how script is ran on server.
$dir = realpath(dirname(__FILE__));
chdir($dir);
// Define shortened name for DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);
// Move down and over to 'inc' directory to read common_paths.php
$path = $dir . DS . '..' . DS . 'inc' . DS . 'common_paths.php';
require_once realpath($path);
// Load ADO classes that are needed.
require_once YAPEAL_ADODB . 'adodb.inc.php';
require_once YAPEAL_ADODB . 'adodb-xmlschema03.inc.php';
if ($argc < 5) {
  $mess = 'Hostname Username Password Database are required in ' . $argv[0] . PHP_EOL;
  $mess .= 'TablePrefix and XMLfile(s) are optional' . PHP_EOL;
  $mess .= 'If XMLfile(s) is a list it needs to be inside quotes' . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error');
  exit(2);
};
// Strip any quotes
$replace = array("'", '"');
for ($i = 1; $i < $argc; ++$i) {
  $argv[$i] = str_replace($replace, '', $argv[$i]);
};
$dsn = 'mysql://' . $argv[2] . ':' . $argv[3] . '@' . $argv[1] . '/' . $argv[4];
if ($argc > 6) {
  $sections = explode(' ', $argv[6]);
} else {
  $sections = array('util', 'account', 'char', 'corp', 'eve', 'map', 'server');
};
$ret = 0;
try {
  // Get connection to DB.
  $db = ADONewConnection($dsn);
  $missing = array();
  foreach ($sections as $section) {
    $file = realpath(YAPEAL_INSTALL . $section . '.xml');
    // Get new Schema.
    $schema = new adoSchema($db);
    // Some settings for Schema.
    $schema->ExecuteInline(FALSE);
    $schema->ContinueOnError(FALSE);
    $schema->SetUpgradeMethod('ALTER');
    if ($argc > 5 && !empty($argv[5])) {
      $schema->SetPrefix($argv[5], FALSE);
    };
    if (!is_file($file)) {
      $missing[] = $file;
      continue;
    };
    $xml = file_get_contents(realpath(YAPEAL_INSTALL . $section . '.xml'));
    if (FALSE === $xml) {
      $mess = 'Could not read ' . $section . '.xml in ' . YAPEAL_INSTALL;
      fwrite(STDERR, $mess);
      fwrite(STDOUT, 'error');
      exit(2);
    };
    $sql = $schema->ParseSchemaString($xml);
    $result = $schema->ExecuteSchema($sql);
    if ($result == 2) {
      ++$ret;
    } else if ($result == 1) {
      $mess = 'Error executing schema for ' . $section . PHP_EOL;
      fwrite(STDERR, $mess);
    } else {
      $mess = 'Failed to execute schema for ' . $section . PHP_EOL;
      fwrite(STDERR, $mess);
    };
    $result = $schema->SaveSQL(YAPEAL_CACHE . $section . '.sql');
    if (FALSE === $result) {
      $mess = 'Could not save ' . $section . '.sql to ' . YAPEAL_CACHE . PHP_EOL;
      fwrite(STDERR, $mess);
    };
    $schema = NULL;
  };// foreach $sections as $section ...
  if (!empty($missing)) {
    $mess = 'Could not find the following files: ';
    $mess .= implode('.xml, ', $missing) . PHP_EOL;
    fwrite(STDERR, $mess);
  };
  if ($ret != count($sections)) {
    $mess = 'Not all files processed correctly' . PHP_EOL;
    fwrite(STDERR, $mess);
    fwrite(STDOUT, 'false');
  }else {
    fwrite(STDOUT , 'true');
  };
  exit(0);
} catch (Exception $e) {
  $mess = <<<MESS
EXCEPTION:
     Code: {$e->getCode()}
  Message: {$e->getMessage()}
     File: {$e->getFile()}
     Line: {$e->getLine()}
Backtrace:
{$e->getTraceAsString()}
MESS;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'false');
  exit(4);
}
?>
