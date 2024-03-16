<?php
/*
 * Copyright (c) 2024. Artisan Software Consulting. All rights reserved.
 */

namespace ArtisanSoftwareConsulting;

/**
 * @name audaValue.php
 * @author
 * @copyright Artisan Software Consulting
 * @version
 * @package
 * @description
 */
class audaValue
{
    private bool $protected;
    private mixed $value;

    public function __construct(bool $protected, mixed $value)
    {
        $this->protected = $protected;
        $this->value = $value;
    }

    public function isProtected(): bool
    {
        return $this->protected;
    }

    public function setProtected(bool $protected): void
    {
        $this->protected = $protected;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

}