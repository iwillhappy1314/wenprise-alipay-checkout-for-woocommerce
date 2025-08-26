<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 08-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Wenprise\Alipay\Money\Exception;

use InvalidArgumentException as CoreInvalidArgumentException;
use Wenprise\Alipay\Money\Exception;

final class InvalidArgumentException extends CoreInvalidArgumentException implements Exception
{
    /** @psalm-pure */
    public static function divisionByZero(): self
    {
        return new self('Cannot compute division with a zero divisor');
    }

    /** @psalm-pure */
    public static function moduloByZero(): self
    {
        return new self('Cannot compute modulo with a zero divisor');
    }
}
