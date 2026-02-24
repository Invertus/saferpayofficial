-- Force PrestaShop to use HTTPS/SSL
-- Run against your PrestaShop database

-- Enable SSL
UPDATE ps_configuration SET value = '1' WHERE name = 'PS_SSL_ENABLED';

-- Enable SSL everywhere (force all pages to HTTPS)
UPDATE ps_configuration SET value = '1' WHERE name = 'PS_SSL_ENABLED_EVERYWHERE';

-- Copy domain to domain_ssl if domain_ssl is empty or different
UPDATE ps_shop_url SET domain_ssl = domain WHERE domain_ssl = '' OR domain_ssl IS NULL;
