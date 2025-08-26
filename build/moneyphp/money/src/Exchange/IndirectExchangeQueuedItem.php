<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 08-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Wenprise\Alipay\Money\Exchange;

use Wenprise\Alipay\Money\Currency;

/** @internal for sole consumption by {@see IndirectExchange} */
final class IndirectExchangeQueuedItem
{
    public bool $discovered  = false;
    public self|null $parent = null;

    public function __construct(public Currency $currency)
    {
    }
}
