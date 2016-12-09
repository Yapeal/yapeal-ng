<?php
declare(strict_types = 1);
/**
 * Contains class LineFormatter.
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
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Log;

use Monolog\Formatter\LineFormatter as MLineFormatter;

/**
 * Class LineFormatter.
 */
class LineFormatter extends MLineFormatter
{
    /**
     * @return bool
     */
    public function isPrettyJson(): bool
    {
        return $this->prettyJson;
    }
    /**
     * @param bool $value
     */
    public function setPrettyJson(bool $value = true)
    {
        $this->prettyJson = $value;
        if ($value) {
            $this->allowInlineLineBreaks($value);
        }
    }
    /**
     * @param mixed $data
     *
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function normalize($data)
    {
        if ($data instanceof \DateTime) {
            return substr($data->format($this->dateFormat), 0, 14);
        }
        return parent::normalize($data);
    }
    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * @param \Throwable $exc
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function normalizeException($exc): array
    {
        if (!$exc instanceof \Throwable) {
            throw new \InvalidArgumentException('Throwable expected, got ' . gettype($exc) . ' / ' . get_class($exc));
        }
        $data = [
            'class' => get_class($exc),
            'message' => $exc->getMessage(),
            'code' => $exc->getCode(),
            'file' => str_replace('\\', '/', $exc->getFile()) . ':' . $exc->getLine()
        ];
        if ($exc instanceof \SoapFault) {
            if (isset($exc->faultcode)) {
                $data['faultcode'] = $exc->faultcode;
            }
            if (isset($exc->faultactor)) {
                $data['faultactor'] = $exc->faultactor;
            }
            if (isset($exc->detail)) {
                $data['detail'] = $exc->detail;
            }
        }
        if ($this->includeStacktraces) {
            foreach ($exc->getTrace() as $frame) {
                if (isset($frame['file'])) {
                    $data['trace'][] = str_replace('\\', '/', $frame['file']) . ':' . $frame['line'];
                } elseif (isset($frame['function']) && $frame['function'] === '{closure}') {
                    // We should again normalize the frames, because it might contain invalid items
                    $data['trace'][] = $this->normalize($frame['function']);
                } else {
                    // We should again normalize the frames, because it might contain invalid items
                    $data['trace'][] = $this->toJson($this->normalize($frame), true);
                }
            }
        }
        if (null !== $previous = $exc->getPrevious()) {
            $data['previous'] = $this->normalizeException($previous);
        }
        return $data;
    }
    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Return the JSON representation of a value
     *
     * @param  mixed $data
     * @param  bool  $ignoreErrors
     *
     * @throws \RuntimeException if encoding fails and errors are not ignored
     * @return string
     */
    protected function toJson($data, $ignoreErrors = false): string
    {
        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION;
        if ($this->isPrettyJson()) {
            $options |= JSON_PRETTY_PRINT;
        }
        // suppress json_encode errors since it's twitchy with some inputs
        if ($ignoreErrors) {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            return @json_encode($data, $options);
        }
        $json = json_encode($data, $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $json = json_last_error_msg();
        }
        return $json;
    }
    /**
     * @var bool $prettyJson
     */
    private $prettyJson = false;
}
