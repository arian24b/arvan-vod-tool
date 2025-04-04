<?php return array(
    'root' => array(
        'name' => 'khorshid/arvancloud-vod-for-wordpress',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => '507f62aa9b213731f3a28bf86f99f256ac05532d',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'khorshid/arvancloud-vod-for-wordpress' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '507f62aa9b213731f3a28bf86f99f256ac05532d',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'khorshid/wp-encrypt' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '67cc7ded5f0319845a917ff7dcaa36349512cac8',
            'type' => 'library',
            'install_path' => __DIR__ . '/../khorshid/wp-encrypt',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'woocommerce/action-scheduler' => array(
            'pretty_version' => '3.9.2',
            'version' => '3.9.2.0',
            'reference' => 'efbb7953f72a433086335b249292f280dd43ddfe',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../woocommerce/action-scheduler',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wpbp/debug' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '42dcbe2ab429df037f1248da568e970f9021934f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wpbp/debug',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => true,
        ),
    ),
);
