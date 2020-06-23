<?php
/** 
 * Fill login form automaticaly
 *
 * Install to Adminer on http://www.adminer.org/plugins/
 * @author Pavel Kutáč, http://www.kutac.cz/
 * 
 */

if (!defined('WIKINDX_ADMINER_CALLER')) {
    header("HTTP/1.1 403 Forbidden");
    die("Access forbidden.");
}

class FillLoginForm {  
  private $system;  
  private $server;
  private $name;
  private $pass;
  private $database;
    
  /**
   * Initialize plugin for filling login form
   * @param $system - Set driver
   *   server - MySQL
   *   sqlite - SQLite3
   *   sqlite2 - SQLite2
   *   pgsql - PostgreeSQL
   *   oracle - Oracle
   *   mssql - MS SQL
   *   firebird - Firebird (alpha)
   *   simpledb - SimpleDB
   *   mongo - MongoDB
   *   elastic - Elasticsearch
   *   
   * @param $server - Server to log in, default: localhost
   * @param $name - User name
   * @param $pass - Password to database
   * @param $database - Name of database
   */    
  public function __construct($system = "server", $server = false, $name = false, $pass = false, $database = false){
    $this->system = $system;
    $this->server = $server;
    $this->name = $name;
    $this->pass = $pass;
    $this->database = $database;
  }
    
  public function loginForm()
  {  
      ?>
      <script<?php echo nonce(); ?>>
      document.addEventListener("DOMContentLoaded", function(event) {
        var dr = qs("option[value='<?php echo $this->system; ?>']");
        if(dr){ dr.selected = true; }
        
        var s = qs("input[name='auth[server]']");
        if(s && s.value.trim() == ""){ s.value = "<?php echo $this->server ?>"; }
        
        var l = qs("input[name='auth[username]']");
        if(l && l.value.trim() == ""){ l.value = "<?php echo $this->name ?>"; }
        
        var p = qs("input[name='auth[password]']");
        if(p && p.value.trim() == ""){ p.value = "<?php echo $this->pass ?>"; }
        
        var d = qs("input[name='auth[db]']");
        if(d && d.value.trim() == ""){ d.value = "<?php echo $this->database ?>"; }
        d.readonly = true;
      });
      </script>
      <?php
      
    return null;
  }
}
