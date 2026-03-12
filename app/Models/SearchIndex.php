<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchIndex extends Model
{
    protected $table = 'search_indices';
    public $timestamps = true;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    protected $fillable = [
        'model',
        'association_key',
        'data',
    ];

    // ---------------------------------------------------------------
    // Static Helpers
    // ---------------------------------------------------------------

    /**
     * Search across all indexed models using FULLTEXT search.
     */
    public static function searchModels(string $query, ?string $modelType = null)
    {
        $builder = static::whereRaw(
            'MATCH(data) AGAINST(? IN BOOLEAN MODE)',
            [$query]
        );

        if ($modelType) {
            $builder->where('model', $modelType);
        }

        return $builder->orderByRaw(
            'MATCH(data) AGAINST(? IN BOOLEAN MODE) DESC',
            [$query]
        )->get();
    }

    /**
     * Update or create a search index entry for a given model.
     */
    public static function updateIndex(string $modelType, int $modelId, string $content): static
    {
        return static::updateOrCreate(
            ['model' => $modelType, 'association_key' => $modelId],
            ['data' => $content]
        );
    }

    /**
     * Remove the search index entry for a given model.
     */
    public static function removeIndex(string $modelType, int $modelId): void
    {
        static::where('model', $modelType)
            ->where('association_key', $modelId)
            ->delete();
    }
}
