<?php

namespace App\Support\Signals;

use Illuminate\Support\Collection;

class TradeSignalTuningFeedbackLoop
{
    public function __construct(
        private readonly TradeSignalStrategyComparison $strategyComparison,
    ) {
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function reviewCandidates(): Collection
    {
        return $this->strategyComparison
            ->leaderboard()
            ->map(function (array $strategy): array {
                $decision = $this->decisionFor($strategy);

                return [
                    'strategy_key' => $strategy['strategy_key'],
                    'decision' => $decision['decision'],
                    'review_cadence' => $decision['review_cadence'],
                    'confidence' => $decision['confidence'],
                    'reasons' => $decision['reasons'],
                    'candidate_tuning_inputs' => $decision['candidate_tuning_inputs'],
                    'validation_expectations' => $decision['validation_expectations'],
                    'sample_size_note' => $strategy['sample_size_note'],
                    'evidence' => [
                        'signal_count' => $strategy['signal_count'],
                        'resolved_count' => $strategy['resolved_count'],
                        'win_rate' => $strategy['win_rate'],
                        'target_hit_rate' => $strategy['target_hit_rate'],
                        'stop_hit_rate' => $strategy['stop_hit_rate'],
                        'average_r_multiple' => $strategy['average_r_multiple'],
                        'bullish_signal_count' => $strategy['bullish_signal_count'],
                        'bearish_signal_count' => $strategy['bearish_signal_count'],
                        'comparison_score' => $strategy['comparison_score'],
                    ],
                ];
            })
            ->sortBy([
                ['review_cadence', 'asc'],
                ['strategy_key', 'asc'],
            ])
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function strategyRecommendation(string $strategyKey): array
    {
        $detail = $this->strategyComparison->detail($strategyKey);
        $decision = $this->decisionFor($detail);

        return [
            'strategy_key' => $strategyKey,
            'decision' => $decision['decision'],
            'review_cadence' => $decision['review_cadence'],
            'confidence' => $decision['confidence'],
            'reasons' => $decision['reasons'],
            'candidate_tuning_inputs' => $decision['candidate_tuning_inputs'],
            'validation_expectations' => $decision['validation_expectations'],
            'sample_size_note' => $detail['sample_size_note'],
            'symbol_breakdown' => $detail['symbol_breakdown'],
            'timeframe_breakdown' => $detail['timeframe_breakdown'],
            'direction_breakdown' => $detail['direction_breakdown'],
            'evidence' => [
                'signal_count' => $detail['signal_count'],
                'resolved_count' => $detail['resolved_count'],
                'win_rate' => $detail['win_rate'],
                'target_hit_rate' => $detail['target_hit_rate'],
                'stop_hit_rate' => $detail['stop_hit_rate'],
                'average_r_multiple' => $detail['average_r_multiple'],
                'bullish_signal_count' => $detail['bullish_signal_count'],
                'bearish_signal_count' => $detail['bearish_signal_count'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $strategy
     * @return array<string, mixed>
     */
    private function decisionFor(array $strategy): array
    {
        $signalCount = (int) ($strategy['signal_count'] ?? 0);
        $resolvedCount = (int) ($strategy['resolved_count'] ?? 0);
        $winRate = $strategy['win_rate'];
        $averageR = $strategy['average_r_multiple'];
        $stopHitRate = $strategy['stop_hit_rate'];

        $reasons = [];
        $candidateInputs = [];

        if ($signalCount < 5 || $resolvedCount < 3) {
            return [
                'decision' => 'observe_longer',
                'review_cadence' => 'weekly',
                'confidence' => 'low',
                'reasons' => ['Sample size is still too small for confident tuning decisions.'],
                'candidate_tuning_inputs' => [],
                'validation_expectations' => $this->validationExpectations('observe_longer'),
            ];
        }

        if ($winRate !== null && $winRate < 40) {
            $reasons[] = 'Win rate is materially weak for the current sample.';
            $candidateInputs[] = 'confidence threshold effectiveness';
            $candidateInputs[] = 'signal frequency vs quality tradeoff';
        }

        if ($averageR !== null && $averageR < 0) {
            $reasons[] = 'Average R multiple is negative across resolved outcomes.';
            $candidateInputs[] = 'ranking score threshold quality';
        }

        if ($stopHitRate !== null && $stopHitRate > 55) {
            $reasons[] = 'Stop hit rate is elevated, suggesting poor downside containment.';
            $candidateInputs[] = 'symbol-specific failure concentration';
            $candidateInputs[] = 'timeframe-specific degradation';
        }

        if ($winRate !== null && $winRate >= 65 && $averageR !== null && $averageR > 0) {
            return [
                'decision' => 'promote_strategy',
                'review_cadence' => 'monthly',
                'confidence' => $signalCount >= 10 ? 'high' : 'medium',
                'reasons' => ['Strategy shows strong win rate and positive average R outcome.'],
                'candidate_tuning_inputs' => ['consider increasing allocation or review priority'],
                'validation_expectations' => $this->validationExpectations('promote_strategy'),
            ];
        }

        if ($reasons !== []) {
            return [
                'decision' => 'tune_thresholds',
                'review_cadence' => 'weekly',
                'confidence' => $signalCount >= 10 ? 'medium' : 'low',
                'reasons' => $reasons,
                'candidate_tuning_inputs' => array_values(array_unique($candidateInputs)),
                'validation_expectations' => $this->validationExpectations('tune_thresholds'),
            ];
        }

        return [
            'decision' => 'no_change',
            'review_cadence' => 'monthly',
            'confidence' => 'medium',
            'reasons' => ['Current metrics do not justify a tuning change.'],
            'candidate_tuning_inputs' => [],
            'validation_expectations' => $this->validationExpectations('no_change'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationExpectations(string $decision): array
    {
        return match ($decision) {
            'tune_thresholds' => [
                'success_window' => 'Review after at least 10 resolved signals or 30 days, whichever comes later.',
                'success_criteria' => 'Win rate and average R should improve without collapsing signal quality or increasing unresolved noise excessively.',
            ],
            'promote_strategy' => [
                'success_window' => 'Validate over the next monthly review cycle.',
                'success_criteria' => 'Performance leadership should remain stable with healthy sample growth.',
            ],
            'observe_longer' => [
                'success_window' => 'Wait for more resolved signals before changing thresholds.',
                'success_criteria' => 'Gather enough data for a more confident comparison.',
            ],
            default => [
                'success_window' => 'Revisit on the next scheduled review cadence.',
                'success_criteria' => 'No material degradation should appear before the next review.',
            ],
        };
    }
}
