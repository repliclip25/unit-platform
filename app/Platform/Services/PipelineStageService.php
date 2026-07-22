<?php

namespace App\Platform\Services;

class PipelineStageService
{
    // Collapses a contract's raw pipelineStages() into ordered, deduplicated
    // checkpoints using each stage's group/group_label/group_color — this is
    // what Desk and Transaction Detail show instead of a hand-copied stage
    // list. Adding a stage to the contract with an existing group key folds
    // it into that checkpoint automatically; a new group key adds a new
    // checkpoint. No page needs to change either way.
    public static function groupedStages(array $rawStages): array
    {
        $groups = [];

        foreach ($rawStages as $stage) {
            $groupKey = $stage['group'] ?? $stage['key'];

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'key'            => $groupKey,
                    'label'          => $stage['group_label'] ?? $stage['label'],
                    'color'          => $stage['group_color'] ?? '#6366f1',
                    'image'          => $stage['image'] ?? null,
                    'output_column'  => null,
                    'stage_keys'     => [],
                    'log_stage_keys' => [],
                ];
            }

            $groups[$groupKey]['stage_keys'][] = $stage['key'];
            $groups[$groupKey]['log_stage_keys'][] = $stage['log_stage_key'] ?? $stage['key'];

            // First stage in the group that actually has an output column
            // decides what "this checkpoint has run" means.
            if ($groups[$groupKey]['output_column'] === null && !empty($stage['output_column'])) {
                $groups[$groupKey]['output_column'] = $stage['output_column'];
            }
        }

        return array_values($groups);
    }

    // Further collapses the grouped checkpoints into a small number of
    // onboarding-narrative columns (e.g. "Analyzing" / "Drafting" / "Reply is
    // ready") — a coarser, page-specific view built on top of the same
    // groups, not a second raw-stage field. $labels maps a 1-based column
    // number to its onboarding-facing title; $columnSize groups are folded
    // into each column in order.
    public static function onboardingColumns(array $groupedStages, array $columnGroupCounts, array $labels): array
    {
        $columns = [];
        $cursor  = 0;

        foreach ($columnGroupCounts as $i => $count) {
            $slice = array_slice($groupedStages, $cursor, $count);
            $cursor += $count;

            $columns[] = [
                'num'        => $i + 1,
                'label'      => $labels[$i] ?? ('Step ' . ($i + 1)),
                'image'      => $slice[0]['image'] ?? null,
                'groups'     => $slice,
                'stage_keys' => collect($slice)->pluck('stage_keys')->flatten()->all(),
            ];
        }

        return $columns;
    }
}
