<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchIndex extends Model
{
    protected $table = 'search_indices';
    public $timestamps = true;

    protected $fillable = [
        'model_type',
        'model_id',
        'content',
    ];

    // ---------------------------------------------------------------
    // Static Helpers
    // ---------------------------------------------------------------

    /**
     * Search across all indexed models using FULLTEXT search.
     *
     * @param  string  $query  The search terms.
     * @param  string|null  $modelType  Optional: restrict to a specific model class.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function searchModels(string $query, ?string $modelType = null)
    {
        $builder = static::whereRaw(
            'MATCH(content) AGAINST(? IN BOOLEAN MODE)',
            [$query]
        );

        if ($modelType) {
            $builder->where('model_type', $modelType);
        }

        return $builder->orderByRaw(
            'MATCH(content) AGAINST(? IN BOOLEAN MODE) DESC',
            [$query]
        )->get();
    }

    /**
     * Update or create a search index entry for a given model.
     */
    public static function updateIndex(string $modelType, int $modelId, string $content): static
    {
        return static::updateOrCreate(
            ['model_type' => $modelType, 'model_id' => $modelId],
            ['content' => $content]
        );
    }

    /**
     * Remove the search index entry for a given model.
     */
    public static function removeIndex(string $modelType, int $modelId): void
    {
        static::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->delete();
    }
}
