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

    /** @var bool 日志是否启用 */
    public $debug_active = false;

    /** @var WC_Logger Logger 实例 */
    public $log = false;

    public $environment = false;

    public $app_id = false;
    public $private_key = false;
    public $alipay_public_key = false;

    /** @var string WC_API for the gateway - 作为回调 url 使用 */
    public $notify_url;

    function __construct()
    {

        // 支付方法的全局 ID
        $this->id = WENPRISE_ALIPAY_WOOCOMMERCE_ID;

        // 支付网关页面显示的支付网关标题
        $this->method_title = __("Alipay", 'wprs-woo-alipay');

        // 支付网关设置页面显示的支付网关标题
        $this->method_description = __("Alipay Payment Gateway for WooCommerce", 'wprs-woo-alipay');

        // 前端显示的支付网关名称
        $this->title = __("Alipay", 'wprs-woo-alipay');

        // 支付网关标题
        $this->icon = apply_filters('omnipay_alipay_icon', null);

        $this->supports = [];

        // 被 init_settings() 加载的基础设置
        $this->init_form_fields();

        $this->init_settings();

        $this->debug_active = true;
        $this->has_fields   = false;

        $this->description = $this->get_option('description');

        // 转换设置为变量以方便使用
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }

        // 设置是否应该重命名按钮。
        $this->order_button_text = apply_filters('woocommerce_Alipay_button_text', __('Proceed to Alipay', 'wprs-woo-alipay'));

        // 保存设置
        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        // Hooks
        add_action('woocommerce_api_wprs-alipay-return', [$this, 'listen_return_notify']);
        add_action('woocommerce_api_wprs-alipay-notify', [$this, 'listen_return_notify']);
    }


    /**
     * 网关设置
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled'           => [
                'title'   => __('Enable / Disable', 'wprs-woo-alipay'),
                'label'   => __('Enable this payment gateway', 'wprs-woo-alipay'),
                'type'    => 'checkbox',
                'default' => 'no',
            ],
            'environment'       => [
                'title'       => __(' Alipay Sanbox Mode', 'wprs-woo-alipay'),
                'label'       => __('Enable Alipay Sanbox Mode', 'wprs-woo-alipay'),
                'type'        => 'checkbox',
                'description' => sprintf(__('Alipay sandbox can be used to test payments. Sign up for an account <a href="%s">here</a>',
                    'wprs-woo-alipay'),
                    'https://sandbox.Alipay.com'),
                'default'     => 'no',
            ],
            'title'             => [
                'title'   => __('Title', 'wprs-woo-alipay'),
                'type'    => 'text',
                'default' => __('Alipay', 'wprs-woo-alipay'),
            ],
            'description'       => [
                'title'   => __('Description', 'wprs-woo-alipay'),
                'type'    => 'textarea',
                'default' => __('Pay securely using Alipay', 'wprs-woo-alipay'),
                'css'     => 'max-width:350px;',
            ],
            'app_id'            => [
                'title'       => __('App ID', 'wprs-woo-alipay'),
                'type'        => 'text',
                'description' => __('Enter your Alipay Partner Number.', 'wprs-woo-alipay'),
            ],
            'private_key'       => [
                'title'       => __('Private Key', 'wprs-woo-alipay'),
                'type'        => 'textarea',
                'description' => __('Enter your Alipay secret key.', 'wprs-woo-alipay'),
            ],
            'alipay_public_key' => [
                'title'       => __('Alipay Public Key Key', 'wprs-woo-alipay'),
                'type'        => 'textarea',
                'description' => __('Enter your Alipay Seller Email.', 'wprs-woo-alipay'),
            ],
        ];
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
     * 获取支付网关
     *
     * @return mixed
     */
    public function get_gateway()
    {

        /**
         * @var \omnipay\Alipay\AbstractAopGateway | \omnipay\Alipay\AopWapGateway | \Omnipay\WechatPay\BaseAbstractGateway $gateway
         */
        $gateway = Omnipay::create('Alipay_AopPage');
        $gateway->setSignType('RSA2');
        $gateway->setAppId($this->app_id);
        $gateway->setPrivateKey($this->private_key);
        $gateway->setAlipayPublicKey($this->alipay_public_key);
        $gateway->setReturnUrl(urldecode(WC()->api_request_url('wprs-alipay-return')));
        $gateway->setNotifyUrl(urldecode(WC()->api_request_url('wprs-alipay-notify')));

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
        $order = wc_get_order($order_id);

        do_action('wenprise_woocommerce_alipay_before_process_payment');

        // 调用响应的方法来处理支付
        try {
            $gateway = $this->get_gateway();

            // 账单及收货地址
            $formData = [
                'firstName' => $order->billing_first_name,
                'lastName'  => $order->billing_last_name,
                'email'     => $order->billing_email,
                'address1'  => $order->billing_address_1,
                'address2'  => $order->billing_address_2,
                'city'      => $order->billing_city,
                'state'     => $order->billing_state,
                'postcode'  => $order->billing_postcode,
                'country'   => $order->billing_country,
            ];


            // 获取购物车中的商品
            $order_cart = $order->get_items();

            // 构造购物车数组
            $cart = [];
            foreach ($order_cart as $product_id => $product) {
                $cart[] = [
                    'name'       => $product[ 'name' ],
                    'quantity'   => $product[ 'qty' ],
                    'price'      => $product[ 'line_total' ],
                    'product_id' => $product_id,
                ];
            }

            // 添加更多购物车数据
            if (($shipping_total = $order->get_total()) > 0) {
                $cart[] = [
                    'name'     => __('Shipping Fee', 'wprs-woo-alipay'),
                    'quantity' => 1,
                    'price'    => $shipping_total,
                ];
            }

            // 生成订单并发送支付
            /** @var \Omnipay\Alipay\Requests\AbstractAopRequest $request */
            $request = $gateway->purchase();
            $request->setBizContent(
                apply_filters('woocommerce_wenprise_alipay_args',
                    [
                        'out_trade_no'     => $order->get_order_number(),
                        'subject'          => __('Pay for order #', 'wprs-woo-alipay') . $order->get_order_number() . __(' At ',
                                'wprs-woo-alipay') . get_bloginfo('name'),
                        'body'             => __('Pay for order #', 'wprs-woo-alipay') . $order->get_order_number() . __(' At ',
                                'wprs-woo-alipay') . get_bloginfo('name'),
                        'total_amount'     => $order->get_total(),
                        'product_code'     => 'FAST_INSTANT_TRADE_PAY',
                        'spbill_create_ip' => '127.0.0.1',
                        'show_url'         => get_permalink(),
                    ]
                )
            );

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
                    $order->add_order_note(sprintf(__('Alipay payment complete (Alipay ID: %s)', 'wprs-woo-alipay'), $_REQUEST[ 'trade_no' ]));

                    wc_empty_cart();
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
            $this->log->add('woocommerce_wprs-woo-alipay', $message);
        }
    }

}
