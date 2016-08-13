<?php
declare(strict_types = 1);
/**
 * Contains EveApiCreatorTrait trait.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015-2016 Michael Cummings
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
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Cli\EveApi;

use Twig_Environment;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Exception\YapealFileSystemException;
use Yapeal\FileSystem\CommonFileHandlingTrait;
use Yapeal\FileSystem\RelativeFileSearchTrait;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Trait EveApiCreatorTrait
 */
trait EveApiCreatorTrait
{
    use CommonFileHandlingTrait, EveApiEventEmitterTrait, RelativeFileSearchTrait;
    /**
     * Getter for $overwrite.
     *
     * @return boolean
     */
    public function isOverwrite(): bool
    {
        return $this->overwrite;
    }
    /**
     * Fluent interface setter for $overwrite.
     *
     * @param bool $value
     *
     * @return self Fluent interface.
     */
    public function setOverwrite(bool $value = true)
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
     * @param string                               $eventName
     * @param \Yapeal\Xml\EveApiReadWriteInterface $data
     * @param array                                $context
     *
     * @return bool|string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function getContentsFromTwig(string $eventName, EveApiReadWriteInterface $data, array $context)
    {
        $yem = $this->getYem();
        try {
            $templateName = $this->findRelativeFileWithPath(ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                $this->getTwigExtension());
        } catch (YapealFileSystemException $exc) {
            $mess = 'Failed to find accessible twig template file during';
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::WARNING,
                $this->createEventMessage($mess, $data, $eventName),
                ['exception' => $exc]);
            return false;
        }
        $mess = sprintf('Using %1$s template file in twig to create file of', $templateName);
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
        try {
            $contents = $this->getTwig()
                ->render($templateName, $context);
        } catch (\Twig_Error $exc) {
            $mess = 'Creation of file failed because of twig exception during';
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::WARNING,
                $this->createEventMessage($mess, $data, $eventName),
                ['exception' => $exc]);
            return false;
        }
        return $contents;
    }
    /**
     * @return Twig_Environment
     * @throws \LogicException
     */
    protected function getTwig(): Twig_Environment
    {
        if (null === $this->twig) {
            $mess = 'Tried to use Twig before it was set';
            throw new \LogicException($mess);
        }
        return $this->twig;
    }
    /**
     * @return string
     * @throws \LogicException
     */
    protected function getTwigExtension(): string
    {
        if (null === $this->twigExtension) {
            $mess = 'Tried to use twig file extension before it was set';
            throw new \LogicException($mess);
        }
        return $this->twigExtension;
    }
    /**
     * Used to decide if existing file should be overwritten.
     *
     * @var bool $overwrite
     */
    private $overwrite = false;
    /**
     * @var Twig_Environment $twig
     */
    private $twig;
    /**
     * @var string twigExtension
     */
    private $twigExtension;
}
