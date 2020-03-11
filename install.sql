INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('ru', 'unitpay_domain', 'Домен');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('en', 'unitpay_domain', 'Domain');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('ru', 'unitpay_public_key', 'Публичный ключ');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('en', 'unitpay_public_key', 'Public key');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('ru', 'unitpay_secret_key', 'Секретный ключ');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('en', 'unitpay_secret_key', 'Secret key');
INSERT INTO `cscart_payment_processors` (`processor`, `processor_script`, `processor_template`, `admin_template`, `callback`, `type`) VALUES ('Unitpay', 'unitpay.php', 'views/orders/components/payments/cc_outside.tpl', 'unitpay.tpl', 'N', 'P');

