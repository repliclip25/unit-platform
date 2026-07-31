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
}
