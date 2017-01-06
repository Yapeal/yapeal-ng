<?php
declare(strict_types = 1);
/**
 * Contains Bootstrap.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2017 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the COPYING-LESSER.md
 * file. A copy of the GNU GPL should also be available in the COPYING.md file.
 *
 * @copyright 2014-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
use Composer\Autoload\ClassLoader;

/*
 * Nothing to do if Composer auto loader already exists.
 */
if (class_exists(ClassLoader::class, false)) {
    return 0;
}
/*
 * Find Composer auto loader after striping away any vendor path.
 */
$path = str_replace('\\', '/', dirname(__DIR__));
$vendorPos = strpos($path, 'vendor/');
if (false !== $vendorPos) {
    $path = substr($path, 0, $vendorPos);
}
$path .= '/vendor/autoload.php';
/*
 * Turn off warning messages for the following include.
 */
$errorReporting = error_reporting(E_ALL & ~E_WARNING);
/** @noinspection PhpIncludeInspection */
include_once $path;
error_reporting($errorReporting);
unset($errorReporting, $path, $vendorPos);
if (!class_exists('\Composer\Autoload\ClassLoader', false)) {
    $mess = 'Could NOT find required Composer class auto loader. Aborting ...';
    if ('cli' === PHP_SAPI) {
        fwrite(STDERR, $mess);
    } else {
        fwrite(STDOUT, $mess);
    }
    unset($mess);
    exit(1);
}
