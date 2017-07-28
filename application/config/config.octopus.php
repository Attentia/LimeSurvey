<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

return array(
	'components' => array(
		'db' => array(
			'connectionString' => '#{WebsiteSurveyDBConnectionString}',
			'username' => '#{WebsiteSurveyDBUsername}',
			'password' => '#{WebsiteSurveyDBPassword}',
			'charset' => 'utf8',
			'tablePrefix' => '#{WebsiteSurveyDBTablesPrefix}',
			'initSQLs'=>array('SET DATEFORMAT ymd;','SET QUOTED_IDENTIFIER ON;'),
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