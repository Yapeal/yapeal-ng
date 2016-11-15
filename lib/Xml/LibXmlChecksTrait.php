<?php
declare(strict_types = 1);
/**
 * Contains trait LibXmlChecksTrait.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016 Michael Cummings
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
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 */
namespace Yapeal\Xml;

use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;

/**
 * Trait LibXmlChecksTrait.
 */
trait LibXmlChecksTrait
{
    /**
     * Checks for any libxml errors and logs them.
     *
     * @param EveApiReadWriteInterface $data
     * @param MediatorInterface        $yem
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function checkLibXmlErrors(EveApiReadWriteInterface $data, MediatorInterface $yem)
    {
        /**
         * @var \libXMLError[] $errors
         */
        $errors = libxml_get_errors();
        if (0 !== count($errors)) {
            foreach ($errors as $error) {
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $messagePrefix = 'Libxml Warning';
                        $level = Logger::NOTICE;
                        break;
                    case LIBXML_ERR_ERROR:
                        $messagePrefix = 'Libxml Error';
                        $level = Logger::WARNING;
                        break;
                    case LIBXML_ERR_FATAL:
                        $messagePrefix = 'Libxml Fatal Error';
                        $level = Logger::ERROR;
                        break;
                    default:
                        $messagePrefix = '';
                        $level = Logger::DEBUG;
                        break;
                }
                if ('' !== $messagePrefix) {
                    $messagePrefix .= sprintf(' %u: %s - Col: %u Line: %u during the transform of',
                        $error->code,
                        trim($error->message),
                        $error->column,
                        $error->line);
                    $yem->triggerLogEvent('Yapeal.Log.log', $level, $this->createEveApiMessage($messagePrefix, $data));
                }
            }
        }
    }
    /**
     * @param string                   $messagePrefix
     * @param EveApiReadWriteInterface $data
     *
     * @return string
     * @throws \LogicException
     */
    abstract protected function createEveApiMessage(string $messagePrefix, EveApiReadWriteInterface $data): string;
}
