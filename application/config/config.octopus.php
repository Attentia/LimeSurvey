<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

return array(
	'preload'=>array('log'),
	
	'components' => array(
		'db' => array(
			'connectionString' => '#{WebsiteSurveyDBConnectionString}',
			'username' => '#{WebsiteSurveyDBUsername}',
			'password' => '#{WebsiteSurveyDBPassword}',
			'charset' => 'utf8',
			'tablePrefix' => '#{WebsiteSurveyDBTablesPrefix}',
			'initSQLs'=>array('SET DATEFORMAT ymd;','SET QUOTED_IDENTIFIER ON;'),
		),
		'log' => array(
				'class'=>'CLogRouter',
				'routes' => array(
					array(
						'class' => 'CFileLogRoute',
						'levels' => 'info, warning, error',
						'except' => 'exception.CHttpException.404',
						'logFile' => '#{WebsiteSurveyLogFileName}',
						'rotateByCopy' => true,
						'maxFileSize' => #{WebsiteSurveyLogFileMaxSizeInKB},
						'maxLogFiles' => #{WebsiteSurveyLogFilesMaxCount}
					),
				),
		),
		'urlManager' => array(
			'urlFormat' => 'get',
			'rules' => require('routes.php'),
			'showScriptName' => true,
		),

		'request' => array(
            'class'=>'LSHttpRequest',
            'noCsrfValidationRoutes'=>array(
                'remotecontrol',
                'plugins/direct'
			)
        )
	
	),
	
	// For security issue : it's better to set runtimePath out of web access
	// Directory must be readable and writable by the webuser
	'runtimePath'=> '#{WebsiteSurveyLogFilePath}',
	
	'config'=>array(
		'debug'=>'#{WebsiteSurveyDebug}',
		'debugsql'=>'#{WebsiteSurveyDebugSql}',
		'uploaddir'=>'#{WebsiteSurveyUploadDirectoryPhysicalPath}',
		'usertemplaterootdir'=>'#{WebsiteSurveyUploadDirectoryPhysicalPath}\templates',
		'publicurl' => '#{WebsiteSurveyURL}/',
		'RPCInterface' => 'json'

	)
);
/* End of file config.php */
/* Location: ./application/config/config.php */