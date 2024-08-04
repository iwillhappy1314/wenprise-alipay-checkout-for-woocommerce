<?php
namespace Wenprise\Alipay;

/**
 *
 * trade_no | $order->get_transaction_id() :支付宝交易号，和商户订单号不能同时为空
 * out_trade_no | $order_id | $order->get_order_number() | $order->get_id(): 网站交易号
 *
 */

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Omnipay;

/**
 * Gateway class
 */
class PaymentGateway extends \WC_Payment_Gateway
{

    /**
     * @var \WC_Logger Logger 实例
     */
    public $log = false;

    /**
     * enabled_f2f
     */
    public $enabled_f2f = 'no';

    /**
     * @var bool
     */
    public $is_sandbox_mod = false;

    /**
     * @var string
     */
    private $order_prefix = '';

    /**
     * @var string
     */
    private $app_id = '';

    /**
     * @var string
     */
    private $private_key = '';

    /**
     * @var string
     */
    private $alipay_public_key = '';

    /**
     * @var string
     */
    private $cert_type;

    /**
     * @var string
     */
    private $alipay_cert_public_key_rsa2;

    /**
     * @var string
     */
    private $alipay_root_cert;

    /**
     * @var string
     */
    private $app_cert_publicKey;

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
     * @var bool 日志是否启用
     */
    public $is_debug_mod = false;

    /**
     * 网关支持的功能
     *
     * @var array
     */
    public $supports = ['products', 'refunds'];

    /** @var string WC_API for the gateway - 作为回调 url 使用 */
    public $notify_url;

    public function __construct()
    {
        // 支付方法的全局 ID
        $this->id = WENPRISE_ALIPAY_WOOCOMMERCE_ID;

        // 支付网关页面显示的支付网关标题
        $this->method_title = __('Alipay', 'wprs-wc-alipay');

        // 支付网关设置页面显示的支付网关标题
        $this->method_description = __('Alipay Payment Gateway for WooCommerce', 'wprs-wc-alipay');

        // 被 init_settings() 加载的基础设置
        $this->init_form_fields();

        $this->init_settings();

        // 转换设置为变量以方便使用
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }

        // 前端显示的支付网关名称
        $this->title = $this->get_option('title');

        // 支付网关标题
        $this->icon = apply_filters('omnipay_alipay_icon', WENPRISE_ALIPAY_ASSETS_URL . 'alipay.svg');

        $this->supports = ['products', 'refunds'];

        $this->has_fields = false;

        $this->is_debug_mod = 'yes' === $this->get_option('is_debug_mod');

        $this->is_sandbox_mod = 'yes' === $this->get_option('is_sandbox_mod');

        $this->description = $this->get_option('description');

        $this->current_currency = get_option('woocommerce_currency');

        $this->multi_currency_enabled = in_array('woocommerce-multilingual/wpml-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true) && get_option('icl_enable_multi_currency') === 'yes';

        $this->exchange_rate = $this->get_option('exchange_rate');

        // 保存设置
        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        // below is the hook you need for that purpose
        add_action('woocommerce_receipt_' . $this->id, [$this, 'receipt_page']);

        // 仪表盘通知
        add_action('admin_notices', [$this, 'requirement_checks']);

        // Hooks
        add_action('woocommerce_api_wprs-wc-alipay-return', [$this, 'listen_return_notify']);
        add_action('woocommerce_api_wprs-wc-alipay-notify', [$this, 'listen_return_notify']);
        add_action('woocommerce_api_wprs-wc-query-order', [$this, 'query_alipay_order']);

    }


