<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\DataServiceBillDownloadUrlQueryResponse;
use Wenprise\Alipay\Omnipay\Common\Exception\InvalidRequestException;
use Wenprise\Alipay\Omnipay\Common\Message\ResponseInterface;

/**
 * Class DataServiceBillDownloadUrlQueryRequest
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/doc2/apiDetail.htm?apiId=1054&docType=4
 */
class DataServiceBillDownloadUrlQueryRequest extends AbstractAopRequest
{
    protected $method = 'alipay.data.dataservice.bill.downloadurl.query';


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $data = parent::sendData($data);

        return $this->response = new DataServiceBillDownloadUrlQueryResponse($this, $data);
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent(
            'bill_type',
            'bill_date'
        );
    }
}
