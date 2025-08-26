<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\DataServiceBillDownloadUrlQueryRequest;

class DataServiceBillDownloadUrlQueryResponse extends AbstractAopResponse
{
    protected $key = 'alipay_data_dataservice_bill_downloadurl_query_response';

    /**
     * @var DataServiceBillDownloadUrlQueryRequest
     */
    protected $request;
}
