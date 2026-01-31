<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use Flarum\Search\SearchState;

interface RankingStrategyInterface
{
    public function apply(SearchState $search, string $bit): void;
}
