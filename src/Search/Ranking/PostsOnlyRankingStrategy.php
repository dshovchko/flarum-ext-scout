<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Post\Post;
use Flarum\Search\SearchState;
use Illuminate\Database\Query\Expression;

class PostsOnlyRankingStrategy implements RankingStrategyInterface
{
    public function apply(SearchState $search, string $bit): void
    {
        $exactBit = '"' . str_replace('"', '\\"', $bit) . '"';

        $postExactBuilder = ScoutStatic::makeBuilder(Post::class, $exactBit);
        $postExactIds = $postExactBuilder->keys()->all();

        $postBuilder = ScoutStatic::makeBuilder(Post::class, $bit);
        $postIds = $postBuilder->keys()->all();

        $postIdsMerged = array_values(array_unique(array_merge($postExactIds, $postIds)));

        $postIdsCount = count($postIdsMerged);

        // We could replace the "where field" with "where false" everywhere when there are no IDs, but it's easier to
        // keep a FIELD() statement and just hard-code some values to prevent SQL errors
        // we know nothing will be returned anyway, so it doesn't really matter what impact it has on the query
        $postIdsSql = $postIdsCount > 0 ? str_repeat(', ?', count($postIdsMerged)) : ', 0';

        $postExactIdsSql = count($postExactIds) > 0 ? str_repeat(', ?', count($postExactIds)) : ', 0';

        $query = $search->getQuery();
        $grammar = $query->getGrammar();

        $allMatchingPostsQuery = Post::whereVisibleTo($search->getActor())
            ->select('posts.discussion_id')
            ->selectRaw('FIELD(id' . $postIdsSql . ') as priority', $postIdsMerged)
            ->where('posts.type', 'comment')
            ->whereIn('id', $postIdsMerged);

        // Using wrap() instead of wrapTable() in join subquery to skip table prefixes
        // Using raw() in the join table name to use the same prefixless name
        $bestMatchingPostQuery = Post::query()
            ->select('posts.discussion_id')
            ->selectRaw('min(matching_posts.priority) as min_priority')
            ->join(
                new Expression('(' . $allMatchingPostsQuery->toSql() . ') ' . $grammar->wrap('matching_posts')),
                $query->raw('matching_posts.discussion_id'),
                '=',
                'posts.discussion_id'
            )
            ->groupBy('posts.discussion_id')
            ->addBinding($allMatchingPostsQuery->getBindings(), 'join');

        // Code based on Flarum\Discussion\Search\Gambit\FulltextGambit
        $subquery = Post::whereVisibleTo($search->getActor())
            ->select('posts.discussion_id')
            ->selectRaw('id as most_relevant_post_id')
            ->join(
                new Expression('(' . $bestMatchingPostQuery->toSql() . ') ' . $grammar->wrap('best_matching_posts')),
                $query->raw('best_matching_posts.discussion_id'),
                '=',
                'posts.discussion_id'
            )
            ->whereIn('id', $postIdsMerged)
            ->whereRaw('FIELD(id' . $postIdsSql . ') = best_matching_posts.min_priority', $postIdsMerged)
            ->addBinding($bestMatchingPostQuery->getBindings(), 'join');

        $exactPostsDiscussionsQuery = Post::whereVisibleTo($search->getActor())
            ->select('posts.discussion_id')
            ->where('posts.type', 'comment')
            ->whereIn('id', $postExactIds)
            ->groupBy('posts.discussion_id');

        $query
            ->selectRaw('COALESCE(posts_ft.most_relevant_post_id, ' . $grammar->wrapTable('discussions') . '.first_post_id) as most_relevant_post_id')
            ->leftJoin(
                new Expression('(' . $subquery->toSql() . ') ' . $grammar->wrap('posts_ft')),
                $query->raw('posts_ft.discussion_id'),
                '=',
                'discussions.id'
            )
            ->leftJoin(
                new Expression('(' . $exactPostsDiscussionsQuery->toSql() . ') ' . $grammar->wrap('exact_posts')),
                $query->raw('exact_posts.discussion_id'),
                '=',
                'discussions.id'
            )
            ->where(function (\Illuminate\Database\Query\Builder $query) {
                $query->whereNotNull('posts_ft.most_relevant_post_id');
            })
            ->groupBy('discussions.id')
            ->addBinding($subquery->getBindings(), 'join')
            ->addBinding($exactPostsDiscussionsQuery->getBindings(), 'join');

        $search->setDefaultSort(function ($query) use (
            $postExactIds,
            $postExactIdsSql,
            $postIdsSql,
            $postIdsMerged
        ) {
            $query
                ->orderByRaw(
                    'CASE ' .
                    'WHEN exact_posts.discussion_id IS NOT NULL THEN 0 ' .
                    'WHEN posts_ft.most_relevant_post_id IS NOT NULL THEN 1 ' .
                    'ELSE 2 END',
                    []
                )
                ->orderByRaw(
                    'CASE ' .
                    'WHEN exact_posts.discussion_id IS NOT NULL THEN FIELD(posts_ft.most_relevant_post_id' . $postExactIdsSql . ') ' .
                    'WHEN posts_ft.most_relevant_post_id IS NOT NULL THEN FIELD(posts_ft.most_relevant_post_id' . $postIdsSql . ') ' .
                    'ELSE 0 END',
                    array_merge($postExactIds, $postIdsMerged)
                );
        });
    }
}
