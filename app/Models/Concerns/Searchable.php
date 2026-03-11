<?php

namespace App\Models\Concerns;

use App\Models\SearchIndex;

trait Searchable
{
    /**
     * Return a string of all searchable content for this model.
     * Must be implemented by the using class.
     */
    abstract public function indexData(): string;

    /**
     * Boot the Searchable trait.
     *
     * Automatically updates the search_indices table whenever the
     * model is saved or deleted.
     */
    public static function bootSearchable(): void
    {
        static::saved(function ($model) {
            $model->updateSearchIndex();
        });

        static::deleted(function ($model) {
            $model->removeSearchIndex();
        });
    }

    /**
     * Update or create the search index entry for this model.
     */
    public function updateSearchIndex(): void
    {
        SearchIndex::updateIndex(
            static::class,
            $this->getKey(),
            $this->indexData()
        );
    }

    /**
     * Remove the search index entry for this model.
     */
    public function removeSearchIndex(): void
    {
        SearchIndex::removeIndex(
            static::class,
            $this->getKey()
        );
    }
}
