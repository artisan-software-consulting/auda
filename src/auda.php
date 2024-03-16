<?php
/*
 * Copyright (c) 2024. Artisan Software Consulting. All rights reserved.
 */

namespace jaschiel;

/**
 * @name auda.php
 * @author James Schiel
 * @date March 8, 2024
 * @copyright Artisan Software Consulting
 * @version 1
 * @package
 * @description
 *
 * 2024-Mar-16 - Schiel - added a clear method (initially used in a redirect); moved into a separate directory; I want to try to keep
 *                   this functionality as independent of the sierra architecture as possible.
 * 2024-Mar-9 - Schiel - modified to convert "$$" in values to "/" since it is a security risk to accept encoded slashes in the URL.
 */
final class auda
{
    private const TRIM_CHARACTERS = " ]";
    private array $theAuda;

    public function __construct()
    {
        $this->clear();
    }

    public function getAll(): array
    {
        return $this->theAuda;
    }

    public function add(string $name, mixed $rawValue, bool $toLower = true, bool $convertDollarsToSlashes = true): auda
    {
        $this->setNestedValue($this->theAuda, $this->correctedName($toLower, $name), $this->preparedValue($convertDollarsToSlashes, $rawValue));
        return $this;
    }

    public function addProtected(string $name, mixed $rawValue, bool $toLower = true, bool $convertDollarsToSlashes = true): auda
    {
        $this->setNestedValue($this->theAuda, $this->correctedName($toLower, $name), $this->preparedValue($convertDollarsToSlashes, $rawValue, true));
        return $this;
    }

    public function addQuery(string $query): static
    {
        parse_str($query, $this->theAuda);
        return $this;
    }

    /**
     * Receive the RAW JSON data from the request by reading the contents of the input stream and decoding it.
     * If the JSON is valid, an array representation of the JSON data is returned. Otherwise, an empty array is returned.
     *
     * @return array An array representation of the JSON data or an empty array if the JSON is invalid
     */
    private function receiveRAWJsonData(): array
    {
        //Receive the RAW post data.
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        //If json_decode failed, the JSON is invalid.
        if (is_array($decoded)) {
            return $decoded;
        } else {
            return [];
        }
    }

    /**
     * Injects the arguments fetched from the request body into the `$theAuda` array if the content type is either "application/json" or "text/plain".
     * Modified the routine so that the last assignment to a specific key wins; older values are lost.
     *
     * @param string $contentType The content type of the request body
     * @param bool $toLower
     * @return void
     */
    public function addFetch(string $contentType, bool $toLower = true): void
    {
        if ($contentType == "application/json" || $contentType == "text/plain") {
            $jsonArgs = $this->receiveRAWJsonData();
            foreach ($jsonArgs as $key => $value) {
                $this->add($key, $value, $toLower);
            }
        }
    }

    public function __toString(): string
    {
        $response = "AUDA=>";
        foreach ($this->theAuda as $name => $value) {
            if (is_string($value)) {
                $response .= "{$name}={$value},";
            } else {
                $response .= "{$name}=object,";
            }
        }
        return $response;
    }

    public function get($name, bool $toLower = true): mixed
    {
        $name = ($toLower) ? strtolower($name) : $name;
        $parts = explode('.', $name);

        // If the name concludes with "[]", an array is requested
        if (preg_match('/\[\]$/', end($parts))) {
            $parts[key($parts)] = rtrim(end($parts), '[]');
        }

        $value = $this->theAuda;

        foreach ($parts as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null; // The value doesn't exist
            }
        }

        if (is_array($value)) {
            return $this->flattenArray($value);
        } else {
            /** @var audaValue $value */
            return $value->getValue();
        }
    }

    /**
     * Clear the array of arguments.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->theAuda = [];
    }

    /**
     *
     * PRIVATE METHODS
     *
     */

    /**
     * @param bool $toLower
     * @param string $name
     * @return string[]
     */
    private function correctedName(bool $toLower, string $name): array
    {
        return explode("[", ($toLower) ? strtolower($name) : $name);
    }

    /**
     * @param array $data
     * @param array $names
     * @param mixed $value
     * @return void
     */
    private function setNestedValue(array &$data, array $names, audaValue $value): void
    {
        $keyPart = $this->shiftAndTrim($names);

        if (sizeof($names) === 0 or $keyPart === "") {
            if ($keyPart === "") {
                $data = $value;
            } else {
                if (!isset($data[$keyPart]) || !$data[$keyPart]->isProtected()) {
                    $data[$keyPart] = $value;
                }
            }
        } else {
            // If the key does not exist in the array, initialize it as an empty array
            if (!isset($data[$keyPart])) {
                $data[$keyPart] = [];
            }
            $this->setNestedValue($data[$keyPart], $names, $value);
        }
    }

    private function shiftAndTrim(array &$array): string
    {
        return trim(array_shift($array), self::TRIM_CHARACTERS);
    }

    private function preparedValue(bool $convertDollarsToSlashes, mixed $value, bool $protected = false): audaValue
    {
        if ($convertDollarsToSlashes && is_string($value)) {
            $value = str_replace('$$', '/', $value);
        }
        return new audaValue($protected, $value);
    }

    /**
     * @param array $value
     * @return array
     * This routine converts arrays of audaValue objects into an array with only the user-anticipated values.
     */
    private function flattenArray(array $value): array
    {
        $result = [];
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                $result = array_merge($result, $this->flattenArray($val));
            } else {
                /** @var audaValue $val */
                $result[$key] = $val->getValue();
            }
        }
        return $result;
    }
}