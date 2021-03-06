+++
title = "Hosting Setup"
date = 2021-01-30T00:08:41+01:00
weight = 2
+++


We can't cover all possible setups. We will restrict ourselves
to examples taken from our own development configurations
for a local install on a personal computer. 

We propose in the following configurations less secure parameters
or very high values because we describe here a personal installation
which will not be exposed to the Internet. We are more concerned
with practicality than security. For security it would be better
to have a restrictive approach where you only open features
or add resources if they are necessary according to your situation. 


## WinNMP on Windows

[WinNMP](https://winnmp.wtriple.com) is really easy to use and come
with a good documentation. Download the installer and install WinNMP at `C:\WinNMP`.
After that just follow the section [How to create a new Project](https://winnmp.wtriple.com/howtos#How-to-create-a-new-Project)
of its documentation. Once this step is completed, you could install WIKINDX
in the new virtual host, in `C:\WinNMP\WWW\wikindx` folder.

### Virtual host Setup

* Open __WinNMP Manager__ by clicking the taskbar or desktop icon,
  then click on __New Project__ icon, choose a project name like __wikindx__, hit __Enter__ or click __Save Project__.

![WinNMP Manager](../../images/winnmp_project_1.png)

* Accept the __User Account Control__ warning about `WinNMP.exe`.
  This is required to map the domain name `wikindx.test`
  to `localhost`, for testing SEF links and other Nginx rewrite rules.
* Check __Enable Local Virtual Server__.
* Don't change other options since your local installation
  is a production installation. You are not setting a local
  host to do some devloppement of a production website.
* Click __Save__ to close the __Edit Project__ window

![Edit Project](../../images/winnmp_project_2.png)

* The project database __wikindx__ and MariaDB user __wikindx__
  are already created automatically (Adminer, a web ui db admin
  is available at <http://localhost/tools/adminer.php>).
* The default MariaDb(MySql) host is __localhost__
  with username __root__ and no password.

~~~~
MySql server:       'localhost'
MySql user:         'root' or 'wikindx'
MySql password:     '' (no password)
MySql database:     'wikindx'
~~~~

* Type in your browser <http://wikindx.test>. You can see a welcome message
  with some instructions. Some tools are also available at <http://localhost>.

At this point you have a working virtual host ready for installing some PHP code.
We need a few more tweaks to accommodate WIKINDX.


### PHP configuration (php.ini)

* Edit the file `C:\WinNMP\conf\php.ini` (see [List of php.ini directives](https://www.php.net/manual/en/ini.list.php) on www.php.net).
* Comment __open_basedir__ directive.
* Comment __disable_functions__ directive.
* Comment __disable_classes__ directive.
* Change for `memory_limit = 128M` (max memory consumption by process).
* Change for `post_max_size = 128M` (max size of one HTTP request).
* Change for `upload_max_filesize = 128M` (Max size of one uploaded file).
* Change for `max_file_uploads = 20` (number of files uploaded at same time).
* Change for `date.timezone = Europe/Paris` (your timezone).
* Enable extensions:
  * `extension = php_bz2.dll`
  * `extension = php_curl.dll`
  * `extension = php_gd2.dll`
  * `extension = php_intl.dll`
  * `extension = php_mbstring.dll`
  * `extension = php_exif.dll`
  * `extension = php_sockets.dll`
  * `extension = php_openssl.dll`
  * `extension = php_fileinfo.dll`
  * `extension = php_ldap.dll`
  * `extension = php_gettext.dll`
  * `extension = php_mysqli.dll`

Sample of a `php.ini` file:

~~~~ini
; Generated by NMP - Configuration Editor
engine = On
zend.ze1_compatibility_mode = Off
short_open_tag = On
asp_tags = Off
precision = 14
y2k_compliance = On
;output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
unserialize_callback_func = 
serialize_precision = 100
allow_call_time_pass_reference = Off
safe_mode_allowed_env_vars = PHP_
safe_mode_protected_env_vars = LD_LIBRARY_PATH
;open_basedir = "c:/winnmp"
include_path = ".;c:/winnmp/include"
;disable_functions="allow_url_fopen, disk_free_space, diskfreespace, dl, eval, exec, fp, fput, ftp_connect, ftp_exec, ftp_get, ftp_login, ftp_nb_fput, ftp_put, ftp_raw, ftp_rawlist, highlight_file, ini_alter, ini_get_all, ini_restore, inject_code, msg_receive, msg_send, openlog, passthru, pcntl_alarm, pcntl_exec, pcntl_fork, pcntl_get_last_error, pcntl_getpriority, pcntl_setpriority, pcntl_signal, pcntl_signal_dispatch, pcntl_sigprocmask, pcntl_sigtimedwait, pcntl_sigwaitinfo, pcntl_strerror, pcntl_wait, pcntl_waitpid, pcntl_wexitstatus, pcntl_wifexited, pcntl_wifsignaled, pcntl_wifstopped, pcntl_wstopsig, pcntl_wtermsig, popen, posix_ctermid, posix_getgrgid, posix_getgrnam, posix_getlogin, posix_getpwuid, posix_kill, posix_mkfifo, posix_setegid, posix_seteuid, posix_setgid, posix_setpgid, posix_setsid, posix_setuid, posix_uname, proc_close, proc_get_status, proc_nice, proc_open, proc_terminate, putenv, sem_acquire, sem_get, shell_exec, shm_attach, shm_get_var, shm_put_var, shm_remove, shmop_close, shmop_delete, shmop_open, shmop_write, show_source, symlink, syslog, system, xmlrpc_entity_decode"
; disable_classes = Reflection, ReflectionClass, ReflectionExtension, ReflectionFunction, ReflectionFunctionAbstract, ReflectionMethod, ReflectionObject, ReflectionParameter, ReflectionProperty, Reflector
expose_php = Off
max_execution_time = 120
max_input_time = 15
memory_limit = 128M
error_reporting = E_ALL
display_errors = On
display_startup_errors = Off
log_errors = On
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
track_errors = Off
html_errors = On
error_log = "c:/winnmp/log/php_error.log"
mail.log = "c:/winnmp/log/php_mail.log"
curl.cainfo="c:/winnmp/src/cacert.pem"
openssl.cafile="c:/winnmp/src/cacert.pem"
sendmail_from = "WinNMP@localhost"
;sendmail_path = '"c:/winnmp/bin/msmtp/msmtp.exe" -C "c:/winnmp/conf/msmtp.ini" -t'
sendmail_path = '"c:/winnmp/bin/php" -n -f "c:/winnmp/include/tools/mailtodisk.php" --'
variables_order = "EGPCS"
register_globals = Off
register_long_arrays = Off
register_argc_argv = Off
auto_globals_jit = On
post_max_size = 128M
magic_quotes_gpc = Off
magic_quotes_runtime = Off
magic_quotes_sybase = Off
auto_prepend_file = 
auto_append_file = 
default_mimetype = "text/html"
doc_root = 
user_dir = 
cgi.fix_pathinfo = 0
file_uploads = On
upload_tmp_dir = "c:/winnmp/tmp"
upload_max_filesize = 128M
max_file_uploads = 20
allow_url_fopen = Off
allow_url_include = Off
default_socket_timeout = 180
extension_dir = "ext"
enable_dl = Off
date.timezone = Europe/Paris
define_syslog_variables = Off
sql.safe_mode = Off

mysql.allow_persistent = On
mysql.max_persistent = -1
mysql.max_links = -1
mysql.default_port = 3306
mysql.default_host = "localhost"
mysql.default_user = "root"
mysql.default_password = 
mysql.connect_timeout = 60
mysql.trace_mode = Off

mysqli.max_links = -1
mysqli.default_port = 3306
mysqli.default_host = "localhost"
mysqli.default_user = "root"
mysqli.default_pw = 
mysqli.reconnect = Off

bcmath.scale = 0

session.save_handler = files
session.save_path = "c:/winnmp/tmp"
session.use_cookies = 1
session.name = PHPSESSID
session.auto_start = 0
session.cookie_lifetime = 0
session.cookie_path = /
session.cookie_domain = 
session.cookie_httponly = 
session.serialize_handler = php
session.gc_probability = 15
session.gc_divisor = 100
session.gc_maxlifetime = 7200
session.bug_compat_42 = 0
session.bug_compat_warn = 1
session.referer_check = off
session.entropy_length = 0
session.entropy_file = 
session.cache_limiter = nocache
session.cache_expire = 180
session.use_trans_sid = 0
session.hash_function = 0
session.hash_bits_per_character = 5

url_rewriter.tags = "a=href,area=href,frame=src,input=src,form=fakeentry"
tidy.clean_output = Off

soap.wsdl_cache_enabled = 1
soap.wsdl_cache_dir = "c:/winnmp/tmp"
soap.wsdl_cache_ttl = 86400

mbstring.internal_encoding = UTF-8
mbstring.detect_order = UTF-8,ISO-8859-15,ISO-8859-1,ASCII

;Extensions
extension = php_bz2.dll
extension = php_curl.dll
extension = php_gd2.dll
;extension = php_imap.dll
extension = php_intl.dll
extension = php_mbstring.dll
extension = php_exif.dll
;extension = php_soap.dll
extension = php_sockets.dll
extension = php_openssl.dll
extension = php_fileinfo.dll

extension = php_ldap.dll
;extension = php_ffi.dll

;Additional extensions from c:/winnmp/bin/PHP/php-x.x.x/ext
;extension = php_ftp.dll
extension = php_gettext.dll
;extension = php_tidy.dll
;extension = php_xmlrpc.dll
;extension = php_xsl.dll

; Redis Cache / NoSql
; https://github.com/phpredis/phpredis#readme
;extension = php_redis.dll
; Uncomment to use redis as session storage:
;session.save_handler = redis
;session.save_path = "tcp://localhost:6379?weight=1"

;Database Extensions
extension = php_mysqli.dll
;extension = php_pdo_mysql.dll
;extension = php_mongodb.dll
;extension = php_odbc.dll
;extension = php_pdo_odbc.dll
;extension = php_pgsql.dll
;extension = php_pdo_pgsql.dll
;extension = php_sqlite3.dll
;extension = php_pdo_sqlite.dll
~~~~

### Nginx configuration

If your setup is fine your should find a file `C:\WinNMP\conf\domains.d\wikindx.conf`
which is the Nginx config of the Virtual Host dedicated to <http://wikindx.test>.
Example:

~~~~nginx
server {
	
	## How to allow access from LAN and Internet to your local project:
	## https://winnmp.wtriple.com/howtos#How-to-allow-access-from-LAN-and-Internet-to-your-local-project
	
	listen		127.0.0.1:80;
	
	## Enable self signed SSL certificate:
	## https://winnmp.wtriple.com/howtos#Enable-self-signed-SSL-certificate-for-your-local-project
	# listen		127.0.0.1:443 ssl http2;
	# ssl_certificate_key "c:/winnmp/conf/opensslCA/selfsigned/wikindx.test.key";
	# ssl_certificate "c:/winnmp/conf/opensslCA/selfsigned/wikindx.test.crt";
	
	## How to add additional local test server names to my project:
	## https://winnmp.wtriple.com/howtos#How-to-add-additional-local-test-server-names-to-my-project
	
	server_name 	wikindx.test;
	
	## To manually change the root directive replace the ending comment with: # locked
	## https://winnmp.wtriple.com/howtos#How-to-change-the-root-directory-of-a-project
	
	root 	"c:/winnmp/www/wikindx"; # automatically modified on each restart! can be manually set by replacing this comment
	
	## Access Restrictions
	allow		127.0.0.1;
	deny		all;
	
	## Add locations:
	## https://winnmp.wtriple.com/howtos#How-to-add-locations
	
	## Configure for various PHP Frameworks:
	## http://winnmp.wtriple.com/nginx.php
	
	autoindex on;
 
	location ~ \.php$ {
		try_files $uri =404;
		include		nginx.fastcgi.conf;
		include		nginx.redis.conf;
		fastcgi_pass	php_farm;
		fastcgi_hide_header X-Powered-By;
	}
 
	location / {
		try_files $uri $uri/ =404;
	}
}
~~~~

Instead we will focus on the `C:\WinNMP\conf\nginx.conf` file.
This file is the global config of Nginx and `C:\WinNMP\conf\domains.d\wikindx.conf`
inherits of it. This will improve Adminer configuration at the same time,
allow the upload of big files (128MB), and long script execution
for database backups from Adminer. Add or set the following options
as in the sample file below:

~~~~nginx
## Timeouts ##
##############

client_body_timeout     300;
client_header_timeout	300;
send_timeout		    600s;

# Amount of time for upstream to wait for a fastcgi process to send data. 
# Change this directive if you have long running fastcgi processes that do not produce output until they have finished processing. 
# If you are seeing an upstream timed out error in the error log, then increase this parameter to something more appropriate. 
fastcgi_read_timeout	600s;

# Request timeout to the server. The timeout is calculated between two write operations, not for the whole request. 
# If no data have been written during this period then serve closes the connection.
fastcgi_send_timeout	600s;

# php max upload limit cannot be larger than this       
client_max_body_size		128m;
~~~~


~~~~nginx
## For a live site, handling more connections, uncomment, then start WinNMP.exe --phpCgiServers=25:
#worker_processes auto;
#worker_rlimit_nofile 100000;

events {
	## For a live site, uncomment:
	#worker_connections 8096;
}

http {
    server_tokens   off;
	sendfile		on;
	tcp_nopush		on;
	tcp_nodelay		on;
	ssi			    off;
	server_names_hash_bucket_size  64;


	## Timeouts ##
	##############

    client_body_timeout     300;
    client_header_timeout	300;
    keepalive_timeout	    25 25;
    send_timeout		    600s;
	resolver_timeout	    3s;

	# Timeout period for connection with FastCGI-server. It should be noted that this value can't exceed 75 seconds. 
	fastcgi_connect_timeout 5s;

	# Amount of time for upstream to wait for a fastcgi process to send data. 
	# Change this directive if you have long running fastcgi processes that do not produce output until they have finished processing. 
	# If you are seeing an upstream timed out error in the error log, then increase this parameter to something more appropriate. 
	fastcgi_read_timeout	600s;

	# Request timeout to the server. The timeout is calculated between two write operations, not for the whole request. 
	# If no data have been written during this period then serve closes the connection.
	fastcgi_send_timeout	600s;


	## Buffers ##
	#############

	fastcgi_buffers			8 32k;
	fastcgi_buffer_size		32k;
	#fastcgi_busy_buffers_size	256k;
	#fastcgi_temp_file_write_size	256k;

	open_file_cache			off;

	# php max upload limit cannot be larger than this       
	client_max_body_size		128m;	

   ####client_body_buffer_size	1K;
	client_header_buffer_size 5120k;
	large_client_header_buffers 16 5120k;
	types_hash_max_size		2048;

	include nginx.mimetypes.conf;
	default_type text/html;

	## Logging ##
	#############

	access_log	"c:/winnmp/log/nginx_access.log";
	error_log	"c:/winnmp/log/nginx_error.log" warn;	#debug or warn
	log_not_found	on;	#enables or disables messages in error_log about files not found on disk. 
	rewrite_log	off;

	fastcgi_intercept_errors off;	# Do Not Change (off) !

	gzip  off;

	index  index.php index.htm index.html;

	server {
		# NEVER ALLOW PUBLIC ACCESS TO THIS SERVER !!!
		# Instead, create projects using WinNMP Manager, and allow public access only to those projects!
		# How to allow access from LAN and Internet to your local project:
		# http://WinNMP.wtriple.com/howtos.php#How-to-allow-access-from-LAN-and-Internet-to-your-local-project

		listen		127.0.0.1:80	default_server;		# Do Not Change ! Security Risk !
		#listen		[::1]:80	ipv6only=on;		# Do Not Change ! Security Risk !
		server_name	localhost;				# Do Not Change ! Security Risk !

		# This directive is modified automatically by WinNMP.exe for portability.
		root		"c:/winnmp/www";
		autoindex on;
		
		allow		127.0.0.1;	# Do Not Change ! Security Risk !
		allow		::1;		# Do Not Change ! Security Risk !
		deny		all;		# Do Not Change ! Security Risk !

		## deny access to .htaccess files, if Apache's document root concurs with nginx's one
		location ~ /\.ht {
			deny  all;
		}

		location = /favicon.ico {
				log_not_found off; 
		}
		location = /robots.txt {
				log_not_found off; 
		}

		## Tools are now served from include/tools/
		location ~ ^/tools/.*\.php$ {					
			root "c:/winnmp/include";
			try_files $uri =404; 
			include		nginx.fastcgi.conf;
			fastcgi_pass	php_farm;
			allow		127.0.0.1;		# Do Not Change ! Security Risk !
			allow		::1;			# Do Not Change ! Security Risk !
			deny		all;			# Do Not Change ! Security Risk !
		}
		location ~ ^/tools/ {
			root "c:/winnmp/include";
			allow		127.0.0.1;		# Do Not Change ! Security Risk !
			allow		::1;			# Do Not Change ! Security Risk !
			deny		all;			# Do Not Change ! Security Risk !
		}

		## How to add phpMyAdmin 
		## Copy phpMyAdmin files to c:/winnmp/include/phpMyAdmin then uncomment:

		#location ~ ^/phpMyAdmin/.*\.php$ {
		#	root "c:/winnmp/include";
		#	try_files $uri =404; 
		#	include         nginx.fastcgi.conf;
		#	fastcgi_pass    php_farm;
		#	allow           127.0.0.1;  
		#	allow           ::1;
		#	deny            all;
		#}       
		#location ~ ^/phpMyAdmin/ {
		#	root "c:/winnmp/include";
		#}

		## Notice that the root directive lacks /phpMyAdmin because Nginx adds the URL path /phpMyAdmin to the root path, so the resulting directory is c:/winnmp/include/phpMyAdmin
		

		## PHP for localhost ##
		#######################

		location ~ \.php$ {
			try_files $uri =404; 
			include		nginx.fastcgi.conf;
			include		nginx.redis.conf;
			fastcgi_pass	php_farm;
			allow		127.0.0.1;		# Do Not Change ! Security Risk !
			allow		::1;			# Do Not Change ! Security Risk !
			deny		all;			# Do Not Change ! Security Risk !
	        }

		# How to allow access from LAN and Internet to your local project:
		# http://WinNMP.wtriple.com/howtos.php#How-to-allow-access-from-LAN-and-Internet-to-your-local-project
	}

	include domains.d/*.conf;
	include nginx.phpfarm.conf;
}
~~~~


## Apache and mod_php on Debian

>>> [TODO]


## MacOS

We had an example for [XAMPP](http://www.apachefriends.org/en/xampp.html).
Unfortunately it is no longer valid due to the abandonment of the version without VM.

You can use the [Homebrew](https://brew.sh/) package manager
to get all the necessary software. This is probably the simplest solution.

Some resources:

* [Setup local MAMP server on Mac](https://documentation.mamp.info/en/MAMP-Mac/)
* [Setup Local (L)AMP Stack on Mac with Homebrew](https://medium.com/@JanFaessler/setup-local-lamp-stack-on-mac-with-homebrew-47eb8d6d53ea)
* [Upgrade to PHP 7.3 with Homebrew on Mac](https://stitcher.io/blog/php-73-upgrade-mac)
* [Upgrade to PHP 7.4 with Homebrew on Mac](https://stitcher.io/blog/php-74-upgrade-mac)
* [Upgrade to PHP 8.0 with Homebrew on Mac](https://stitcher.io/blog/php-8-upgrade-mac)

>>> We are looking for a contribution to complete this section.


## Creating a database

WinNMP creates a database on its own. For the other server environments
you need to create by hand the database with a MySQL client.
Hosting providers have often a dedicated tool.

The suggested database name, user name, and password below must match those in WIKINDX's
top-level `config.php` (`$WIKINDX_DB`, `$WIKINDX_DB_USER`, `$WIKINDX_DB_PASSWORD`).


### With phpMyAdmin

1. Launch PhpMyAdmin in a web browser, and log in.
   There might be a link to this in your web server control panel or,
   if running locally, try <http://localhost/phpmyadmin/>
   in the web browser address bar.

2. Open the _Databases_ tab. Type in __wikindx__
   as the name of a new database, set **utf8mb4_unicode_520_ci**
   as the collation, and click _Create_ button.

3. Go back to the _Databases_ tab, click on _Check privileges_
   for the new database, and select _Add user account_.

4. In _User name_ field type in __wikindx__
   and in _password_ field type __wikindx__.
   If running WIKINDX locally, select __local__ for host.
   Check the checkbox for _Grant all privileges on database wikindx_
   then click on the _Go_ button.


### With Adminer

1. Launch Adminer in a web browser, and log in.
   There might be a link to this in your web server control panel or,
   if running locally, try <http://localhost/adminer/> in the web browser address bar.

2. Click on *Create database* top link.
   Type in __wikindx__ as the name of a new database,
   set **utf8mb4_unicode_520_ci** as the collation,
   and click _Save_ button.

3. Click on _Privileges_ link at the top of the database screen.
   Click on _Create user_ link at the top.

4. In _Username_ field type __wikindx__
   and in _Password_ field type __wikindx__.
   Check __All privileges__ option and click on _Save_ button.


### With mysql CLI

1. Launch mysql tool from a console session on the hosting server.
   The command should be something like:

~~~~sh
shell> mysql
~~~~

or

~~~~sh
shell> mysql --user=admin_user_name --password admin_user_pwd
~~~~

2. In mysql prompt, execute this SQL query to create the __wikindx__ database:

~~~~sql
mysql> CREATE DATABASE wikindx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
~~~~

3. Add a __wikindx__ user with a __wikindx__ password,
   with privileges on __wikindx__ database
   when it accesses it
   from __localhost__ hostname server.

~~~~sql
mysql> GRANT ALL PRIVILEGES ON wikindx.* TO 'wikindx'@'localhost' IDENTIFIED BY 'wikindx';
~~~~


4. Refresh the privileges:

~~~~sql
mysql> FLUSH PRIVILEGES;
~~~~

4. Quit mysql prompt:

~~~~sql
mysql> exit
~~~~
