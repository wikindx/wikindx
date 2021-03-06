<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

// Include code of libraries bundled in static classes
// UTF8 must be included before others namespace because they use it
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "UTF8.php"]));

// Include code of libraries bundled in namespaces
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "LOADSTYLE.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "HTML.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "FORM.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "AJAX.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "FILE.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "UTILS.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "LOCALES.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "URL.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "TEMPSTORAGE.php"]));


// Include code of libraries bundled in classes
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "FRONT.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "images.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "SESSION.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "COOKIE.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "MAIL.php"]));


/**
 * FACTORY_BROWSERTABID
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BROWSERTABID
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "startup", "BROWSERTABID.php"]));
            self::$instance = new BROWSERTABID();
        }

        return self::$instance;
    }
}

/**
 * FACTORY_HOUSEKEEPING
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_HOUSEKEEPING
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "startup", "HOUSEKEEPING.php"]));
            self::$instance = new HOUSEKEEPING();
        }

        return self::$instance;
    }
}
/**
 * FACTORY_PASSWORD
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_PASSWORD
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "password", "PASSWORD.php"]));
            self::$instance = new PASSWORD;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CONFIGDBSTRUCTURE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CONFIGDBSTRUCTURE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "startup", "CONFIGDBSTRUCTURE.php"]));
        self::$instance = new CONFIGDBSTRUCTURE;

        return self::$instance;
    }
}
/**
 * FACTORY_LOADCONFIG
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_LOADCONFIG
{
    /** object */
    private static $instance;

    /**
     * Get instance -- always a fresh instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "startup", "LOADCONFIG.php"]));
        self::$instance = new LOADCONFIG;

        return self::$instance;
    }
}
/**
 * FACTORY_SESSION
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_SESSION
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            self::$instance = new SESSION;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_COOKIE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_COOKIE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            self::$instance = new COOKIE();
        }

        return self::$instance;
    }
}
/**
 * FACTORY_AUTHORIZE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_AUTHORIZE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "startup", "AUTHORIZE.php"]));
            self::$instance = new AUTHORIZE;
        }

        return self::$instance;
    }
}

/**
 * FACTORY_MESSAGES
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_MESSAGES
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "messages", "MESSAGES.php"]));
            self::$instance = new MESSAGES();
        }

        return self::$instance;
    }
    /**
     * Get fresh instance
     *
     * Get instance regardless of whether instance already exists or not
     *
     * @return object (self::$instance)
     */
    public static function getFreshInstance()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "messages", "MESSAGES.php"]));
        self::$instance = new MESSAGES();

        return self::$instance;
    }
}
/**
 * FACTORY_ERRORS
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_ERRORS
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "messages", "ERRORS.php"]));
            self::$instance = new ERRORS;
        }

        return self::$instance;
    }
    /**
     * Get fresh instance
     *
     * Get instance regardless of whether instance already exists or not
     *
     * @return object (self::$instance)
     */
    public static function getFreshInstance()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "messages", "ERRORS.php"]));
        self::$instance = new ERRORS;

        return self::$instance;
    }
}
/**
 * FACTORY_SUCCESS
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_SUCCESS
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "messages", "SUCCESS.php"]));
            self::$instance = new SUCCESS;
        }

        return self::$instance;
    }
    /**
     * Get fresh instance
     *
     * Get instance regardless of whether instance already exists or not
     *
     * @return object (self::$instance)
     */
    public static function getFreshInstance()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "messages", "SUCCESS.php"]));
        self::$instance = new SUCCESS;

        return self::$instance;
    }
}
/**
 * FACTORY_CONSTANTS
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CONSTANTS
{
    /** object */
    private static $instance;

    /**
     * Get fresh instance
     *
     * @param bool $force_english Force the catalog to return english content only (useful for Bibtex and Endnote)
     *
     * @return object (self::$instance)
     */
    public static function getFreshInstance($force_english = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "messages", "CONSTANTS.php"]));
        self::$instance = new CONSTANTS($force_english);

        return self::$instance;
    }


    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "messages", "CONSTANTS.php"]));
            self::$instance = new CONSTANTS;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_DB
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_DB
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "SQL.php"]));
            self::$instance = new SQL;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_SQLSTATEMENTS
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_SQLSTATEMENTS
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "SQLSTATEMENTS.php"]));
            self::$instance = new SQLSTATEMENTS;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_TEMPLATE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_TEMPLATE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "TEMPLATE.php"]));
            self::$instance = new TEMPLATE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_USER
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_USER
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "usersgroups", "USER.php"]));
            self::$instance = new USER;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CLOSE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CLOSE
{
    /** object */
    public static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance) && empty(FACTORY_CLOSEPOPUP::$instance) && empty(FACTORY_CLOSENOMENU::$instance) && empty(FACTORY_CLOSERAW::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "CLOSE.php"]));
            self::$instance = new CLOSE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_FRONT
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_FRONT
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            self::$instance = new FRONT;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_QUARANTINE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_QUARANTINE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "admin", "QUARANTINE.php"]));
            self::$instance = new QUARANTINE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CLOSENOMENU
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CLOSENOMENU
{
    /** object */
    public static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance) && empty(FACTORY_CLOSEPOPUP::$instance) && empty(FACTORY_CLOSE::$instance) && empty(FACTORY_CLOSERAW::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "CLOSE.php"]));
            self::$instance = new CLOSENOMENU;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CLOSEPOPUP
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CLOSEPOPUP
{
    /** object */
    public static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance) && empty(FACTORY_CLOSENOMENU::$instance) && empty(FACTORY_CLOSE::$instance) && empty(FACTORY_CLOSERAW::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "CLOSE.php"]));
            self::$instance = new CLOSEPOPUP;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CLOSERAW
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CLOSERAW
{
    /** object */
    public static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance) && empty(FACTORY_CLOSEPOPUP::$instance) && empty(FACTORY_CLOSENOMENU::$instance) && empty(FACTORY_CLOSE::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "CLOSE.php"]));
            self::$instance = new CLOSERAW;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_STATISTICS
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_STATISTICS
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "miscellaneous", "STATISTICS.php"]));
            self::$instance = new STATISTICS;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_LOADICONS
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_LOADICONS
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "LOADICONS.php"]));
            self::$instance = new LOADICONS;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_EXPORTCOMMON
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_EXPORTCOMMON
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @param $outputType
     * @return object (self::$instance)
     */
    public static function getInstance($outputType = 'plain')
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "EXPORTCOMMON.php"]));
            self::$instance = new EXPORTCOMMON($outputType);
        }

        return self::$instance;
    }
}
/**
 * FACTORY_EXPORTCOINS
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_EXPORTCOINS
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "EXPORTCOINS.php"]));
            self::$instance = new EXPORTCOINS;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_EXPORTGOOGLESCHOLAR
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_EXPORTGOOGLESCHOLAR
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "EXPORTGOOGLESCHOLAR.php"]));
            self::$instance = new EXPORTGOOGLESCHOLAR;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BIBTEXPARSE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BIBTEXPARSE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "BIBTEXPARSE.php"]));
            self::$instance = new BIBTEXPARSE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_RICHTEXTFORMAT
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_RICHTEXTFORMAT
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @param string $imgMagickPath Default is FALSE in which case, RICHTEXTFORMAT() tries to pick it up from the word processor plugin config.php file
     *
     * @return object (self::$instance)
     */
    public static function getInstance($imgMagickPath = FALSE)
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "RICHTEXTFORMAT.php"]));
            self::$instance = new RICHTEXTFORMAT($imgMagickPath);
        }

        return self::$instance;
    }
}
/**
 * FACTORY_COINSMAP
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_COINSMAP
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "COINSMAP.php"]));
            self::$instance = new COINSMAP;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BIBTEXCONFIG
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BIBTEXCONFIG
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "BIBTEXCONFIG.php"]));
            self::$instance = new BIBTEXCONFIG;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BIBTEXMAP
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BIBTEXMAP
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "BIBTEXMAP.php"]));
            self::$instance = new BIBTEXMAP;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BIBTEXCREATORPARSE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BIBTEXCREATORPARSE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "BIBTEXCREATORPARSE.php"]));
            self::$instance = new BIBTEXCREATORPARSE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BIBTEXMONTHPARSE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BIBTEXMONTHPARSE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "BIBTEXMONTHPARSE.php"]));
            self::$instance = new BIBTEXMONTHPARSE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BIBTEXPAGEPARSE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BIBTEXPAGEPARSE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "BIBTEXPAGEPARSE.php"]));
            self::$instance = new BIBTEXPAGEPARSE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_EXPORTBIBTEX
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_EXPORTBIBTEX
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "EXPORTBIBTEX.php"]));
            self::$instance = new EXPORTBIBTEX;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_PARSEXML
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_PARSEXML
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "PARSEXML.php"]));
            self::$instance = new PARSEXML;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_STYLEMAP
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_STYLEMAP
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "STYLEMAP.php"]));
            self::$instance = new STYLEMAP;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BROWSECOMMON
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BROWSECOMMON
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "browse", "BROWSECOMMON.php"]));
            self::$instance = new BROWSECOMMON;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BIBSTYLE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BIBSTYLE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @param string $output Default is 'html'
     *
     * @return object (self::$instance)
     */
    public static function getInstance($output = 'html')
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "BIBSTYLE.php"]));
            self::$instance = new BIBSTYLE($output);
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BIBFORMAT
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BIBFORMAT
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @param string $output Default is 'html'
     *
     * @return object (self::$instance)
     */
    public static function getInstance($output = 'html')
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "BIBFORMAT.php"]));
            self::$instance = new BIBFORMAT($output);
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CITE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CITE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @param string $output Default is 'html'
     *
     * @return object (self::$instance)
     */
    public static function getInstance($output = 'html')
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "CITE.php"]));
            self::$instance = new CITE($output);
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CITESTYLE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CITESTYLE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @param string $output Default is 'html'
     *
     * @return object (self::$instance)
     */
    public static function getInstance($output = 'html')
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "CITESTYLE.php"]));
            self::$instance = new CITESTYLE($output);
        }

        return self::$instance;
    }
}
/**
 * FACTORY_EXPORTFILTER
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_EXPORTFILTER
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "EXPORTFILTER.php"]));
            self::$instance = new EXPORTFILTER;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CITEFORMAT
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CITEFORMAT
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @param string $output Default is 'html'
     *
     * @return object (self::$instance)
     */
    public static function getInstance($output = 'html')
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "CITEFORMAT.php"]));
            self::$instance = new CITEFORMAT($output);
        }

        return self::$instance;
    }
}
/**
 * FACTORY_LISTCOMMON
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_LISTCOMMON
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "lists", "LISTCOMMON.php"]));
            self::$instance = new LISTCOMMON;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_METADATA
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_METADATA
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "metadata", "METADATA.php"]));
            self::$instance = new METADATA;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_TYPE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_TYPE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "type", "TYPE.php"]));
            self::$instance = new TYPE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CATEGORY
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CATEGORY
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "category", "CATEGORY.php"]));
            self::$instance = new CATEGORY;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_TAG
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_TAG
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "miscellaneous", "TAG.php"]));
            self::$instance = new TAG;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_USERTAGS
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_USERTAGS
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "usersgroups", "USERTAGS.php"]));
            self::$instance = new USERTAGS;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_KEYWORD
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_KEYWORD
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "keyword", "KEYWORD.php"]));
            self::$instance = new KEYWORD;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_RESOURCEMAP
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_RESOURCEMAP
{
    /** object */
    private static $instance;

    /**
     * Get fresh instance
     *
     * @param mixed $exception
     *
     * @return object (self::$instance)
     */
    public static function getFreshInstance($exception = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "resources", "RESOURCEMAP.php"]));
        self::$instance = new RESOURCEMAP($exception);

        return self::$instance;
    }

    /**
     * Get instance
     *
     * @param mixed $exception
     *
     * @return object (self::$instance)
     */
    public static function getInstance($exception = FALSE)
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "resources", "RESOURCEMAP.php"]));
            self::$instance = new RESOURCEMAP($exception);
        }

        return self::$instance;
    }
}
/**
 * FACTORY_CREATOR
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_CREATOR
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "creator", "CREATOR.php"]));
            self::$instance = new CREATOR;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_PUBLISHER
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_PUBLISHER
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "publisher", "PUBLISHER.php"]));
            self::$instance = new PUBLISHER;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_PUBLISHERMAP
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_PUBLISHERMAP
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "publisher", "PUBLISHERMAP.php"]));
            self::$instance = new PUBLISHERMAP;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_COLLECTION
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_COLLECTION
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "collection", "COLLECTION.php"]));
            self::$instance = new COLLECTION;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_COLLECTIONMAP
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_COLLECTIONMAP
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "collection", "COLLECTIONMAP.php"]));
            self::$instance = new COLLECTIONMAP;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_MENU
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_MENU
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "navigation", "MENU.php"]));
            self::$instance = new MENU;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BADINPUT
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BADINPUT
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "navigation", "BADINPUT.php"]));
            self::$instance = new BADINPUT;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_NAVIGATE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_NAVIGATE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "navigation", "NAVIGATE.php"]));
            self::$instance = new NAVIGATE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_BIBLIOGRAPHYCOMMON
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_BIBLIOGRAPHYCOMMON
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibliographies", "BIBLIOGRAPHYCOMMON.php"]));
            self::$instance = new BIBLIOGRAPHYCOMMON;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_GATEKEEP
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_GATEKEEP
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "miscellaneous", "GATEKEEP.php"]));
            self::$instance = new GATEKEEP;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_RESOURCECOMMON
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_RESOURCECOMMON
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "resources", "RESOURCECOMMON.php"]));
            self::$instance = new RESOURCECOMMON;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_MAIL
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_MAIL
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "MAIL.php"]));
            self::$instance = new MAIL;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_PAGING
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_PAGING
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "lists", "PAGING.php"]));
            self::$instance = new PAGING;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_PAGINGALPHA
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_PAGINGALPHA
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "lists", "PAGINGALPHA.php"]));
            self::$instance = new PAGINGALPHA;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_PARSESTYLE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_PARSESTYLE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "PARSESTYLE.php"]));
            self::$instance = new PARSESTYLE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_EXPORTER
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_EXPORTER
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "importexport", "EXPORTER.php"]));
            self::$instance = new EXPORTER;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_LOADTINYMCE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_LOADTINYMCE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "tiny_mce", "LOADTINYMCE.php"]));
            self::$instance = new LOADTINYMCE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_LOADTINYMCE5
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_LOADTINYMCE5
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "tinymce", "LOADTINYMCE5.php"]));
            self::$instance = new LOADTINYMCE5;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_PARSEPHRASE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_PARSEPHRASE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "lists", "PARSEPHRASE.php"]));
            self::$instance = new PARSEPHRASE;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_ATTACHMENT
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_ATTACHMENT
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "miscellaneous", "ATTACHMENT.php"]));
            self::$instance = new ATTACHMENT;
        }

        return self::$instance;
    }
}
/**
 * FACTORY_DATE
 *
 *	Create objects for commonly used classes.
 *	Theoretically, this should save time in loading classes using include() statements and, perhaps, memory
 *	by not having multiple instances of the same object.
 *	Many WIKINDX classes have busy __construct() methods (initializing arrays etc.).  Using FACTORY ensures that
 *	this work is only done once each time the web server deals with a script -- subsequent class instantiations
 *	in the same server call return only the already constructed object.
 *
 *	e.g. To call the FACTORY SESSION object:
 *		$this->session = FACTORY_SESSION::getInstance();
 *
 * @package wikindx\core\startup
 */
class FACTORY_DATE
{
    /** object */
    private static $instance;

    /**
     * Get instance
     *
     * @return object (self::$instance)
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "miscellaneous", "DATE.php"]));
            self::$instance = new DATE;
        }

        return self::$instance;
    }
}
