<?php
/**
 * Contains EveApiCreatorTrait trait.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015 Michael Cummings
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Console\Command;

use FilePathNormalizer\FilePathNormalizerTrait;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Log\Logger;

/**
 * Trait EveApiCreatorTrait
 */
trait EveApiCreatorTrait
{
    use EveApiEventEmitterTrait, FilePathNormalizerTrait;
    /**
     * @param string $for
     *
     * @return false|string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function getTemplate($for)
    {
        $templateName = sprintf('%1$s/eveApi.%2$s.template', __DIR__, $for);
        $templateName = $this->getFpn()
            ->normalizeFile($templateName);
        if (!is_file($templateName)) {
            $mess = 'Could NOT find template file ' . $templateName;
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::ERROR, $mess);
            return false;
        }
        $template = file_get_contents($templateName);
        if (false === $template) {
            $mess = 'Could NOT open template file ' . $templateName;
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::ERROR, $mess);
            return false;
        }
        return $template;
    }
    /**
     * @param array  $subs
     * @param string $template
     *
     * @return string
     */
    protected function processTemplate(array $subs, $template)
    {
        $keys = [];
        $replacements = [];
        foreach ($subs as $name => $value) {
            $keys[] = '{' . $name . '}';
            $replacements[] = $value;
        }
        return str_replace($keys, $replacements, $template);
    }
    /**
     * @param string $fileName
     * @param string $contents
     *
     * @return int
     */
    protected function saveToFile($fileName, $contents)
    {
        $fileName = $this->getFpn()
            ->normalizeFile($fileName);
        return file_put_contents($fileName, $contents);
    }
}
