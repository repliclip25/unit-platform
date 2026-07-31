<?php

namespace App\Platform\Services;

/**
 * Picks the right email_templates row for a category and (optionally) a
 * specific cadence round — used by both SelectTemplateJob (round 1) and
 * DraftEmailJob (rounds 2/3, re-resolving instead of reusing round 1's
 * frozen template_output). Precedence, most to least specific:
 *
 *   1. Tenant's own template for this category + this exact round
 *   2. Tenant's own template for this category with no round set (applies to all rounds)
 *   3. Platform default for this category + this exact round
 *   4. Platform default for this category with no round set
 *   5. Generic "Other" platform default
 */
class TemplateResolver
{
    public static function resolve(string $category, array $userTemplates, array $defaultTemplates, ?int $round = null): array
    {
        $field = fn($t, $k) => is_object($t) ? ($t->{$k} ?? null) : ($t[$k] ?? null);

        $matches = fn($t, $cat, $r) => $field($t, 'category') === $cat
            && ($r === null ? empty($field($t, 'cadence_round')) : (int) $field($t, 'cadence_round') === $r);

        $template = null;

        if ($round !== null) {
            $template = collect($userTemplates)->first(fn($t) => $matches($t, $category, $round));
        }
        if (!$template) {
            $template = collect($userTemplates)->first(fn($t) => $matches($t, $category, null));
        }
        if (!$template && $round !== null) {
            $template = collect($defaultTemplates)->first(fn($t) => $field($t, 'is_default') && $matches($t, $category, $round));
        }
        if (!$template) {
            $template = collect($defaultTemplates)->first(fn($t) => $field($t, 'is_default') && $matches($t, $category, null));
        }
        if (!$template) {
            $template = collect($defaultTemplates)->first(fn($t) => $field($t, 'is_default') && $matches($t, 'Other', null));
        }

        return $template ? (array) $template : [];
    }

    /**
     * Picks a "system message" template — one scoped to a pipeline stage
     * (reminders, nudges, the closing notice) rather than a renewal
     * category. Precedence, most to least specific:
     *
     *   1. Tenant's own template for this stage + this exact tone
     *   2. Tenant's own template for this stage with no tone set (applies at any attempt)
     *   3. Platform default for this stage + this exact tone
     *   4. Platform default for this stage with no tone set
     *
     * Callers pre-filter $userTemplates/$defaultTemplates to the relevant
     * stage_key — this only disambiguates by tone within that set.
     */
    public static function resolveByStage(array $userTemplates, array $defaultTemplates, ?string $tone = null): array
    {
        $field = fn($t, $k) => is_object($t) ? ($t->{$k} ?? null) : ($t[$k] ?? null);

        $matchesTone = fn($t, $tn) => $tn === null ? empty($field($t, 'tone')) : $field($t, 'tone') === $tn;

        $template = null;

        if ($tone !== null) {
            $template = collect($userTemplates)->first(fn($t) => $matchesTone($t, $tone));
        }
        if (!$template) {
            $template = collect($userTemplates)->first(fn($t) => $matchesTone($t, null));
        }
        if (!$template && $tone !== null) {
            $template = collect($defaultTemplates)->first(fn($t) => $field($t, 'is_default') && $matchesTone($t, $tone));
        }
        if (!$template) {
            $template = collect($defaultTemplates)->first(fn($t) => $field($t, 'is_default') && $matchesTone($t, null));
        }

        return $template ? (array) $template : [];
    }
}
