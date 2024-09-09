<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Money;

use Wenprise\Alipay\Money\Exception\UnknownCurrencyException;

/**
 * Implement this to provide a list of currencies.
 *
 * @author Mathias Verraes
 */
interface Currencies extends \IteratorAggregate
{
    /**
     * Checks whether a currency is available in the current context.
     *
     * @return bool
     */
    public function contains(Currency $currency);

    /**
     * Returns the subunit for a currency.
     *
     * @return int
     *
     * @throws UnknownCurrencyException If currency is not available in the current context
     */
    public function subunitFor(Currency $currency);
}
