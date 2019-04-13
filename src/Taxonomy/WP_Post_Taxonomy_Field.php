<?php

namespace PeteKlein\WP\PostCollection\Taxonomy;

class WP_Post_Taxonomy_Field
{
    public $taxonomy;
    public $default;

    public function __construct(string $taxonomy, $default)
    {
        $this->taxonomy = $taxonomy;
        $this->default = $default;
    }
}
