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
 * Copyright (C) 2016-2017 Michael Cummings
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
 * @copyright 2016-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Log;

use Monolog\Formatter\FormatterInterface;

/**
 * Class LineFormatter.
 */
final class LineFormatter implements FormatterInterface
{
    const MAX_NORMALIZED_ARRAY_ELEMENTS = 1000;
    const SIMPLE_DATE = "Y-m-d H:i:s";
    const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
    /**
     * LineFormatter constructor.
     *
     * @param string|null $format                     The format of the message.
     * @param string|null $dateFormat                 The format of the timestamp: one supported by DateTime::format.
     * @param bool        $allowInlineLineBreaks      Whether to allow inline line breaks in log entries.
     * @param bool        $ignoreEmptyContextAndExtra Whether to ignore empty context and/or extra parts in log entries.
     *
     * @throws \RuntimeException
     */
    public function __construct(
        string $format = null,
        string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false
    ) {
        $this->dateFormat = $dateFormat ?? static::SIMPLE_DATE;
        $this->format = $format ?? static::SIMPLE_FORMAT;
        $this->setInlineLineBreaks($allowInlineLineBreaks);
        $this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra;
        $this->includeStackTraces = false;
    }
    /**
     * Formats a single log record.
     *
     * @param array $record
     *
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function format(array $record): string
    {
        $vars = $this->normalize($record);
        $output = $this->format;
        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.' . $var . '%')) {
                $output = str_replace('%extra.' . $var . '%', $this->stringify($val), $output);
                unset($vars['extra'][$var]);
            }
        }
        foreach ($vars['context'] as $var => $val) {
            if (false !== strpos($output, '%context.' . $var . '%')) {
                $output = str_replace('%context.' . $var . '%', $this->stringify($val), $output);
                unset($vars['context'][$var]);
            }
        }
        if ($this->ignoreEmptyContextAndExtra) {
            if (empty($vars['context'])) {
                unset($vars['context']);
                $output = str_replace('%context%', '', $output);
            }
            if (empty($vars['extra'])) {
                unset($vars['extra']);
                $output = str_replace('%extra%', '', $output);
            }
        }
        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%' . $var . '%')) {
                $output = str_replace('%' . $var . '%', $this->stringify($val), $output);
            }
        }
        // remove leftover %extra.xxx% and %context.xxx% if any
        $output = (string)preg_replace('/%(?:extra|context)\..+?%/', '', $output);
        return $output;
    }
    /**
     * Allows batch processing of log records.
     *
     * @param array $records
     *
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function formatBatch(array $records): string
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }
        return $message;
    }
    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * @return bool
     */
    public function isPrettyJson(): bool
    {
        return $this->prettyJson;
    }
    /**
     * Setter for include stack traces when given a Throwable.
     *
     * @param bool $value
     *
     * @return self Fluent interface.
     */
    public function setIncludeStackTraces(bool $value): self
    {
        $this->includeStackTraces = $value;
        return $this;
    }
    /**
     * Sets whether to allow any inline EOL chars.
     *
     * @param bool $allow
     */
    public function setInlineLineBreaks(bool $allow = true)
    {
        $this->inlineLineBreaks = $allow;
    }
    /**
     * Used to set pretty json mode.
     *
     * @param bool $value
     */
    public function setPrettyJson(bool $value = true)
    {
        $this->prettyJson = $value;
        if ($value) {
            $this->setInlineLineBreaks($value);
        }
    }
    /**
     * Makes sure everything ends up as a string with or without inline EOL chars.
     *
     * @param mixed $value
     *
     * @return string
     * @throws \RuntimeException
     */
    public function stringify($value): string
    {
        $value = $this->convertToString($value);
        if (!$this->inlineLineBreaks) {
            $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);
        }
        return $value;
    }
    /**
     * Handling the converting to string of NULL, booleans, other scalars, and json encodes everything else.
     *
     * @param $data
     *
     * @return string
     * @throws \RuntimeException
     */
    private function convertToString($data): string
    {
        if (null === $data || is_bool($data)) {
            return (string)var_export($data, true);
        }
        if (is_scalar($data)) {
            return (string)$data;
        }
        return $this->toJson($data);
    }
    /**
     * @param mixed $data
     *
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function normalize($data)
    {
        if (null === $data || is_scalar($data)) {
            if (is_float($data)) {
                if (is_infinite($data)) {
                    return ($data > 0 ? '' : '-') . 'INF';
                }
                if (is_nan($data)) {
                    return 'NaN';
                }
            }
            return $data;
        }
        if (is_array($data)) {
            return $this->normalizeArray($data);
        }
        if ($data instanceof \DateTime) {
            return substr($data->format($this->dateFormat), 0, 14);
        }
        if (is_object($data)) {
            if ($data instanceof \Throwable) {
                return $this->normalizeThrowable($data);
            }
            if ($data instanceof \Closure) {
                return ['[function]:' => '(closure)'];
            }
            if (method_exists($data, '__toString') && !$data instanceof \JsonSerializable) {
                $value = $data->__toString();
            } else {
                $value = $this->normalizeObject($data);
            }
            return ['[class]:' => [$this->normalize(get_class($data)) => $value]];
        }
        // Instead of believing in unicorns like the original code I assume anything that gets here is a resource.
        return ['[resource]:' => '(' . get_resource_type($data) . ')'];
    }
    /**
     * Normalizes array while limiting itself to no more then the first 1000 elements.
     *
     * @param $data
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function normalizeArray(array $data): array
    {
        $cnt = count($data);
        $wasToBig = false;
        if (self::MAX_NORMALIZED_ARRAY_ELEMENTS < $cnt) {
            $wasToBig = true;
            list($data,) = array_chunk($data, self::MAX_NORMALIZED_ARRAY_ELEMENTS);
        }
        $normalized = [];
        foreach ($data as $key => $value) {
            $normalized[$key] = $this->normalize($value);
        }
        if ($wasToBig) {
            $normalized['...'] = sprintf('Over %s items (%s total), aborting normalization',
                self::MAX_NORMALIZED_ARRAY_ELEMENTS,
                $cnt);
        }
        return $normalized;
    }
    /**
     * Normalizes exception by converting parts of it into a cleaned up array that can be ran through json encoding.
     *
     * @param \Throwable $exc
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function normalizeThrowable(\Throwable $exc): array
    {
        $data = [
            'message' => $exc->getMessage(),
            'code' => $exc->getCode(),
            'file' => str_replace('\\', '/', $exc->getFile()) . ':' . $exc->getLine()
        ];
        if ($this->includeStackTraces) {
            $traces = [];
            foreach ($exc->getTrace() as $frame) {
                $trace = [];
                if (array_key_exists('class', $frame)) {
                    $ownerType = 'class:';
                    $owner = $frame['class'];
                    $trace[$ownerType][$owner]['function'] = $frame['function'] ?? '(unknown)';
                } elseif (array_key_exists('function', $frame)) {
                    $ownerType = 'function:';
                    $owner = $frame['function'];
                } else {
                    $mess = 'You have found an unicorn inside an exception backtrace please send pictures '
                        . gettype($frame);
                    throw new \RuntimeException($mess);
                }
                $trace[$ownerType][$owner]['line'] = $frame['line'] ?? 0;
                $trace[$ownerType][$owner]['file'] = !empty($frame['file']) ? str_replace('\\',
                    '/',
                    $frame['file']) : '(unknown)';
                $traces[] = $trace;
            }
            $data['trace'] = $traces;
        }
        if (null !== $previous = $exc->getPrevious()) {
            $data['previous'] = $this->normalizeThrowable($previous);
        }
        return ['[exception]:' => [$this->normalize(get_class($exc)) => $data]];
    }
    /**
     * Normalize general objects showing their public properties and methods.
     *
     * @param object $data
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function normalizeObject($data): array
    {
        $properties = get_object_vars($data);
        $methods = get_class_methods($data);
        $value = [];
        if (null !== $methods && 0 !== count($methods)) {
            $value['methods'] = $this->normalize($methods);
        }
        if (0 !== count($properties)) {
            $value['properties'] = $this->normalize($properties);
        }
        return $value;
    }
    /**
     * Return the JSON representation of a value
     *
     * @param  mixed $data
     *
     * @throws \RuntimeException if encoding fails and errors are not ignored
     * @return string
     */
    private function toJson($data): string
    {
        $options = $this->isPrettyJson() ? JSON_PRETTY_PRINT : 0;
        $options |= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION;
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        return (string)@json_encode($data, $options);
    }
    /**
     * @var string $dateFormat
     */
    private $dateFormat;
    /**
     * @var string $format
     */
    private $format;
    /**
     * @var bool $ignoreEmptyContextAndExtra
     */
    private $ignoreEmptyContextAndExtra;
    /**
     * @var bool $includeStackTraces
     */
    private $includeStackTraces;
    /**
     * @var bool $inlineLineBreaks
     */
    private $inlineLineBreaks;
    /**
     * @var bool $prettyJson
     */
    private $prettyJson = false;
}
