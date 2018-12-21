<?php

if ( ! function_exists('wprs_is_wechat')) {
    /**
     * 判断是否在微信中打开
     */
    function wprs_is_wechat()
    {
        if ( ! empty($_SERVER[ 'HTTP_USER_AGENT' ]) && strpos($_SERVER[ 'HTTP_USER_AGENT' ], 'MicroMessenger') !== false) {
            return true;
        }

        return false;
    }
}



if ( ! function_exists('wprs_get_ip')) {
    /**
     * 获取用户的真实 IP
     *
     * @return mixed
     */
    function wprs_get_ip()
    {
        $client  = @$_SERVER[ 'HTTP_CLIENT_IP' ];
        $forward = @$_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
        $remote  = $_SERVER[ 'REMOTE_ADDR' ];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }
}

