<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Money\Exception;

use Wenprise\Alipay\Money\Currency;
use Wenprise\Alipay\Money\Exception;

/**
 * Thrown when there is no currency pair (rate) available for the given currencies.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class UnresolvableCurrencyPairException extends \InvalidArgumentException implements Exception
{
    /**
     * Creates an exception from Currency objects.
     *
     * @return UnresolvableCurrencyPairException
     */
    public static function createFromCurrencies(Currency $baseCurrency, Currency $counterCurrency)
    {
        $message = sprintf(
            'Cannot resolve a currency pair for currencies: %s/%s',
            $baseCurrency->getCode(),
            $counterCurrency->getCode()
        );

        return new self($message);
    }
}