    /**
     * 网关设置
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled'                     => [
                'title'   => __('Enable / Disable', 'wprs-wc-alipay'),
                'label'   => __('Enable this payment gateway', 'wprs-wc-alipay'),
                'type'    => 'checkbox',
                'default' => 'no',
            ],
            'enabled_f2f'                 => [
                'title'   => __('Enable Face2Face Payment', 'wprs-wc-alipay'),
                'label'   => __('Enable Face2Face Payment on PC', 'wprs-wc-alipay'),
                'type'    => 'checkbox',
                'default' => 'no',
            ],
            'is_sandbox_mod'              => [
                'title'       => __('Enable Alipay Sandbox Mode', 'wprs-wc-alipay'),
                'label'       => __('Enable Alipay Sandbox Mode', 'wprs-wc-alipay'),
                'type'        => 'checkbox',
                'description' => sprintf(__('Alipay sandbox can be used to test payments. Sign up for an account <a target="_blank" href="%s">here</a>', 'wprs-wc-alipay'), 'https://sandbox.Alipay.com'),
                'default'     => 'no',
            ],
            'title'                       => [
                'title'   => __('Title', 'wprs-wc-alipay'),
                'type'    => 'text',
                'default' => __('Alipay', 'wprs-wc-alipay'),
            ],
            'description'                 => [
                'title'   => __('Description', 'wprs-wc-alipay'),
                'type'    => 'textarea',
                'default' => __('Pay securely using Alipay', 'wprs-wc-alipay'),
                'css'     => 'max-width:400px;',
            ],
            'order_prefix'                => [
                'title'       => __('Order Number Prefix', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => __('Only alphabet or number Allowed', 'wprs-wc-alipay'),
                'default'     => __('WC-', 'wprs-wc-alipay'),
            ],
            'app_id'                      => [
                'title'       => __('App ID', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => __('Enter your Alipay APPID. 开放平台密钥中的"APPID"，授权回调地址：', 'wprs-wc-alipay') . home_url('wc-api/wprs-wc-alipay-notify/'),
            ],
            'private_key'                 => [
                'title'       => __('Private Key', 'wprs-wc-alipay'),
                'type'        => 'textarea',
                'description' => __('Enter your App secret key. (rsa_private_key.pem 文件的全部内容，创建订单时使用)', 'wprs-wc-alipay'),
                'css'         => 'height:300px',
            ],
            'cert_type'                   => [
                'title'       => __('signing mode', 'wprs-wc-alipay'),
                'type'        => 'select',
                'description' => __('Select the signing mode。', 'wprs-wc-alipay'),
                'default'     => 'public_key',
                'options'     => [
                    'public_key'             => __('Public Key', 'wprs-wc-alipay'),
                    'public_key_certificate' => __('Public Key Certificate', 'wprs-wc-alipay'),
                ],
            ],
            'alipay_public_key'           => [
                'title'       => __('Alipay Public Key', 'wprs-wc-alipay'),
                'type'        => 'textarea',
                'description' => __('Enter your Alipay public key.（开放平台密钥中的"支付宝公钥"，验证支付结果时使用）', 'wprs-wc-alipay'),
            ],
            'alipay_cert_public_key_rsa2' => [
                'title'       => __('alipayCertPublicKey_RSA2.crt Path', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => sprintf(__('Enter the absolute path of alipayCertPublicKey_RSA2.crt, this is your website root path: %s', 'wprs-wc-alipay'), ABSPATH),
            ],
            'alipay_root_cert'            => [
                'title'       => __('alipayRootCert.crt Path', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => sprintf(__('Enter The absolute path of alipayRootCert.crt, this is your website root path: %s', 'wprs-wc-alipay'), ABSPATH),
            ],
            'app_cert_publicKey'          => [
                'title'       => __('appCertPublicKey.crt Path', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => sprintf(__('Enter The absolute path of appCertPublicKey.crt, this is your website root path: %s', 'wprs-wc-alipay'), ABSPATH),
            ],
            'is_debug_mod'                => [
                'title'       => __('Debug Mode', 'wprs-wc-alipay'),
                'label'       => __('Enable debug mod', 'wprs-wc-alipay'),
                'type'        => 'checkbox',
                'description' => __('If checked, plugin will show program errors in frontend.', 'wprs-wc-alipay'),
                'default'     => 'no',
            ],
        ];

        if ( ! in_array($this->current_currency, ['RMB', 'CNY'])) {

            $this->form_fields[ 'exchange_rate' ] = [
                'title'       => __('Exchange Rate', 'wprs-wc-alipay'),
                'type'        => 'text',
                'description' => sprintf(__('Please set the %s against Chinese Yuan exchange rate, eg if your currency is US Dollar, then you should enter 6.19',
                    'wprs-wc-alipay'), $this->current_currency),
            ];

        }
    }


    /**
     * 管理选项
     */
    public function admin_options()
    { ?>

        <h3>
            <?php echo ( ! empty($this->method_title)) ? $this->method_title : __('Settings', 'wprs-wc-alipay'); ?>
            <?php wc_back_link(__('Return to payments', 'woocommerce'), admin_url('admin.php?page=wc-settings&tab=checkout')); ?>
        </h3>

        <?php echo ( ! empty($this->method_description)) ? wpautop($this->method_description) : ''; ?>

        <table class="form-table">
        <?php $this->generate_settings_html(); ?>
        </table><?php
    }


