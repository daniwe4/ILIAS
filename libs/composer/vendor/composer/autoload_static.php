<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdf10d65404ae6ccf02b102f486e8e6ea
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Whoops\\' => 7,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'I' => 
        array (
            'ILIAS\\' => 6,
        ),
        'C' => 
        array (
            'CaT\\Plugins\\TalentAssessment\\' => 29,
            'CaT\\Plugins\\CareerGoal\\' => 23,
            'CaT\\' => 4,
        ),
        'B' => 
        array (
            'Box\\Spout\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Whoops\\' => 
        array (
            0 => __DIR__ . '/..' . '/filp/whoops/src/Whoops',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'ILIAS\\' => 
        array (
            0 => __DIR__ . '/../..' . '/../../src',
        ),
        'CaT\\Plugins\\TalentAssessment\\' => 
        array (
            0 => __DIR__ . '/../..' . '/../../Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes',
        ),
        'CaT\\Plugins\\CareerGoal\\' => 
        array (
            0 => __DIR__ . '/../..' . '/../../Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal/classes',
        ),
        'CaT\\' => 
        array (
            0 => __DIR__ . '/../..' . '/../../CaT/src',
        ),
        'Box\\Spout\\' => 
        array (
            0 => __DIR__ . '/..' . '/box/spout/src/Spout',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Pimple' => 
            array (
                0 => __DIR__ . '/..' . '/pimple/pimple/src',
            ),
        ),
        'E' => 
        array (
            'Eluceo\\iCal' => 
            array (
                0 => __DIR__ . '/..' . '/eluceo/ical/src',
            ),
        ),
    );

    public static $classMap = array (
        'EasyPeasyICS' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/EasyPeasyICS.php',
        'GeSHi' => __DIR__ . '/..' . '/geshi/geshi/src/geshi.php',
        'PHPMailer' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
        'PHPMailerOAuth' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauth.php',
        'PHPMailerOAuthGoogle' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauthgoogle.php',
        'POP3' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.pop3.php',
        'SMTP' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.smtp.php',
        'ntlm_sasl_client_class' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/ntlm_sasl_client.php',
        'phpmailerException' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdf10d65404ae6ccf02b102f486e8e6ea::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdf10d65404ae6ccf02b102f486e8e6ea::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitdf10d65404ae6ccf02b102f486e8e6ea::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitdf10d65404ae6ccf02b102f486e8e6ea::$classMap;

        }, null, ClassLoader::class);
    }
}
