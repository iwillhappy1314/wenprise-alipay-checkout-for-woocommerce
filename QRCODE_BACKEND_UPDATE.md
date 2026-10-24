# 支付宝当面付二维码后端生成说明

## 问题表现

- 订单支付页偶尔一直显示“正在生成二维码”，但没有出现可扫码的二维码。
- WooCommerce 二维码脚本未加载、被缓存工具延迟或加载顺序变化时，页面不会给出明确错误。
- 首次创建二维码与手动刷新二维码使用不同的支付宝响应处理逻辑，错误行为不一致。

## 根本原因

- 插件依赖 WooCommerce 内部的 `jquery.qrcode.js` 路径，并在浏览器中使用 Canvas 绘制二维码。
- 前端绘制函数不存在或抛出异常时，没有可靠的降级显示。
- 首次创建当面付订单时使用 `pageExecute()` 拼接地址后再次请求，没有复用刷新流程中较完整的响应校验。

## 修复方式

- 使用 `chillerlan/php-qrcode` 在 PHP 后端生成带标准留白的 SVG Data URI。
- 支付页直接输出二维码 `<img>`，刷新接口返回新的 `qrcode_image`。
- 移除 WooCommerce 内部二维码脚本依赖，前端只负责图片状态、倒计时、支付查询和刷新。
- 首次创建和手动刷新统一调用 `precreate_f2f_qrcode()`，并通过支付宝 SDK `execute()` 获取和校验响应。
- 捕获 `Throwable`，对二维码生成、接口响应及图片加载失败提供可见错误和重新生成入口。
- 在 `after_setup_theme` 阶段优先加载插件随包语言文件，避免 `wp-content/languages/plugins` 中的旧翻译覆盖新文案。
- 将倒计时、加载、失败和刷新提示由 PHP 翻译后传给 JavaScript，避免首屏英文随后被硬编码中文替换。

## 验证方式

```bash
composer validate --strict --no-check-publish
composer test:qrcode
composer test:sdk
composer test:localization
php -l src/Support/QrcodeGenerator.php
php -l src/PaymentGateway.php
php -l src/Init.php
node --check frontend/scripts.js
```

还应在 WooCommerce 测试订单中验证：首次显示、二维码到期、手动刷新、扫码支付和支付成功跳转。