    /**
     * 检查是否满足需求
     *
     * @access public
     * @return void
     */
    public function requirement_checks()
    {
        if ( ! $this->exchange_rate && ! in_array($this->current_currency, ['RMB', 'CNY'])) {
            echo '<div class="error"><p>' . sprintf(__('Alipay is enabled, but the store currency is ·not set to Chinese Yuan. Please <a href="%1s">set the %2s against the Chinese Yuan exchange rate</a>.',
                    'wprs-wc-alipay'), admin_url('admin.php?page=wc-settings&tab=checkout&section=wprs-wc-alipay#woocommerce_wprs-wc-alipay_exchange_rate'),
                    $this->current_currency) . '</p></div>';
        }
    }


    /**
     * 获取订单号
     *
     * @param $order_id
     *
     * @return string
     */
    public function get_order_number($order_id): string
    {
        return $this->order_prefix . ltrim($order_id, '#');
    }


    /**
     * 检查是否可用
     *
     * @return bool
     */
    public function is_available(): bool
    {

        $is_available = 'yes' === $this->enabled;

        if ($this->multi_currency_enabled) {
            if ( ! $this->exchange_rate && ! in_array(get_woocommerce_currency(), ['RMB', 'CNY'])) {
                $is_available = false;
            }
        } elseif ( ! $this->exchange_rate && ! in_array($this->current_currency, ['RMB', 'CNY'])) {
            $is_available = false;
        }

        return $is_available;
    }


    /**
     * 获取支付网关
     *
     * @return \Omnipay\Alipay\AopPageGateway|\omnipay\Alipay\AopWapGateway|\Omnipay\Common\GatewayInterface
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function get_gateway()
    {

        /**
         * @var \omnipay\Alipay\AopWapGateway | \Omnipay\Alipay\AopPageGateway $gateway
         */

        if (wp_is_mobile()) {
            $gateway = Omnipay::create('Alipay_AopWap');
        } else {
            if ($this->enabled_f2f === 'yes') {
                $gateway = Omnipay::create('Alipay_AopF2F');
            } else {
                $gateway = Omnipay::create('Alipay_AopPage');
            }
        }

        $gateway->setAppId($this->app_id);
        $gateway->setSignType('RSA2');
        $gateway->setPrivateKey($this->private_key);

        if ($this->enabled_f2f !== 'yes') {
            $gateway->setReturnUrl(WC()->api_request_url('wprs-wc-alipay-return'));
        }

        $gateway->setNotifyUrl(WC()->api_request_url('wprs-wc-alipay-notify'));

        if ($this->cert_type === 'public_key_certificate') {
            $gateway->setAlipayRootCert($this->alipay_root_cert);
            $gateway->setAlipayPublicCert($this->alipay_cert_public_key_rsa2);
            $gateway->setAppCert($this->app_cert_publicKey);
            $gateway->setCheckAlipayPublicCert(true);
        } else {
            $gateway->setAlipayPublicKey($this->alipay_public_key);
        }

        $gateway->setAlipayPublicKey($this->alipay_public_key);

        if ($this->is_sandbox_mod) {
            $gateway->sandbox();
        }

