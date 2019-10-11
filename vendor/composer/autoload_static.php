<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcbb75d65d04d4ee67bcd9e905e591029
{
    public static $prefixLengthsPsr4 = array (
        'J' => 
        array (
            'JsonMachine\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'JsonMachine\\' => 
        array (
            0 => __DIR__ . '/..' . '/halaxa/json-machine/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitcbb75d65d04d4ee67bcd9e905e591029::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitcbb75d65d04d4ee67bcd9e905e591029::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}