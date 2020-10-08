<?php
namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\App;

class LazyLoader
{

    public static function createUrl($module, $dataField)
    {
        $url = App::getUrlRoot() . '?action=lazyModuleInfo&archiveName=' . $module->getArchiveName() . '&version=' . $module->getVersion() . '&data=' . $dataField;
        return $url;
    }

    public static function createScript($target, $url, $default = '')
    {
        return '
            <script>
                $.get("' . $url . '", function( data ) {
                    if (data) {
                        $("' . $target . '").html(data);
                        $("' . $target . '").show();
                    } else {
                        $("' . $target . '").html("' . $default . '");
                    }
                });
            </script>
        ';
    }

    public static function loadModuleInstallation($module, $target, $default = '')
    {
        $url = self::createUrl($module, 'installationMd');
        return self::createScript($target, $url, $default);
    }

    public static function loadModuleUsage($module, $target, $default = '')
    {
        $url = self::createUrl($module, 'usageMd');
        return self::createScript($target, $url, $default);
    }

    public static function loadModuleChangelog($module, $target, $default = '')
    {
        $url = self::createUrl($module, 'changelogMd');
        return self::createScript($target, $url, $default);
    }

    public static function loadModuleUpdateCount($target)
    {
        $url = App::getUrlRoot() . '?action=lazyModuleUpdateCount';
        return self::createScript($target, $url, '0');
    }

    public static function loadModuleChangeCount($target)
    {
        $url = App::getUrlRoot() . '?action=lazyModuleChangeCount';
        return self::createScript($target, $url, '0');
    }

    public static function loadSystemUpdateCount($target)
    {
        $url = App::getUrlRoot() . '?action=lazySystemUpdateCount';
        return self::createScript($target, $url, '0');
    }
}