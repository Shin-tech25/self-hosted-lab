<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit629bd27f04f5077d4532c6b4283adfce
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit629bd27f04f5077d4532c6b4283adfce::$classMap;

        }, null, ClassLoader::class);
    }
}
