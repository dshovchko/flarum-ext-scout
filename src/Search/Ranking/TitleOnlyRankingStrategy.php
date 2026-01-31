<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Discussion\Discussion;
use Flarum\Search\SearchState;

class TitleOnlyRankingStrategy implements RankingStrategyInterface
{
    public function apply(SearchState $search, string $bit): void
    {
        $exactBit = '"' . str_replace('"', '\\"', $bit) . '"';

        $discussionExactTitleIds = Discussion::whereVisibleTo($search->getActor())
            ->where('title', $bit)
            ->pluck('id')
            ->all();

        $discussionExactBuilder = ScoutStatic::makeBuilder(Discussion::class, $exactBit);
        $discussionExactIds = $discussionExactBuilder->keys()->all();

        $discussionBuilder = ScoutStatic::makeBuilder(Discussion::class, $bit);
        $discussionIds = $discussionBuilder->keys()->all();

        $discussionAllIds = array_values(array_unique(array_merge($discussionExactTitleIds, $discussionExactIds, $discussionIds)));

        $discussionExactTitleIdsSql = count($discussionExactTitleIds) > 0 ? implode(', ', array_fill(0, count($discussionExactTitleIds), '?')) : '0';
        $discussionExactIdsSql = count($discussionExactIds) > 0 ? implode(', ', array_fill(0, count($discussionExactIds), '?')) : '0';
        $discussionIdsSql = count($discussionIds) > 0 ? implode(', ', array_fill(0, count($discussionIds), '?')) : '0';

        $query = $search->getQuery();
        $grammar = $query->getGrammar();

        $query
            ->where(function (\Illuminate\Database\Query\Builder $query) use ($discussionAllIds) {
                $query->whereIn('id', $discussionAllIds);
            })
            ->selectRaw($grammar->wrapTable('discussions') . '.first_post_id as most_relevant_post_id')
            ->groupBy('discussions.id');

        $search->setDefaultSort(function ($query) use (
            $discussionExactTitleIds,
            $discussionExactIds,
            $discussionExactTitleIdsSql,
            $discussionExactIdsSql,
            $discussionIds,
            $discussionIdsSql
        ) {
            $query
                ->orderByRaw(
                    'CASE ' .
                    'WHEN discussions.id IN (' . $discussionExactTitleIdsSql . ') THEN 0 ' .
                    'WHEN discussions.id IN (' . $discussionExactIdsSql . ') THEN 1 ' .
                    'WHEN discussions.id IN (' . $discussionIdsSql . ') THEN 2 ' .
                    'ELSE 3 END',
                    array_merge($discussionExactTitleIds, $discussionExactIds, $discussionIds)
                )
                ->orderByRaw(
                    'CASE ' .
                    'WHEN discussions.id IN (' . $discussionExactTitleIdsSql . ') THEN FIELD(discussions.id, ' . $discussionExactTitleIdsSql . ') ' .
                    'WHEN discussions.id IN (' . $discussionExactIdsSql . ') THEN FIELD(discussions.id, ' . $discussionExactIdsSql . ') ' .
                    'WHEN discussions.id IN (' . $discussionIdsSql . ') THEN FIELD(discussions.id, ' . $discussionIdsSql . ') ' .
                    'ELSE 0 END',
                    array_merge(
                        $discussionExactTitleIds,
                        $discussionExactTitleIds,
                        $discussionExactIds,
                        $discussionExactIds,
                        $discussionIds,
                        $discussionIds
                    )
                );
        });
    }
}
