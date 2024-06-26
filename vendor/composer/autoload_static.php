<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcd5ff7f07b3494a6e8ba358c44e7e171
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
        'N' => 
        array (
            'Nftdialpi\\NftProject\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'Nftdialpi\\NftProject\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitcd5ff7f07b3494a6e8ba358c44e7e171::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitcd5ff7f07b3494a6e8ba358c44e7e171::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitcd5ff7f07b3494a6e8ba358c44e7e171::$classMap;

        }, null, ClassLoader::class);
    }
}
