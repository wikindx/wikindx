<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Functions used for the upgrade process
 *
 * @package wikindx\core\update
 */
namespace SETUP\STEPS
{
    function step_unknown_error()
    {
        $output = "";
        
        $output .= "<h2>Unknown Error</h2>";
        $output .= __FUNCTION__;

        $output .= "
            <p>An unknown error has occurred. It appears that the current installation is already installed and up to date.</p>
            <p>If you have directly consulted this page you can return to the main page with this <a href='..'>link</a>.</p>
            <p>If you were automatically redirected to this page, there could be a detection error or an incorrect configuration.</p>
            <p>To avoid being blocked in a loop you will not be automatically redirected to the home page.</p>
        ";
        
        return $output;
    }

    function step_install_start()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_upgrade_start()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_db_min_version()
    {
        $output = "";
        
        $output .= __FUNCTION__;

        if (\SETUP\isDBEngineVersionMinCompatible($dbo)) {
            $output .= "<p>Your database engine is compatible with WIKINDX.</p>";
        } else {
            $EngineVersionRaw = $dbo->getStringEngineVersion();
            $EngineVersion = strtolower($EngineVersionRaw);

            if (strstr($EngineVersion, "mariadb")) {
                $EngineName = "MariaDB";
                $VersionMin = WIKINDX_MARIADB_VERSION_MIN; // Check MariaDB version
            } else {
                $EngineName = "MySQL";
                $VersionMin = WIKINDX_MYSQL_VERSION_MIN; // Check MySql or unknow engine version
            }
            
            $output .= "
                <p>Your database engine is not compatible with WIKINDX.
                WIKINDX requires {$EngineName} {$VersionMin}.
                Your version is {$EngineVersionRaw}.
                Please upgrade your db engine and continue.
                </p>
            ";
        }
        
        return $output;
    }
    
    function step_php_min_version()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_php_max_version()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_php_mandatory_extensions()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_install_config()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_upgrade_config()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_login_superadmin()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_create_superadmin()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_install_end()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
    
    function step_upgrade_end()
    {
        $output = "";
        
        $output .= __FUNCTION__;
        
        return $output;
    }
}

