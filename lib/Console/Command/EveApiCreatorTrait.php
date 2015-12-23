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
use Twig_Environment;
use Yapeal\Event\EveApiEventEmitterTrait;

/**
 * Trait EveApiCreatorTrait
 */
trait EveApiCreatorTrait
{
    use EveApiEventEmitterTrait, FilePathNormalizerTrait;
    /**
     * Getter for $overwrite.
     *
     * @return boolean
     */
    public function isOverwrite()
    {
        return $this->overwrite;
    }
    /**
     * @param string $value
     *
     * @return self Fluent interface.
     */
    public function setDir($value)
    {
        $this->dir = (string)$value;
        return $this;
    }
    /**
     * Fluent interface setter for $overwrite.
     *
     * @param boolean $value
     *
     * @return self Fluent interface.
     */
    public function setOverwrite($value = true)
    {
        $this->overwrite = (bool)$value;
        return $this;
    }
    /**
     * @param Twig_Environment $twig
     *
     * @return self Fluent interface.
     */
    public function setTwig(Twig_Environment $twig)
    {
        $this->twig = $twig;
        return $this;
    }
    /**
     * @return string
     */
    protected function getDir()
    {
        return $this->dir;
    }
    /**
     * @param string $for
     * @param string $sectionName
     * @param string $apiName
     *
     * @return false|string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function getTemplateName($for, $sectionName, $apiName)
    {
        // section/api.for.twig, api.for.twig, section.for.twig, for.twig
        $templateNames = explode(
            ',',
            sprintf('%1$s/%2$s.%3$s.twig,%2$s.%3$s.twig,%1$s.%3$s.twig,%3$s.twig', $sectionName, $apiName, $for)
        );
        foreach ($templateNames as $templateName) {
            if (is_file($templateName)) {
                return $templateName;
            }
        }
        return false;
    }
    /**
     * @return Twig_Environment
     */
    protected function getTwig()
    {
        return $this->twig;
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
    /**
     * @type string $dir Directory path used when saving new files.
     */
    protected $dir;
    /**
     * Used to decide if existing file should be overwritten.
     *
     * @type bool $overwrite
     */
    protected $overwrite = false;
    /**
     * @type Twig_Environment $twig
     */
    protected $twig;
}
