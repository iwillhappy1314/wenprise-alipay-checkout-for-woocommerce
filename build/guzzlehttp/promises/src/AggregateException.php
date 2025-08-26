<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\GuzzleHttp\Promise;

/**
 * Exception thrown when too many errors occur in the some() or any() methods.
 */
class AggregateException extends RejectionException
{
    public function __construct($msg, array $reasons)
    {
        parent::__construct(
            $reasons,
            sprintf('%s; %d rejected promises', $msg, count($reasons))
        );
    }
}
