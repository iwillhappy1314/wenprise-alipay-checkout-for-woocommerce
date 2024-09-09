<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Discovery\Strategy;

use Wenprise\Alipay\Http\Discovery\Exception\StrategyUnavailableException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface DiscoveryStrategy
{
    /**
     * Find a resource of a specific type.
     *
     * @param string $type
     *
     * @return array The return value is always an array with zero or more elements. Each
     *               element is an array with two keys ['class' => string, 'condition' => mixed].
     *
     * @throws StrategyUnavailableException if we cannot use this strategy
     */
    public static function getCandidates($type);
}
