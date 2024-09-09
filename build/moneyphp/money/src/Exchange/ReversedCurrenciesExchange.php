<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Money\Exchange;

use Wenprise\Alipay\Money\Currency;
use Wenprise\Alipay\Money\CurrencyPair;
use Wenprise\Alipay\Money\Exception\UnresolvableCurrencyPairException;
use Wenprise\Alipay\Money\Exchange;

/**
 * Tries the reverse of the currency pair if one is not available.
 *
 * Note: adding nested ReversedCurrenciesExchange could cause a huge performance hit.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class ReversedCurrenciesExchange implements Exchange
{
    /**
     * @var Exchange
     */
    private $exchange;

    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * {@inheritdoc}
     */
    public function quote(Currency $baseCurrency, Currency $counterCurrency)
    {
        try {
            return $this->exchange->quote($baseCurrency, $counterCurrency);
        } catch (UnresolvableCurrencyPairException $exception) {
            try {
                $currencyPair = $this->exchange->quote($counterCurrency, $baseCurrency);

                return new CurrencyPair($baseCurrency, $counterCurrency, 1 / $currencyPair->getConversionRatio());
            } catch (UnresolvableCurrencyPairException $inversedException) {
                throw $exception;
            }
        }
    }
}
