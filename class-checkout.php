<?php

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Omnipay\Omnipay;

/**
 * Gateway class
 */
class Wenprise_Alipay_Gateway extends \WC_Payment_Gateway
{

    /**
     * @var bool 日志是否启用
     */
    public $debug_active = false;

    /**
     * @var WC_Logger Logger 实例
     */
    public $log = false;

    /**
     * @var bool
     */
    public $environment = false;

    /**
     * @var string
     */
    public $app_id = '';

    /**
     * @var string
     */
    public $private_key = '';

    /**
     * @var string
     */
    public $alipay_public_key = '';

    /**
     * @var string
     */
    public $current_currency = '';

    /**
     * @var bool
     */
    public $multi_currency_enabled = false;

    /**
     * @var string
     */
    public $exchange_rate = '';


    /**
     * 网关支持的功能
     *
     * @var array
     */
    public $supports = ['products', 'refunds'];

    /** @var string WC_API for the gateway - 作为回调 url 使用 */
    public $notify_url;

    function __construct()
    {

        // 支付方法的全局 ID
        $this->id = WENPRISE_ALIPAY_WOOCOMMERCE_ID;

        // 支付网关页面显示的支付网关标题
        $this->method_title = __("Alipay", 'wprs-wc-alipay');

        // 支付网关设置页面显示的支付网关标题
        $this->method_description = __("Alipay Payment Gateway for WooCommerce", 'wprs-wc-alipay');

        // 前端显示的支付网关名称
        $this->title = __("Alipay", 'wprs-wc-alipay');

        // 支付网关标题
        $this->icon = apply_filters('omnipay_alipay_icon',
            "data:image/svg+xml,%3Csvg t='1546482465278' class='icon' style='' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='5719' xmlns:xlink='http://www.w3.org/1999/xlink' width='32' height='32'%3E%3Cdefs%3E%3Cstyle type='text/css'%3E%3C/style%3E%3C/defs%3E%3Cpath d='M991.67 689.448v-472.296c0-101.81-82.623-184.433-184.493-184.433h-590.414c-101.81 0-184.433 82.623-184.433 184.433v590.414c0 101.81 82.563 184.433 184.433 184.433h590.414c90.717 0 166.206-65.595 181.615-151.876-48.926-21.165-260.941-112.722-371.385-165.427-84.062 101.81-172.082 162.908-304.771 162.908s-221.248-81.724-210.636-181.735c7.015-65.655 52.044-172.981 247.629-154.574 103.069 9.653 150.257 28.9 234.319 56.661 21.765-39.873 39.813-83.763 53.543-130.47h-372.764v-36.935h184.433v-66.314h-224.965v-40.652h224.965v-95.694c0 0 2.039-14.99 18.587-14.99h92.216v110.684h239.835v40.652h-239.835v66.314h195.646c-17.988 73.209-45.269 140.483-79.446 199.243 56.841 20.566 315.503 99.652 315.503 99.652v0 0zM297.947 774.29c-140.183 0-162.368-88.499-154.933-125.494 7.375-36.815 47.967-84.842 125.914-84.842 89.578 0 169.803 22.904 266.097 69.792-67.633 88.079-150.736 140.543-237.077 140.543v0z' p-id='5720' fill='%232EA0E9'%3E%3C/path%3E%3C/svg%3E");

        $this->supports = ['products', 'refunds'];

        $this->debug_active = false;

        $this->has_fields = false;

        $this->description = $this->get_option('description');

        $this->current_currency = get_option('woocommerce_currency');

        $this->multi_currency_enabled = in_array('woocommerce-multilingual/wpml-woocommerce.php',
                apply_filters('active_plugins', get_option('active_plugins'))) && get_option('icl_enable_multi_currency') == 'yes';

        $this->exchange_rate = $this->get_option('exchange_rate');

        // 转换设置为变量以方便使用
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }

        // 被 init_settings() 加载的基础设置
        $this->init_form_fields();

        $this->init_settings();

