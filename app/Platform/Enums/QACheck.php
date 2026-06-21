<?php

namespace App\Platform\Enums;

/**
 * Finite set of check types the QA evaluator can execute.
 * WorkerContract::qaRequirements() may only use these constants.
 *
 * Evaluator reads the check type and executes the corresponding
 * logic against the transaction's stage output.
 */
class QACheck
{
    // Stage produced any output at all
    const OUTPUT_NOT_EMPTY = 'output_not_empty';

    // A specific field in the stage output is not null
    const FIELD_NOT_NULL = 'field_not_null';

    // A specific field in the stage output is not empty string or null
    const FIELD_NOT_EMPTY = 'field_not_empty';

    // A numeric field is above a threshold (0–1 for confidence scores)
    const VALUE_ABOVE = 'value_above';

    // A string field matches one of the allowed values
    const STATUS_IN = 'status_in';

    // A field, if present, passes email validation
    const VALID_EMAIL = 'valid_email';

    // The stage completed without the transaction entering 'failed' status
    const STAGE_NOT_FAILED = 'stage_not_failed';

    public static function all(): array
    {
        return [
            self::OUTPUT_NOT_EMPTY,
            self::FIELD_NOT_NULL,
            self::FIELD_NOT_EMPTY,
            self::VALUE_ABOVE,
            self::STATUS_IN,
            self::VALID_EMAIL,
            self::STAGE_NOT_FAILED,
        ];
    }
}
