<?php

namespace PeteKlein\WP\PostCollection\Taxonomy;

class WP_Post_Taxonomy_Fields
{
    public $fields = [];
    public $terms = [];

    public function add_field(string $taxonomy, $default)
    {
        $this->fields[] = new WP_Post_Taxonomy_Field($taxonomy, $default);

        return $this;
    }

    private function get_taxonomies()
    {
        return array_column($this->fields, 'taxonomy');
    }

    public function list(int $post_id, string $taxonomy)
    {
        if (!empty($this->terms['post_id'])) {
            return $this->terms['post_id'];
        }

        return null;
    }

    public function get(int $post_id, string $taxonomy)
    {
        $post = null;
        if (!empty($this->terms['post_id'])) {
            $post = $this->terms['post_id'];
        }

        if (!empty($post[$taxonomy])) {
            return $post[$taxonomy];
        }

        return null;
    }

    private function populate_missing_values($formatted_results)
    {
        foreach ($formatted_results as $post_id => &$taxonomy) {
            foreach ($this->fields as $field) {
                if (empty($taxonomy[$field->taxonomy])) {
                    $taxonomy[$field->taxonomy] = $field->default;
                }
            }
        }

        return $formatted_results;
    }
    
    private function format_results($results)
    {
        $formatted_results = [];
        foreach ($results as $result) {
            $post_id = $result->post_id;
            $taxonomy = $result->taxonomy;
            $slug = $result->slug;
            if (empty($formatted_results[$post_id])) {
                $formatted_results[$post_id] = [];
            }

            if (empty($formatted_results[$post_id][$taxonomy])) {
                $formatted_results[$post_id][$taxonomy] = [];
            }
            unset($result->post_id);
            $formatted_results[$post_id][$taxonomy][] = new \WP_Term($result);
        }

        return $this->populate_missing_values($formatted_results);
    }

    /** run query to get terms */
    public function fetch(array $post_ids)
    {
        global $wpdb;

        $taxonomies = $this->get_taxonomies();
        $taxonomy_list = "'" . join("', '", $taxonomies) . "'";
        $post_list = join(', ', $post_ids);

        $query = "SELECT
            tr.object_id as post_id,
            tt.term_id,
            t.name,
            t.slug,
            t.term_group,
            tt.term_taxonomy_id,
            tt.taxonomy,
            tt.description,
            tt.parent,
            tt.count
        FROM $wpdb->term_relationships tr
        INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN $wpdb->terms AS t ON t.term_id = tt.term_id
        WHERE tt.taxonomy IN ($taxonomy_list)
            AND tr.object_id IN ($post_list)";

        $results = $wpdb->get_results($query);
        $formatted_results = $this->format_results($results);
        $this->terms = $formatted_results;

        return $this->terms;
    }
}