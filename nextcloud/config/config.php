<?php
$CONFIG = array (
  'htaccess.RewriteBase' => '/',
  'memcache.local' => '\\OC\\Memcache\\APCu',
  'memcache.locking' => '\\OC\\Memcache\\Redis',
  'redis' => 
  array (
    'host' => getenv('REDIS_HOST'),
    'port' => getenv('REDIS_PORT'),
    'password' => '',
  ),
  'apps_paths' => 
  array (
    0 => 
    array (
      'path' => '/var/www/html/apps',
      'url' => '/apps',
      'writable' => false,
    ),
    1 => 
    array (
      'path' => '/var/www/html/custom_apps',
      'url' => '/custom_apps',
      'writable' => true,
    ),
  ),
  'upgrade.disable-web' => true,
  'instanceid' => getenv('NEXTCLOUD_INSTANCE_ID'),
  'passwordsalt' => getenv('NEXTCLOUD_PASSWORD_SALT'),
  'secret' => getenv('NEXTCLOUD_SECRET'),
  'trusted_domains' => 
  array (
    0 => getenv('NEXTCLOUD_TRUSTED_DOMAINS'),
  ),
  'datadirectory' => '/var/www/html/data',
  'dbtype' => 'mysql',
  'version' => '31.0.1.2',
  'overwrite.cli.url' => getenv('NEXTCLOUD_OVERWRITE_CLI_URL'),
  'overwritehost' => getenv('NEXTCLOUD_TRUSTED_DOMAINS'),
  'overwriteprotocol' => 'https',
  'trusted_proxies' => 
  array (
    0 => '192.168.128.0/20',
  ),
  'dbname' => getenv('MYSQL_DATABASE'),
  'dbhost' => getenv('MYSQL_HOST'),
  'dbport' => '',
  'dbtableprefix' => 'oc_',
  'mysql.utf8mb4' => true,
  'dbuser' => getenv('MYSQL_USER'),
  'dbpassword' => getenv('MYSQL_PASSWORD'),
  'installed' => true,
  'enabledPreviewProviders' => 
  array (
    0 => 'OC\\Preview\\Movie',
    1 => 'OC\\Preview\\BMP',
    2 => 'OC\\Preview\\GIF',
    3 => 'OC\\Preview\\JPEG',
    4 => 'OC\\Preview\\Krita',
    5 => 'OC\\Preview\\MarkDown',
    6 => 'OC\\Preview\\MP3',
    7 => 'OC\\Preview\\OpenDocument',
    8 => 'OC\\Preview\\PNG',
    9 => 'OC\\Preview\\TXT',
    10 => 'OC\\Preview\\XBitmap',
    11 => 'OC\\Preview\\PDF',
    12 => 'OC\\Preview\\HEIC',
    13 => 'OC\\Preview\\MSOffice2003',
    14 => 'OC\\Preview\\MSOffice2007',
    15 => 'OC\\Preview\\MSOfficeDoc',
  ),
  'preview_max_memory' => 1024,
  'objectstore' => 
  array (
    'class' => '\\OC\\Files\\ObjectStore\\S3',
    'arguments' => 
    array (
      'region' => getenv('OBJECTSTORE_S3_REGION'),
      'hostname' => getenv('OBJECTSTORE_S3_HOST'),
      'bucket' => getenv('OBJECTSTORE_S3_BUCKET'),
      'key' => getenv('OBJECTSTORE_S3_KEY'),
      'secret' => getenv('OBJECTSTORE_S3_SECRET'),
      'port' => '',
      'storageClass' => '',
      'objectPrefix' => 'urn:oid:',
      'autocreate' => true,
      'use_ssl' => true,
      'use_path_style' => false,
      'legacy_auth' => false,
    ),
  ),
  'memcache.distributed' => '\\OC\\Memcache\\Redis',
  'app_install_overwrite' => 
  array (
  ),
  'defaultapp' => 'files',
  'maintenance' => false,
  'loglevel' => 2,
);
