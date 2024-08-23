<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Money\Currencies;

use Cache\Taggable\TaggableItemInterface;
use Wenprise\Alipay\Money\Currencies;
use Wenprise\Alipay\Money\Currency;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Cache the result of currency checking.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class CachedCurrencies implements Currencies
{
    /**
     * @var Currencies
     */
    private $currencies;

    /**
     * @var CacheItemPoolInterface
     */
    private $pool;

    public function __construct(Currencies $currencies, CacheItemPoolInterface $pool)
    {
        $this->currencies = $currencies;
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function contains(Currency $currency)
    {
        $item = $this->pool->getItem('currency|availability|'.$currency->getCode());

        if (false === $item->isHit()) {
            $item->set($this->currencies->contains($currency));

            if ($item instanceof TaggableItemInterface) {
                $item->addTag('currency.availability');
            }

            $this->pool->save($item);
        }

        return $item->get();
    }

    /**
     * {@inheritdoc}
     */
    public function subunitFor(Currency $currency)
    {
        $item = $this->pool->getItem('currency|subunit|'.$currency->getCode());

        if (false === $item->isHit()) {
            $item->set($this->currencies->subunitFor($currency));

            if ($item instanceof TaggableItemInterface) {
                $item->addTag('currency.subunit');
            }

            $this->pool->save($item);
        }

        return $item->get();
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \CallbackFilterIterator(
            $this->currencies->getIterator(),
            function (Currency $currency) {
                $item = $this->pool->getItem('currency|availability|'.$currency->getCode());
                $item->set(true);

                if ($item instanceof TaggableItemInterface) {
                    $item->addTag('currency.availability');
                }

                $this->pool->save($item);

                return true;
            }
        );
    }
}
