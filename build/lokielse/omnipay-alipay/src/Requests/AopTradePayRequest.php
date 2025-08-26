<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradeCancelResponse;
use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradePayResponse;
use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradeQueryResponse;

/**
 * Class AopTradePayRequest
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 *
 * @link    https://doc.open.alipay.com/docs/api.htm?docType=4&apiId=850
 */
class AopTradePayRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.pay';

    protected $notifiable = true;

    protected $polling = true;

    protected $pollingWait = 3;

    protected $pollingAttempts = 10;

    /**
     * @var AopTradePayResponse|AopTradeQueryResponse|AopTradeCancelResponse
     */
    protected $response;


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return \Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradePayResponse|\Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradeQueryResponse
     * @throws \Wenprise\Alipay\Psr\Http\Client\Exception\NetworkException
     * @throws \Wenprise\Alipay\Psr\Http\Client\Exception\RequestException
     */
    public function sendData($data)
    {
        $data = parent::sendData($data);

        $this->response = new AopTradePayResponse($this, $data);

        if ($this->response->isWaitPay() && $this->polling) {
            $this->polling();
        }

        return $this->response;
    }


    /**
     * @link https://img.alicdn.com/top/i1/LB14VRALXXXXXcnXXXXXXXXXXXX
     */
    protected function polling()
    {
        $currentAttempt = 0;

        while ($currentAttempt++ < $this->pollingAttempts) {
            /**
             * Query Order Trade Status
             */
            $this->query();

            if ($this->response->getCode() >= 40000) {
                break;
            } elseif ($this->response->isPaid()) {
                break;
            } elseif ($this->response->isClosed()) {
                break;
            }

            sleep($this->pollingWait);
        }

        /**
         * Close Order
         */
        if ($this->response->isWaitPay()) {
            $this->cancel();
        }
    }


    protected function query()
    {
        $request = new AopTradeQueryRequest($this->httpClient, $this->httpRequest);
        $request->initialize($this->parameters->all());
        $request->setEndpoint($this->getEndpoint());
        $request->setPrivateKey($this->getPrivateKey());
        $request->setBizContent(
            ['out_trade_no' => $this->getBizData('out_trade_no')]
        );

        $this->response = $request->send();
    }


    protected function cancel()
    {
        $request = new AopTradeCancelRequest($this->httpClient, $this->httpRequest);
        $request->initialize($this->parameters->all());
        $request->setEndpoint($this->getEndpoint());
        $request->setPrivateKey($this->getPrivateKey());
        $request->setBizContent(
            ['out_trade_no' => $this->getBizData('out_trade_no')]
        );

        $this->response = $request->send();
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent(
            'out_trade_no',
            'scene',
            'auth_code',
            'subject'
        );

        $this->validateBizContentOne(
            'total_amount',
            'discountable_amount',
            'undiscountable_amount'
        );
    }


    /**
     * @param boolean $polling
     *
     * @return \Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradePayRequest
     */
    public function setPolling($polling)
    {
        $this->polling = $polling;

        return $this;
    }


    /**
     * @param int $pollingWait
     *
     * @return \Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradePayRequest
     */
    public function setPollingWait($pollingWait)
    {
        $this->pollingWait = $pollingWait;

        return $this;
    }


    /**
     * @param int $pollingAttempts
     *
     * @return \Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradePayRequest
     */
    public function setPollingAttempts($pollingAttempts)
    {
        $this->pollingAttempts = $pollingAttempts;

        return $this;
    }
}
