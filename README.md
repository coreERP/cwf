Composer Autoload Settings
==========================

To use cwf in a project, complete the following steps

    1. Create linked folder in vendor for cwf/ ::
        $ ln -s ../../cwf/ cwf
    2. Modify the following files to include autoload
        - autoload_psr4.php => :: 'cwf\\' => array($vendorDir . '/cwf/src')
        - autoload_static.php (section: $prefixDirsPsr4) =>  :: 'cwf\\' => array (0 => __DIR__ . '/..' . '/cwf/src',),

