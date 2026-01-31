<?php

namespace ClarkWinkelmann\Scout\Search;

use Flarum\Search\GambitInterface;
use Flarum\Search\SearchState;
use Flarum\Settings\SettingsRepositoryInterface;
use ClarkWinkelmann\Scout\Search\Ranking\DefaultRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\ExactTitlePostRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\PostsOnlyRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\RankingStrategyInterface;
use ClarkWinkelmann\Scout\Search\Ranking\TitleFirstRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\TitleOnlyRankingStrategy;

class DiscussionGambit implements GambitInterface
{
    public function apply(SearchState $search, $bit)
    {
        $settings = resolve(SettingsRepositoryInterface::class);
        $strategyKey = $settings->get('clarkwinkelmann-scout.rankingStrategy') ?: 'default';

        $strategy = $this->resolveStrategy($strategyKey);
        $strategy->apply($search, $bit);
    }

    protected function resolveStrategy(string $strategyKey): RankingStrategyInterface
    {
        switch ($strategyKey) {
            case 'title_first':
                return new TitleFirstRankingStrategy();
            case 'exact_title_post':
                return new ExactTitlePostRankingStrategy();
            case 'title_only':
                return new TitleOnlyRankingStrategy();
            case 'posts_only':
                return new PostsOnlyRankingStrategy();
            case 'default':
            default:
                return new DefaultRankingStrategy();
        }
    }
}
