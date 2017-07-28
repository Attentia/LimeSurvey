<?php
 /*
 * @author Denis Chenu <denis@sondages.pro>
 * @license GPL v3
 * @version 0.1
 *
 * Copyright (C) 2017 LimeSurvey Team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * Note: Copied from https://raw.githubusercontent.com/YunoHost-Apps/limesurvey_ynh/master/patches/app-0001-7ca4932.patch
 */
class UpdateDBCommand extends CConsoleCommand
{
    /**
     * Update database
     * @param array $args
     * @return void
     * @throws CException
     */
    public function run($args=null)
    {
        $this->_setConfigs();

        $newDbVersion = (float)Yii::app()->getConfig('dbversionnumber');
        $currentDbVersion = (float) Yii::app()->getConfig('DBVersion');

        if($newDbVersion > $currentDbVersion){
            echo "Updating " . Yii::app()->db->connectionString . "(using prefix :".Yii::app()->db->tablePrefix.") from version '{$currentDbVersion}' to '{$newDbVersion}'\n";
            Yii::import('application.helpers.common_helper', true);
            Yii::import('application.helpers.update.updatedb_helper', true);
            $result = db_upgrade_all($currentDbVersion);
            if ($result) {
                echo "Database has been successfully upgraded to version " . $newDbVersion . ".\n";
            } else {
                echo "Could not update database. Please check the console output.\n";
            }
        } else {
            echo "No need to update " . Yii::app()->db->connectionString . ", the database is already at version " . $newDbVersion . ".\n";
        }
    }

    private function _setConfigs(){
        /* default config */
        $aDefaultConfigs = require(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config-defaults.php');
        foreach($aDefaultConfigs as $sConfig => $defaultConfig){
            Yii::app()->setConfig($sConfig, $defaultConfig);
        }
        /* Fix for badly set rootdir */
        $sRootDir=realpath(Yii::app()->basePath. DIRECTORY_SEPARATOR . "..") ;
        Yii::app()->setConfig('rootdir', $sRootDir);
        Yii::app()->setConfig('publicdir', $sRootDir);
        Yii::app()->setConfig('homedir', $sRootDir);
        Yii::app()->setConfig('tempdir', $sRootDir.DIRECTORY_SEPARATOR."tmp");
        Yii::app()->setConfig('imagedir', $sRootDir.DIRECTORY_SEPARATOR."images");
        Yii::app()->setConfig('uploaddir', $sRootDir.DIRECTORY_SEPARATOR."upload");
        Yii::app()->setConfig('standardtemplaterootdir', $sRootDir.DIRECTORY_SEPARATOR."templates");
        Yii::app()->setConfig('usertemplaterootdir', $sRootDir.DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR."templates");
        Yii::app()->setConfig('styledir', $sRootDir.DIRECTORY_SEPARATOR."styledir");
        /* version */
        $aVersionConfigs = require(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'version.php');
        foreach ($aVersionConfigs as $sConfig => $versionConfig){
            Yii::app()->setConfig($sConfig, $versionConfig);
        }
        /* LS 3 version */
        Yii::app()->setConfig('runtimedir', $sRootDir.DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR."runtime");
        if (file_exists(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php'))
        {
            $config = require(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
            if (is_array($config['config']) && !empty($config['config']))
            {
                foreach($config['config'] as $key => $value)
                    Yii::app()->setConfig($key, $value);
            }
        }
        $oSettings = SettingGlobal::model()->findAll();
        if (count($oSettings) > 0)
        {
            foreach ($oSettings as $oSetting)
            {
                Yii::app()->setConfig($oSetting->getAttribute('stg_name'), $oSetting->getAttribute('stg_value'));
            }
        }
    }
}