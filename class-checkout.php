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
        $this->icon = apply_filters('omnipay_alipay_icon', null);

        $this->supports = [];

        // 被 init_settings() 加载的基础设置
        $this->init_form_fields();

        $this->init_settings();

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

        // 设置是否应该重命名按钮。
        $this->order_button_text = apply_filters('woocommerce_Alipay_button_text', __('Proceed to Alipay', 'wprs-wc-alipay'));

        // 保存设置
        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

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
            'environment'       => [
                'title'       => __(' Alipay Sanbox Mode', 'wprs-wc-alipay'),
                'label'       => __('Enable Alipay Sanbox Mode', 'wprs-wc-alipay'),
                'type'        => 'checkbox',
                'description' => sprintf(__('Alipay sandbox can be used to test payments. Sign up for an account <a href="%s">here</a>',
                    'wprs-wc-alipay'),
                    'https://sandbox.Alipay.com'),
                'default'     => 'no',
            ],
            'title'             => [
                'title'   => __('Title', 'wprs-wc-alipay'),
                'type'    => 'text',
                'default' => __('Alipay', 'wprs-wc-alipay'),
            ],
            'description'       => [
                'title'   => __('Description', 'wprs-wc-alipay'),
                'type'    => 'textarea',
                'default' => __('Pay securely using Alipay', 'wprs-wc-alipay'),
                'css'     => 'max-width:350px;',
            ],
            'app_id'            => [
                'title'       => __('App ID', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => __('Enter your Alipay Partner Number.', 'wprs-wc-alipay'),
            ],
            'private_key'       => [
                'title'       => __('Private Key', 'wprs-wc-alipay'),
                'type'        => 'textarea',
                'description' => __('Enter your Alipay secret key.', 'wprs-wc-alipay'),
            ],
            'alipay_public_key' => [
                'title'       => __('Alipay Public Key Key', 'wprs-wc-alipay'),
                'type'        => 'textarea',
                'description' => __('Enter your Alipay Seller Email.', 'wprs-wc-alipay'),
            ],
        ];

        if ( ! in_array($this->current_currency, ['RMB', 'CNY'])) {

            $this->form_fields[ 'exchange_rate' ] = [
                'title'       => __('Exchange Rate', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => sprintf(__("Please set the %s against Chinese Yuan exchange rate, eg if your currency is US Dollar, then you should enter 6.19",
                    'wprs-wc-wechatpay'), $this->current_currency),
            ];

        }
    }


    /**
     * 管理选项
     */
    public function admin_options()
    { ?>

        <h3><?php echo ( ! empty($this->method_title)) ? $this->method_title : __('Settings', 'woocommerce'); ?></h3>

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
            echo '<div class="error"><p>' . sprintf(__('WeChatPay is enabled, but the store currency is not set to Chinese Yuan. Please <a href="%1s">set the %2s against the Chinese Yuan exchange rate</a>.',
                    'wechatpay'), admin_url('admin.php?page=wc-settings&tab=checkout&section=wprs-wc-alipay#woocommerce_wprs-wc-alipay_exchange_rate'),
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
         * @var \omnipay\Alipay\AbstractAopGateway | \omnipay\Alipay\AopWapGateway | \Omnipay\Alipay\AopPageGateway $gateway
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

        do_action('wenprise_woocommerce_alipay_before_process_payment');

        // 调用响应的方法来处理支付
        try {
            $gateway = $this->get_gateway();

            $order_data = apply_filters('woocommerce_wenprise_alipay_args',
                [
                    'out_trade_no' => $order_no,
                    'subject'      => __('Pay for order #', 'wprs-wc-alipay') . $order_no . __(' At ', 'wprs-wc-alipay') . get_bloginfo('name'),
                    'body'         => __('Pay for order #', 'wprs-wc-alipay') . $order_no . __(' At ', 'wprs-wc-alipay') . get_bloginfo('name'),
                    'total_amount' => $order->get_total(),
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
                wc_empty_cart();

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
                    'result'   => 'fail',
                    'redirect' => '',
                ];
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
            $order->add_order_note(sprintf("%s Payments Failed: '%s'", $this->method_title, $error));
            wc_add_notice($error, "error");

            return [
                'result'   => 'fail',
                'redirect' => '',
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
        dd($order_id . $amount . $reason);

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
            $request->setParams(array_merge($_POST, $_GET));

            try {

                /** @var \Omnipay\Alipay\Responses\AopTradeQueryResponse $response */
                $response = $request->send();

                $this->log($response);

                if ($response->isPaid()) {

                    $order->payment_complete();

                    // 添加订单备注
                    $order->add_order_note(sprintf(__('Alipay payment complete (Alipay ID: %s)', 'wprs-wc-alipay'), $_REQUEST[ 'trade_no' ]));

                    wp_redirect($this->get_return_url($order));
                    exit;
                } else {
                    $error = $response->getMessage();
                    $order->add_order_note(sprintf("%s Payments Failed: '%s'", $this->method_title, $error));
                    wc_add_notice($error, 'error');
                    $this->log($error);
                    wp_redirect(wc_get_checkout_url());
                    exit;
                }

            } catch (\Exception $e) {

                $error = $e->getMessage();

                wc_add_notice($error, 'error');
                $this->log($error);
                wp_redirect(wc_get_checkout_url());
                exit;

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
