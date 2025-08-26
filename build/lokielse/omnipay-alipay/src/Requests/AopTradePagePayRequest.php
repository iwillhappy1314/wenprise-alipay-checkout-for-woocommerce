<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradePagePayResponse;

/**
 * Class AopTradePagePayRequest
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/doc2/detail.htm?treeId=270&articleId=105901&docType=1
 */
class AopTradePagePayRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.page.pay';

    protected $returnable = true;

    protected $notifiable = true;


    public function sendData($data)
    {
        return $this->response = new AopTradePagePayResponse($this, $data);
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent('subject', 'out_trade_no', 'total_amount', 'product_code');
    }


    protected function getRequestUrl($data)
    {
        $url = sprintf('%s?%s', $this->getEndpoint(), http_build_query($data));

        return $url;
    }
}
