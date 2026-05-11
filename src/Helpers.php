<?php

namespace Wenprise\Alipay;


/**
 * 插件通用辅助方法
 */
class Helpers {

	/**
	 * 判断当前请求是否来自微信内置浏览器
	 *
	 * @return bool
	 */
	public static function is_wechat() {
		if ( ! empty( $_SERVER[ 'HTTP_USER_AGENT' ] ) && strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'MicroMessenger' ) !== false ) {
			return true;
		}

		return false;
	}


	/**
	 * 获取用户的真实 IP
	 *
	 * @return string
	 */
	public static function get_ip(): string {
		$remote_ip = isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ 'REMOTE_ADDR' ] ) ) : '';
		$cf_ip     = isset( $_SERVER[ 'HTTP_CF_CONNECTING_IP' ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_CF_CONNECTING_IP' ] ) ) : '';

		if ( filter_var( $cf_ip, FILTER_VALIDATE_IP ) ) {
			return $cf_ip;
		}

		return filter_var( $remote_ip, FILTER_VALIDATE_IP ) ? $remote_ip : '';
	}

}
