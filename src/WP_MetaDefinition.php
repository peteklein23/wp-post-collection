<?php

namespace PeteKlein\WP\PostCollection;

class WP_MetaDefinition
{
    public $key;
    public $default;

    public function __construct(string $key, $default)
    {
        $this->key = $key;
        $this->default = $default;
    }

    public function valueOrDefault($value)
    {
        if (empty($value)) {
            return $this->default;
        }

        return $value;
    }
}
