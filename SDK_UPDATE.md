# 支付宝 V2 SDK 更新记录

## 2026-10-23：更新至 4.20.1435.ALL

### 更新前状态

- 插件内置支付宝官方 V2 SDK `alipay-sdk-PHP-4.20.249.ALL`。
- PHP 8 调用文件密钥签名路径时，SDK 的 `openssl_free_key()` 会产生弃用警告。

### 原因

- 内置 SDK 落后于支付宝官方 V2 SDK 当前版本。
- PHP 8 使用 `OpenSSLAsymmetricKey` 对象并自动释放；`openssl_free_key()` 只需要在 PHP 7.4 返回资源时调用。

### 实施内容

- 将 `AopClient` 和 `AopCertClient` 的 SDK 标识更新至 `alipay-sdk-PHP-4.20.1435.ALL`。
- 同步插件使用的 7 个官方请求类。
- 保留插件已有的 PHP 8.4 可选参数兼容处理。
- 仅在 OpenSSL 返回资源时显式释放密钥，兼容 PHP 7.4 和 PHP 8.x。
- 继续采用精简 SDK，只保留页面支付、手机网站支付、当面付、查询、退款查询、退款和关闭交易所需文件。

### 验证

- `composer test:sdk`：通过 29 项断言，覆盖版本、配置传递、7 个接口映射、`biz_content`、API 版本及 RSA2 签名验签。
- 全部项目 PHP 文件通过 `php -l`。
- `git diff --check` 无空白错误。
- 插件使用的 7 个请求类与支付宝官方最新版内容一致，忽略官方文件的 CRLF 换行差异。
