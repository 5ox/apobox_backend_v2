<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SearchIndex extends Model
{
    protected $table = 'search_indices';
    public $timestamps = true;

    protected $fillable = [
        'model',
        'foreign_key',
        'data',
    ];

    // ---------------------------------------------------------------
    // Static Helpers
    // ---------------------------------------------------------------

    /**
     * Search across all indexed models using FULLTEXT search.
     * Accepts a raw query and converts it to boolean mode terms.
     */
    public static function searchModels(string $query, ?string $modelType = null): Collection
    {
        $booleanQuery = static::toBooleanQuery($query);

        $builder = static::whereRaw(
            'MATCH(data) AGAINST(? IN BOOLEAN MODE)',
            [$booleanQuery]
        );

        if ($modelType) {
            $builder->where('model', $modelType);
        }

        return $builder->orderByRaw(
            'MATCH(data) AGAINST(? IN BOOLEAN MODE) DESC',
            [$booleanQuery]
        )->get();
    }

    /**
     * Convert a user search string into MySQL FULLTEXT boolean mode query.
     * Each term gets a leading + (required) and trailing * (prefix match).
     */
    public static function toBooleanQuery(string $query): string
    {
        $terms = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);

        if ($terms === false || $terms === []) {
            return '';
        }

        // Each term: +term* (required prefix match)
        return implode(' ', array_map(fn(string $t) => '+' . $t . '*', $terms));
    }

    /**
     * Update or create a search index entry for a given model.
     */
    public static function updateIndex(string $modelType, int $modelId, string $content): static
    {
        return static::updateOrCreate(
            ['model' => $modelType, 'foreign_key' => $modelId],
            ['data' => $content]
        );
    }

    /**
     * Remove the search index entry for a given model.
     */
    public static function removeIndex(string $modelType, int $modelId): void
    {
        static::where('model', $modelType)
            ->where('foreign_key', $modelId)
            ->delete();
    }
}
