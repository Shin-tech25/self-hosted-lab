<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => 'e8a8194e688bd533e85fe39c79af7dd9c611be52',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'e8a8194e688bd533e85fe39c79af7dd9c611be52',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'friendsofphp/php-cs-fixer' => array(
            'dev_requirement' => true,
            'replaced' => array(
                0 => 'v3.64.0',
            ),
        ),
        'nextcloud/coding-standard' => array(
            'pretty_version' => 'v1.2.3',
            'version' => '1.2.3.0',
            'reference' => 'bc9c53a5306114b60c4363057aff9c2ed10a54da',
            'type' => 'library',
            'install_path' => __DIR__ . '/../nextcloud/coding-standard',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'php-cs-fixer/shim' => array(
            'pretty_version' => 'v3.64.0',
            'version' => '3.64.0.0',
            'reference' => '81ccfd24baf3a10810dab1152c403981a790b837',
            'type' => 'application',
            'install_path' => __DIR__ . '/../php-cs-fixer/shim',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
    ),
);
