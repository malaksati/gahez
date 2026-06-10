<?php

namespace App\V1\Repositories\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesInsensitiveSearch
{
    protected function insensitiveLikeTerm(string $search): string
    {
        return '%'.mb_strtolower(trim($search)).'%';
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    protected function applyTranslatableNameSearch(Builder $query, string $search, string $column = 'name'): void
    {
        $term = $this->insensitiveLikeTerm($search);

        $query->where(function (Builder $q) use ($term, $column) {
            $q->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(`{$column}`, '$.en'))) LIKE ?",
                [$term]
            )->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(`{$column}`, '$.ar'))) LIKE ?",
                [$term]
            );
        });
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  list<string>  $columns
     */
    protected function applyColumnsSearchInsensitive(Builder $query, array $columns, string $search): void
    {
        $term = $this->insensitiveLikeTerm($search);

        $query->where(function (Builder $q) use ($columns, $term) {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                $q->{$method}("LOWER(`{$column}`) LIKE ?", [$term]);
            }
        });
    }
}
