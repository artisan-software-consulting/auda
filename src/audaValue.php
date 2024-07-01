<?php
/*
 * Copyright (c) 2024. Artisan Software Consulting. All rights reserved.
 */

namespace auda;

/**
 * @name audaValue.php
 * @author
 * @copyright Artisan Software Consulting
 * @version 1.0.3
 * @package
 * @description
 */
class audaValue
{
    private bool $protected;
    private mixed $value;
    private string $temporary_file_name = "";

    public function __construct(bool $protected, mixed $value)
    {
        $this->protected = $protected;
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function __get($name) {
        switch ($name) {
            case "tempname":
                return $this->getFileTempName();
                break;
            default:
                return $this->getValue();
        }
    }

    public function isProtected(): bool
    {
        return $this->protected;
    }

    public function setProtected(bool $protected): static
    {
        $this->protected = $protected;
        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getFileTempName(): string
    {
        return $this->temporary_file_name;
    }

    public function setFileTempName(string $name): static
    {
        $this->temporary_file_name = $name;
        return $this;
    }

}