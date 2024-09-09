<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Money\Exception;

use Wenprise\Alipay\Money\Exception;

/**
 * Thrown when trying to get ISO currency that does not exists.
 *
 * @author Frederik Bosch <f.bosch@genkgo.nl>
 */
final class UnknownCurrencyException extends \DomainException implements Exception
{
}
