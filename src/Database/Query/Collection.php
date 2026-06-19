<?php
/**
 * Eloquent collection specialized for model instances with dictionary-based merge.
 * Indexes items by primary key to deduplicate during merge operations.
 * Extends the base Collection with model-aware collection behavior.
 *
 * @package    Framework
 * @subpackage Database\Query
 * @since      1.0.0
 */
namespace Framework\Database\Query;

defined('ABSPATH') || exit;

use Framework\Collections\Collection as BaseCollection;
use Framework\Database\Concerns\HasDictionary;

class Collection extends BaseCollection
{
    use HasDictionary;

    /**
     * Create a new instance.
     *
     * @param array $items The items.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    /**
     * Merge the items into the collection.
     *
     * @param mixed $items The items.
     *
     * @return static The merged collection
     *
     * @since 1.0.0
     */
    public function merge($items)
    {
        $dictionary = $this->get_dictionary();

        foreach ($items as $item) {
            $key = $this->get_dictionary_key($item->get_primary_key_value());

            if ($key !== null) {
                $dictionary[$key] = $item;
            }
        }

        return new static(array_values($dictionary));
    }

    /**
     * Get the dictionary for the collection.
     *
     * @param mixed $items The items.
     *
     * @return array The dictionary
     *
     * @since 1.0.0
     */
    public function get_dictionary($items = null)
    {
        $items = is_null($items) ? $this->items : $items;

        $dictionary = [];

        foreach ($items as $item) {
            $key = $this->get_dictionary_key($item->get_primary_key_value());

            if ($key !== null) {
                $dictionary[$key] = $item;
            }
        }

        return $dictionary;
    }

    /**
     * Eager load the relationships for the collection.
     *
     * @param mixed $relations The relations to eager load
     *
     * @return static A new collection containing the eager loaded items
     *
     * @since 1.0.0
     */
    public function load($relations)
    {
        if ($this->not_empty()) {
            if (is_string($relations)) {
                $relations = func_get_args();
            }

            $query = $this->first()->new_query_without_relations()->with($relations);

            $this->items = $query->eager_load_relations($this->items);
        }

        return $this;
    }
}