        return $gateway;
    }


    /**
     * WooCommerce 支付处理 function/method.
     *
     * @inheritdoc
     *
     * @param int $order_id
     *
     * @return array|void
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function process_payment($order_id)
    {
        $order    = wc_get_order($order_id);
        $order_no = $this->get_order_number($order_id);
        $total    = $this->get_order_total();

        $exchange_rate = (float)$this->get_option('exchange_rate');
        if ($exchange_rate <= 0) {
            $exchange_rate = 1;
        }

        $total = round($total * $exchange_rate, get_option('woocommerce_price_num_decimals'));
        $total = number_format($total,get_option('woocommerce_price_num_decimals'),'.','');

        do_action('wenprise_woocommerce_alipay_before_process_payment');

        // 调用响应的方法来处理支付
        $gateway = $this->get_gateway();

        $order_data = apply_filters('woocommerce_wenprise_alipay_args',
            [
                'out_trade_no' => $order_no,
                'subject'      => sprintf(__('Pay for order %1$s at %2$s', 'wprs-wc-alipay'), $order_no, get_bloginfo('name')),
                'body'         => sprintf(__('Pay for order %1$s at %2$s', 'wprs-wc-alipay'), $order_no, get_bloginfo('name')),
                'total_amount' => $total,
                'show_url'     => $order->get_checkout_payment_url(),
            ]
        );

        if ($this->enabled_f2f !== 'yes') {
            $order_data[ 'product_code' ] = 'FAST_INSTANT_TRADE_PAY';
        }

        // 生成订单并发送支付
        /** @var \Omnipay\Alipay\Requests\AbstractAopRequest $request */
        $request = $gateway->purchase();
        $request->setBizContent($order_data);

        /** @var \Omnipay\Alipay\Responses\AopTradePagePayResponse|\Omnipay\Alipay\Responses\AopTradePreCreateResponse $response */
        $response = $request->send();

        // 生成订单后清空购物车，以免订单重复
        WC()->cart->empty_cart();

        do_action('woocommerce_wenprise_alipay_before_payment_redirect', $response);

        $order->update_meta_data('_gateway_payment_url', $response->getRedirectUrl());
        $order->save();

        // 返回支付连接，由 WooCommerce 跳转到支付宝支付
        if ($response->isSuccessful()) {

            if ($this->enabled_f2f === 'yes') {
                $order->update_meta_data('wprs_wc_alipay_f2f_qrcode', $response->getQrCode());
                $order->save();

                return [
                    'result'   => 'success',
                    'redirect' => $order->get_checkout_payment_url(true),
                ];
            }

            if ($response->isRedirect()) {

                return [
                    'result'      => 'success',
                    'redirect'    => $order->get_checkout_payment_url(true),
                    'payment_url' => $response->getRedirectUrl(),
                ];

            }

        } else {

            if ($this->is_debug_mod) {
                $error = $this->get_error_message($response);

                $this->log($error);
                wc_add_notice($error, 'error');
            }

            return [
                'result'   => 'failure',
                'messages' => $this->get_error_message($response),
            ];

        }

    }


    /**
     * 订单支付页面
     *
     * @param $order_id
     *
     */
    public function receipt_page($order_id)
    {
        $order    = wc_get_order($order_id);
        $code_url = $order->get_meta('wprs_wc_alipay_f2f_qrcode');
        ?>

        <div id="js-alipay-confirm-modal" data-order_id="<?= $order_id; ?>" class="rs-confirm-modal">
            <div class="rs-payment-box">

                <?php if ($this->enabled_f2f === 'yes') : ?>

                    <div class='rs-alert rs-alert--warning'>
                        <?= __('Please open alipay and scan this qrcode.', 'wprs-wc-alipay'); ?>
                    </div>

                    <div class="wprs-qrcode">
                        <div id="wprs_wc_alipay_f2f_qrcode"></div>
                    </div>

                    <script>
                      jQuery(document).ready(function($) {
                        $('#wprs_wc_alipay_f2f_qrcode').qrcode('<?= $code_url; ?>');
                      });
                    </script>

                <?php else: ?>

                    <div class="rs-instruction-box">
                        <svg t="1682582744808" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" p-id="6584" width="64" height="64"><path d="M512 960C264.577 960 64 759.424 64 512S264.577 64 512 64s448 200.576 448 448-200.577 448-448 448z m246.632-610.632c-12.491-12.491-32.742-12.491-45.233 0L456 606.767 310.601 461.368c-12.491-12.491-32.742-12.491-45.233 0s-12.491 32.742 0 45.233l167.703 167.703c0.104 0.107 0.191 0.223 0.297 0.328 6.249 6.249 14.441 9.372 22.632 9.368 8.191 0.004 16.383-3.118 22.632-9.368 0.106-0.105 0.193-0.222 0.297-0.328l279.703-279.703c12.491-12.491 12.491-32.742 0-45.233z" p-id="6585" fill="#16a34a"></path></svg>

                        <div class="rs-instruction-box__title">订单提交成功！</div>

                        <p>请核实上面的支付信息后，点击下面的按钮去支付宝支付。</p>
                    </div>

                    <div class="rs-flex rs-justify-center rs-mt-4 rs-action-block">
                        <a target="_blank" class="button alt rs-flex rs-payment-url rswc-button" href="<?= $order->get_meta('_gateway_payment_url'); ?>">
                            <svg t="1682581409962" class="icon" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" p-id="2628" width="24" height="24"><path d="M789 610.3c-38.7-12.9-90.7-32.7-148.5-53.6 34.8-60.3 62.5-129 80.7-203.6H530.5v-68.6h233.6v-38.3H530.5V132h-95.4c-16.7 0-16.7 16.5-16.7 16.5v97.8H182.2v38.3h236.3v68.6H223.4v38.3h378.4a667.18 667.18 0 0 1-54.5 132.9c-122.8-40.4-253.8-73.2-336.1-53-52.6 13-86.5 36.1-106.5 60.3-91.4 111-25.9 279.6 167.2 279.6C386 811.2 496 747.6 581.2 643 708.3 704 960 808.7 960 808.7V659.4s-31.6-2.5-171-49.1zM253.9 746.6c-150.5 0-195-118.3-120.6-183.1 24.8-21.9 70.2-32.6 94.4-35 89.4-8.8 172.2 25.2 269.9 72.8-68.8 89.5-156.3 145.3-243.7 145.3z" p-id="2629" fill="#ffffff"></path></svg>
                            支付宝支付
                        </a>
                        <a href="#" id="js-alipay-fail" class="button rswc-button rs-flex alt2 rs-ml-4">
                            <?= __('查询支付结果', 'wprs-wc-alipay'); ?>
                        </a>
                    </div>

                <?php endif; ?>

            </div>
        </div>

        <?php
    }


    /**
     * 监听支付网关同步返回信息
     * 处理支付接口异步返回的信息
     */
    public function listen_return_notify()
    {

        if (isset($_REQUEST[ 'out_trade_no' ]) && ! empty($_REQUEST[ 'out_trade_no' ])) {

            $out_trade_no = $_REQUEST[ 'out_trade_no' ];

            if (is_numeric($out_trade_no)) {
                if ( ! empty($this->order_prefix)) {
                    $order_id = (int)str_replace($this->order_prefix, '', $out_trade_no);
                } else {
                    $order_id = (int)$out_trade_no;
                }
            } else {
                $order_id = (int)str_replace($this->order_prefix, '', $out_trade_no);
            }

            $order = wc_get_order($order_id);

            try {
                $gateway = $this->get_gateway();

                /**
                 * 获取支付宝返回的参数
                 */
                /** @var \Omnipay\Alipay\Requests\AopCompletePurchaseRequest $request */

                try {
                    $request = $gateway->completePurchase();
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                    wp_die($e->getMessage());
                }

                $request->setParams(stripslashes_deep(array_merge($_POST, $_GET)));

                try {

                    /** @var \Omnipay\Alipay\Responses\AopCompletePurchaseResponse $response */
                    $response = $request->send();

                    if ($response->isPaid()) {

                        // 添加订单备注
                        $this->complete_order($order, $_REQUEST[ 'trade_no' ]);

                        if ($_SERVER[ 'REQUEST_METHOD' ] === 'POST') {
                            echo 'success';
                        } else {
                            wp_redirect($this->get_return_url($order));
                        }

                    } else {

                        $error = $this->get_error_message($response);
                        $this->log($error);

                        wp_redirect(wc_get_checkout_url());

                    }

                } catch (\Exception $e) {

                    $this->log($e->getMessage());
                    wp_die($e->getMessage());

                }

            } catch (InvalidRequestException $e) {

                wp_die($e->getMessage());

            }

        }

    }


    /**
     * 主动查询支付宝订单支付状态
     *
     * https://docs.open.alipay.com/api_1/alipay.trade.query
     */
    public function query_alipay_order()
    {

        $order_id = isset($_POST[ 'order_id' ]) ? (int)$_POST[ 'order_id' ] : false;

        if ( ! $order_id) {
            return;
        }

        try {
            $gateway = $this->get_gateway();

            $order = wc_get_order($order_id);

            /** @var \Omnipay\Alipay\Requests\AopCompletePurchaseRequest $request */
            $request = $gateway->query();

            $request->setBizContent([
                'out_trade_no' => $this->get_order_number($order_id),
            ]);

            try {
                /** @var \Omnipay\Alipay\Responses\AopCompletePurchaseResponse $response */
                $response = $request->send();

                if ($response->isPaid()) {

                    $response_data = $response->getData()[ 'alipay_trade_query_response' ];
                    $this->complete_order($order, $response_data[ 'trade_no' ]);

                    wp_send_json_success($order->get_checkout_order_received_url());
                } else {
                    wp_send_json_error($order->get_checkout_payment_url());
                }
            } catch (\Exception $e) {
                wp_send_json_error($e->getMessage());

                $this->log($e->getMessage());
            }

        } catch (InvalidRequestException $e) {
            wp_send_json_error($e->getMessage());
        }

    }


    /**
     * 处理退款
     *
     * If the gateway declares 'refunds' support, this will allow it to refund.
     * a passed in amount.
     *
     * @param int    $order_id Order ID.
     * @param null   $amount   Refund amount.
     * @param string $reason   Refund reason.
     *
     * @return boolean True or false based on success, or a WP_Error object.
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function process_refund($order_id, $amount = null, $reason = ''): bool
    {
        $gateway = $this->get_gateway();
        $order   = wc_get_order($order_id);
        $total   = $order->get_total();

        $exchange_rate = (float)$this->get_option('exchange_rate');
        if ($exchange_rate <= 0) {
            $exchange_rate = 1;
        }

        $total         = round($total * $exchange_rate, get_option('woocommerce_price_num_decimals'));
        $refund_amount = round($amount * $exchange_rate, get_option('woocommerce_price_num_decimals'));
        $refund_amount = number_format($refund_amount,get_option('woocommerce_price_num_decimals'),'.','');

        if ($refund_amount <= 0 || $refund_amount > $total) {
            return false;
        }

        /** @var \Omnipay\Alipay\Requests\AopTradeRefundRequest $request */
        $request = $gateway->refund();

        $request->setBizContent([
            'out_trade_no'   => $this->get_order_number($order_id),
            'trade_no'       => $order->get_transaction_id(),
            'refund_amount'  => $refund_amount,
            'out_request_no' => date('YmdHis') . wp_rand(1000, 9999),
        ]);

        try {
            /** @var \Omnipay\Alipay\Responses\AopTradeRefundResponse $response */
            $response = $request->send();

            if ($response->isSuccessful()) {
                $order->add_order_note(
                    sprintf(__('Refunded %1$s', 'wprs-wc-alipay'), $amount)
                );

                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }


    /**
     * 完成支付、支付网关验证成功后调用此方法
     *
     * @param $order    \WC_Order
     * @param $trade_no string
     */
    public function complete_order(\WC_Order $order, string $trade_no)
    {
        // 添加订单备注
        if ($order->get_status() === 'pending') {
            $order->add_order_note(sprintf(__('Alipay payment complete (Alipay ID: %s)', 'wprs-wc-alipay'), $trade_no));

            $order->payment_complete($trade_no);
        }

        $order->delete_meta_data('_gateway_payment_url');
        $order->save();
    }


    /**
     * @param $response \Omnipay\Alipay\Responses\AopTradePagePayResponse|\Omnipay\Alipay\Responses\AopTradePreCreateResponse | \Omnipay\Alipay\Responses\AopCompletePurchaseResponse
     *
     * @return string
     */
    public function get_error_message($response): string
    {
        $message = $response->getMessage() . '：' . $response->getSubMessage();

        if ($this->is_debug_mod) {
            $message .= '. Code：' . $response->getCode() . '，SubCode：' . $response->getSubCode();
        }

        return $message;
    }


    /**
     * Logger 辅助功能
     *
     * @param $message
     */
    public function log($message)
    {
        if ($this->is_debug_mod) {
            if ( ! ($this->log)) {
                $this->log = new \WC_Logger();
            }
            $this->log->add('woocommerce_wprs-wc-alipay', var_export($message, true));
        }
    }

}
