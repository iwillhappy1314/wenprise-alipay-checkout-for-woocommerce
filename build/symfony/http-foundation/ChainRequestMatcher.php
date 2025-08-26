<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by __root__ on 08-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Symfony\Component\HttpFoundation;

/**
 * ChainRequestMatcher verifies that all checks match against a Request instance.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ChainRequestMatcher implements RequestMatcherInterface
{
    /**
     * @param iterable<RequestMatcherInterface> $matchers
     */
    public function __construct(private iterable $matchers)
    {
    }

    public function matches(Request $request): bool
    {
        foreach ($this->matchers as $matcher) {
            if (!$matcher->matches($request)) {
                return false;
            }
        }

        return true;
    }
}
