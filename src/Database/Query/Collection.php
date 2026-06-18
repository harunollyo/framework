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

    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    /**
     * Merge the items into the collection.
     *
     * @param  Collection|array<string, Model> $items The items to merge
     * @return static The merged collection
     * @since  1.0.0
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
     * @param  Collection|array<string, Model> $items The items to get the dictionary for
     * @return array The dictionary
     * @since  1.0.0
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
}
