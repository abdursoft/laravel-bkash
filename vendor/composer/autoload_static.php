<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0a188a92391d66985e41dd51d7a51f9a
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Abdur\\LaravelBkash\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Abdur\\LaravelBkash\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit0a188a92391d66985e41dd51d7a51f9a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0a188a92391d66985e41dd51d7a51f9a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0a188a92391d66985e41dd51d7a51f9a::$classMap;

        }, null, ClassLoader::class);
    }
}
