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

namespace Wenprise\Alipay\Symfony\Component\HttpFoundation\RequestMatcher;

use Wenprise\Alipay\Symfony\Component\HttpFoundation\Request;
use Wenprise\Alipay\Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Checks the Request content is valid JSON.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IsJsonRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request): bool
    {
        return json_validate($request->getContent());
    }
}
