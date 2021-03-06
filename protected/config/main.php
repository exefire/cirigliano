<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
Yii::setPathOfAlias('PhpOffice',Yii::getPathOfAlias('application.extensions.PhpOffice'));

return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Movistar Mantenciones',
	'timeZone' => 'America/Santiago',
	'language'=>'es', // Este es el lenguaje en el que querés que muestre las cosas
	'sourceLanguage'=>'en',
	'defaultController' => 'Punto/Index',
	// preloading 'log' component
	'preload'=>array(
		'log',
		'bootstrap'
	),
	'aliases' => array(
		'PhpOffice'=> realpath(__DIR__ . '/../extensions/PhpOffice'),
        'bootstrap' => realpath(__DIR__ . '/../extensions/bootstrap'), // change this if necessary
        'yiiwheels' => realpath(__DIR__ . '/../extensions/yiiwheels'),
        'xupload' => realpath(__DIR__ . '/../extensions/xupload')

    ),
	// autoloading model and component classes
	'import'=>array(
		'webroot.vendor.google.apiclient.src.google.auth.*',
		'webroot.vendor.google.apiclient.src.google.cache.*',
		'webroot.vendor.google.apiclient.src.google.http.*',
		'webroot.vendor.google.apiclient.src.google.io.*',
		'webroot.vendor.google.apiclient.src.google.service.*',
		'webroot.vendor.google.apiclient.src.google.signer.*',
		'webroot.vendor.google.apiclient.src.google.utils.*',
		'webroot.vendor.google.apiclient.src.google.verifier.*',
		'application.models.*',
		'application.vendors.*',
		'application.components.*',
		'application.modules.user.models.*',
        'application.modules.user.components.*',
        'application.extensions.phpass.*',
        'application.extensions.*',
        'bootstrap.helpers.TbHtml',
        'bootstrap.helpers.TbArray',
        'bootstrap.behaviors.TbWidget',
        'bootstrap.widgets.*',
        'application.helpers.*',

	),

	'modules'=>array(
        'historico',
        'reporte',
		'auth'=>array(
		  'strictMode' => true, // when enabled authorization items cannot be assigned children of the same type.
		  'userClass' => 'User', // the name of the user model class.
		  'userIdColumn' => 'id', // the name of the user id column.
		  'userNameColumn' => 'username', // the name of the user name column.
		  //'defaultLayout' => 'application.views.layouts.main', // the layout used by the module.
		  'defaultLayout' => 'webroot.protected.modules.auth.views.layouts.main',
		  'viewDir' => null, // the path to view files to use with this module.
		),
		'user'=>array(
            # encrypting method (php hash function)
            'hash' => 'md5',

            # send activation email
            'sendActivationMail' => false,

            # allow access for non-activated users
            'loginNotActiv' => false,

            # activate user on registration (only sendActivationMail = false)
            'activeAfterRegister' => true,

            # automatically login from registration
            'autoLogin' => true,

            # registration path
            'registrationUrl' => array('/user/registration'),

            # recovery password path
            'recoveryUrl' => array('/user/recovery'),

            # login form path
            'loginUrl' => array('/user/login'),

            # page after login
            'returnUrl' => array('/user/profile'),

            # page after logout
            'returnLogoutUrl' => array('/user/login'),
        ),
		'gii'=>array(
            'class'=>'system.gii.GiiModule',
			'password'=>'1234',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
			'generatorPaths'=>array(
			'bootstrap.gii',
			),
        ),

	),

	// application components
	'components'=>array(
		'image'=>array(
          'class'=>'application.extensions.image.CImageComponent',
            // GD or ImageMagick
            'driver'=>'GD',
            // ImageMagick setup path
            'params'=>array('directory'=>'/opt/local/bin'),
        ),
		'mandrillwrap' => array(
	         'class' => 'ext.mandrillwrap.mandrillwrap',
	         //'options' => array(/.. additional curl options ../)
	    ),
		'authManager' => array(
			'class' => 'auth.components.CachedDbAuthManager',
        	'cachingDuration' => 3600,
	      	'behaviors' => array(
	        	'auth' => array(
	          		'class' => 'auth.components.AuthBehavior',
	        	),
	      	),
	    ),
		'hasher'=>array (
	        'class'=>'Phpass',
	        'hashPortable'=>false,
	        'hashCostLog2'=>10,
	    ),
		'user'=>array(
			'class' => 'auth.components.AuthWebUser',
			'loginUrl' => array('/user/login'),
			'admins' => array('admin'), // users with full access
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName'=>false,
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		'bootstrap' => array(
            'class' => 'bootstrap.components.TbApi',
        ),
        'yiiwheels' => array(
            'class' => 'yiiwheels.YiiWheels',
        ),
		'bitly' => array(
	        'class' => 'application.extensions.bitly.VGBitly',
	        'login' => 'o_41ov1usisp', // login name
	        'apiKey' => 'R_d1d9a49e9a814f598426f686959e57b2', // apikey
	        'format' => 'json', // default format of the response this can be either xml, json (some callbacks support txt as well)
        ),
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=cirigliano',
			'emulatePrepare' => true,
			'username' => 'cirigliano',
			'password' => 'ciriglianodev',
			'charset' => 'utf8',
			'tablePrefix' => 'tbl_',
		),
		'db_wh'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=ciri_wh',
			'emulatePrepare' => true,
			'username' => 'cirigliano',
			'password' => 'ciriglianodev',
			'charset' => 'utf8',
			'tablePrefix' => 'tbl_',
			'class' => 'CDbConnection'
		),
		'db2'=>array(
        	'connectionString' => 'pgsql:host=localhost;port=5432;dbname=ciri_trade2',
			'username' => 'ciri_trade2',
			'password' => 'ciriTrade2Dev',
			'charset' => 'utf8',
			'class' => 'CDbConnection'
		),
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
            'class'=>'CProfileLogRoute',
            'levels'=>'profile',
            'enabled'=>true,
        ),
				// uncomment the following to show log messages on web pages
				// array(
				// 	'class'=>'CWebLogRoute',
				// ),
			),
		),
		'request'=>array(
            'enableCsrfValidation'=>false,
        ),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		'companyName' => 'Exefire',
		'adminEmail'=>'webmaster@exefire.com',
		'facturacionAbierta'=>false,
	),
);