        // 设置是否应该重命名按钮。
        $this->order_button_text = apply_filters('woocommerce_Alipay_button_text', __('Proceed to Alipay', 'wprs-wc-alipay'));

        // 保存设置
        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        // 仪表盘通知
        add_action('admin_notices', [$this, 'requirement_checks']);

        // Hooks
        add_action('woocommerce_api_wprs-wc-alipay-return', [$this, 'listen_return_notify']);
        add_action('woocommerce_api_wprs-wc-alipay-notify', [$this, 'listen_return_notify']);
    }


    /**
     * 网关设置
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled'           => [
                'title'   => __('Enable / Disable', 'wprs-wc-alipay'),
                'label'   => __('Enable this payment gateway', 'wprs-wc-alipay'),
                'type'    => 'checkbox',
                'default' => 'no',
            ],
            // 'environment'       => [
            //     'title'       => __(' Alipay Sanbox Mode', 'wprs-wc-alipay'),
            //     'label'       => __('Enable Alipay Sanbox Mode', 'wprs-wc-alipay'),
            //     'type'        => 'checkbox',
            //     'description' => sprintf(__('Alipay sandbox can be used to test payments. Sign up for an account <a target="_blank" href="%s">here</a>',
            //         'wprs-wc-alipay'),
            //         'https://sandbox.Alipay.com'),
            //     'default'     => 'no',
            // ],
            'title'             => [
                'title'   => __('Title', 'wprs-wc-alipay'),
                'type'    => 'text',
                'default' => __('Alipay', 'wprs-wc-alipay'),
            ],
            'description'       => [
                'title'   => __('Description', 'wprs-wc-alipay'),
                'type'    => 'textarea',
                'default' => __('Pay securely using Alipay', 'wprs-wc-alipay'),
                'css'     => 'max-width:400px;',
            ],
            'app_id'            => [
                'title'       => __('App ID', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => __('Enter your Alipay APPID. 开放平台密钥中的"APPID"，授权回调地址：', 'wprs-wc-alipay') . home_url('wc-api/wprs-wc-alipay-notify/'),
            ],
            'private_key'       => [
                'title'       => __('Private Key', 'wprs-wc-alipay'),
                'type'        => 'textarea',
                'description' => __('Enter your Alipay secret key. (rsa_private_key.pem 文件的全部内容，创建订单时使用)', 'wprs-wc-alipay'),
                'css'         => 'height:300px',
            ],
            'alipay_public_key' => [
                'title'       => __('Alipay Public Key', 'wprs-wc-alipay'),
                'type'        => 'textarea',
                'description' => __('Enter your Alipay public key.（开放平台密钥中的"支付宝公钥"，验证支付结果时使用）', 'wprs-wc-alipay'),
            ],
        ];

        if ( ! in_array($this->current_currency, ['RMB', 'CNY'])) {

            $this->form_fields[ 'exchange_rate' ] = [
                'title'       => __('Exchange Rate', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => sprintf(__("Please set the %s against Chinese Yuan exchange rate, eg if your currency is US Dollar, then you should enter 6.19",
                    'wprs-wc-alipay'), $this->current_currency),
            ];

        }
    }


    /**
     * 管理选项
     */
    public function admin_options()
    { ?>

        <h3><?php echo ( ! empty($this->method_title)) ? $this->method_title : __('Settings', 'wprs-wc-alipay'); ?></h3>

        <?php echo ( ! empty($this->method_description)) ? wpautop($this->method_description) : ''; ?>

        <table class="form-table">
        <?php $this->generate_settings_html(); ?>
        </table><?php
    }


    /**
     * 是否为测试模式
     *
     * @return bool
     */
    public function is_test_mode()
    {
        return $this->environment == "yes";
    }


    /**
     * 是否为测试模式
     * to the payment parameter before redirecting offsite to 2co for payment.
     *
     * This filter controls enabling testing via sandbox account.
     *
     * @return bool
     */
    public function is_sandbox_test()
    {
        return apply_filters('woocommerce_wenprise_alipay_enable_sandbox', true);
    }


    /**
     * 检查是否满足需求
     *
     * @access public
     * @return void
     */
    function requirement_checks()
    {
        if ( ! in_array($this->current_currency, ['RMB', 'CNY']) && ! $this->exchange_rate) {
            echo '<div class="error"><p>' . sprintf(__('Alipay is enabled, but the store currency is ·not set to Chinese Yuan. Please <a href="%1s">set the %2s against the Chinese Yuan exchange rate</a>.',
                    'wprs-wc-alipay'), admin_url('admin.php?page=wc-settings&tab=checkout&section=wprs-wc-alipay#woocommerce_wprs-wc-alipay_exchange_rate'),
                    $this->current_currency) . '</p></div>';
        }
    }


    /**
     * 检查是否可用
     *
     * @return bool
     */
    function is_available()
    {

        $is_available = ('yes' === $this->enabled) ? true : false;

        if ($this->multi_currency_enabled) {
            if ( ! in_array(get_woocommerce_currency(), ['RMB', 'CNY']) && ! $this->exchange_rate) {
                $is_available = false;
            }
        } elseif ( ! in_array($this->current_currency, ['RMB', 'CNY']) && ! $this->exchange_rate) {
            $is_available = false;
        }

        if (wprs_is_wechat()) {
            $is_available = false;
        }

        return $is_available;
    }


    /**
     * 获取支付网关
     *
     * @return mixed
     */
    public function get_gateway()
    {

        /**
         * @var \omnipay\Alipay\AopWapGateway | \Omnipay\Alipay\AopPageGateway $gateway
         */

        if (wp_is_mobile()) {
            $gateway = Omnipay::create('Alipay_AopWap');
        } else {
            $gateway = Omnipay::create('Alipay_AopPage');
        }

        $gateway->setSignType('RSA2');
        $gateway->setAppId($this->app_id);
        $gateway->setPrivateKey($this->private_key);
        $gateway->setAlipayPublicKey($this->alipay_public_key);
        $gateway->setReturnUrl(WC()->api_request_url('wprs-wc-alipay-return'));
        $gateway->setNotifyUrl(WC()->api_request_url('wprs-wc-alipay-notify'));

        return $gateway;
    }


    /**
     * WooCommerce 支付处理 function/method.
     *
     * @inheritdoc
     *
     * @param int $order_id
     *
     * @return mixed
     */
    public function process_payment($order_id)
    {
        $order    = wc_get_order($order_id);
        $order_no = $order->get_order_number();
        $total    = $this->get_order_total();

        $exchange_rate = floatval($this->get_option('exchange_rate'));
        if ($exchange_rate <= 0) {
            $exchange_rate = 1;
        }

        $total = round($total * $exchange_rate, 2);

        // Empty cart.
        WC()->cart->empty_cart();

        do_action('wenprise_woocommerce_alipay_before_process_payment');

        // 调用响应的方法来处理支付
        $gateway = $this->get_gateway();

        $order_data = apply_filters('woocommerce_wenprise_alipay_args',
            [
                'out_trade_no' => $order_no,
                'subject'      => __('Pay for order #', 'wprs-wc-alipay') . $order_no . __(' At ', 'wprs-wc-alipay') . get_bloginfo('name'),
                'body'         => __('Pay for order #', 'wprs-wc-alipay') . $order_no . __(' At ', 'wprs-wc-alipay') . get_bloginfo('name'),
                'total_amount' => $total,
                'product_code' => 'FAST_INSTANT_TRADE_PAY',
                'show_url'     => get_permalink(),
            ]
        );

        // 生成订单并发送支付
        /** @var \Omnipay\Alipay\Requests\AbstractAopRequest $request */
        $request = $gateway->purchase();
        $request->setBizContent($order_data);

        /** @var \Omnipay\Alipay\Responses\AopTradePagePayResponse $response */
        $response = $request->send();

        do_action('woocommerce_wenprise_alipay_before_payment_redirect', $response);

        // 返回支付连接，由 Woo Commerce 跳转到支付宝支付
        if ($response->isRedirect()) {

            return [
                'result'   => 'success',
                'redirect' => $response->getRedirectUrl(),
            ];

        } else {
            $error = $response->getMessage();

            $order->add_order_note(sprintf("%s Payments Failed: '%s'", $this->method_title, $error));

            wc_add_notice($error, 'error');

            $this->log($error);

            return [
                'result'   => 'failure',
                'messages' => $response->getMessage(),
            ];
        }

    }


    /**
     * 处理退款
     *
     * If the gateway declares 'refunds' support, this will allow it to refund.
     * a passed in amount.
     *
     * @param  int    $order_id Order ID.
     * @param  float  $amount   Refund amount.
     * @param  string $reason   Refund reason.
     *
     * @return boolean True or false based on success, or a WP_Error object.
     */
    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $gateway = $this->get_gateway();
        $order   = wc_get_order($order_id);
        $total   = $this->get_order_total();

        $exchange_rate = floatval($this->get_option('exchange_rate'));
        if ($exchange_rate <= 0) {
            $exchange_rate = 1;
        }

        $total         = round($total * $exchange_rate, 2);
        $refund_amount = round($amount * $exchange_rate, 2);

        if ($refund_amount <= 0 || $refund_amount > $total) {
            false;
        }

        /** @var \Omnipay\Alipay\Requests\AopTradeRefundRequest $request */
        $request = $gateway->refund();

        $request->setBizContent([
            'out_trade_no'   => $order_id,
            'trade_no'       => $order->get_transaction_id(),
            'refund_amount'  => $refund_amount,
            'out_request_no' => date('YmdHis') . mt_rand(1000, 9999),
        ]);


        try {
            /** @var \Omnipay\Alipay\Responses\AopTradeRefundResponse $response */
            $response = $request->send();

            if ($response->isSuccessful()) {
                $order->add_order_note(
                    sprintf(__('Refunded %1$s', 'woocommerce'), $amount)
                );

                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }


    /**
     * 监听支付网关同步返回信息
     * 处理支付接口异步返回的信息
     */
    public function listen_return_notify()
    {

        if (isset($_REQUEST[ 'out_trade_no' ]) && ! empty($_REQUEST[ 'out_trade_no' ])) {

            $order   = wc_get_order($_REQUEST[ 'out_trade_no' ]);
            $gateway = $this->get_gateway();

            /**
             * 获取支付宝返回的参数
             */
            /** @var \Omnipay\Alipay\Requests\AopCompletePurchaseRequest $request */
            $request = $gateway->completePurchase();
            // $request->setParams(array_map('stripslashes', array_merge($_POST, $_GET)));
            $request->setParams(stripslashes_deep(array_merge($_POST, $_GET)));

            try {

                /** @var \Omnipay\Alipay\Responses\AopCompletePurchaseResponse $response */
                $response = $request->send();

                $this->log($response);

                if ($response->isPaid()) {

                    $order->payment_complete($_REQUEST[ 'trade_no' ]);

                    // Empty cart.
                    WC()->cart->empty_cart();

                    // 添加订单备注
                    $order->add_order_note(sprintf(__('Alipay payment complete (Alipay ID: %s)', 'wprs-wc-alipay'), $_REQUEST[ 'trade_no' ]));

                    if ($_SERVER[ 'REQUEST_METHOD' ] == 'POST') {
                        echo "success";
                    } else {
                        wp_redirect($this->get_return_url($order));
                    }

                } else {

                    $error = $response->getMessage();
                    $order->add_order_note(sprintf("%s Payments Failed: '%s'", $this->method_title, $error));
                    wc_add_notice($error, 'error');
                    $this->log($error);
                    wp_redirect(wc_get_checkout_url());

                }

            } catch (\Exception $e) {

                file_put_contents(get_theme_file_path("error.log"), print_r($e, true));

            }


        }

    }


    /**
     * Logger 辅助功能
     *
     * @param $message
     */
    public function log($message)
    {
        if ($this->debug_active) {
            if ( ! ($this->log)) {
                $this->log = new WC_Logger();
            }
            $this->log->add('woocommerce_wprs-wc-alipay', $message);
        }
    }

}
