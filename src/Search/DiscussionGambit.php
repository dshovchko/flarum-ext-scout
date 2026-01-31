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
    protected SettingsRepositoryInterface $settings;

    /**
     * @var array<string, RankingStrategyInterface>
     */
    protected array $strategies = [];

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function apply(SearchState $search, $bit)
    {
        $strategyKey = $this->settings->get('clarkwinkelmann-scout.rankingStrategy') ?: 'default';

        $strategy = $this->resolveStrategy($strategyKey);
        $strategy->apply($search, $bit);
    }

    protected function resolveStrategy(string $strategyKey): RankingStrategyInterface
    {
        if (isset($this->strategies[$strategyKey])) {
            return $this->strategies[$strategyKey];
        }

        switch ($strategyKey) {
            case 'title_first':
                $strategy = new TitleFirstRankingStrategy();
                break;
            case 'exact_title_post':
                $strategy = new ExactTitlePostRankingStrategy();
                break;
            case 'title_only':
                $strategy = new TitleOnlyRankingStrategy();
                break;
            case 'posts_only':
                $strategy = new PostsOnlyRankingStrategy();
                break;
            case 'default':
            default:
                $strategy = new DefaultRankingStrategy();
                break;
        }

        $this->strategies[$strategyKey] = $strategy;

        return $strategy;
    }
}
