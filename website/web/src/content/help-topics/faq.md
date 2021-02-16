+++
title = "FAQ"
date = 2021-01-30T00:08:41+01:00
weight = 1
chapter = false
+++


## I don't have access to the internet. Can't I just run WIKINDX on my computer?

Yes. The server side of WIKINDX has been designed to run on free (in some cases, free for non-commercial use) and widely available software running on a variety of operating systems. PHP, MySQL and Apache are available for download (I know, you don't have access to the Internet...) from their respective sites.


## Does upgrading my WIKINDX to a later version lose any database data?

With one exception (see below), no. Untarring or unzipping a new release can safely be done over a previous version without fear of losing any data in your database. Any files in the install directory that are a part of the release package will be replaced; any files in the install directory that are not a part of the release package will be left unchanged. You may like to take a note of your config.php file settings – you should transfer settings to a new config.php file based on the supplied config.php.dist file as upgrades might add new settings that need to be part of config.php for proper operation of WIKINDX. From time to time, new WIKINDX releases may upgrade the underlying database structure (to account for new features) and this takes place the first time the new release is run and will not affect the stored data at all. Naturally, we strongly recommend, as with any digital data, that you initiate a database backup regime.
The one exception noted above is when you upgrade WIKINDX by placing the program files in another directory, pointing config.php to your existing database. If you have any attachments from the old directory that are not in the new attachments/ directory, any references to those attachments in the database will be removed – the upgrade checks for the existence of attachments and will remove what it thinks are redundant data. If you do not wish this to occur, copy attachments across to the new attachments/ directory BEFORE running the new WIKINDX and initiating the database upgrade process. As always, back up your existing database first so you can restore it if need be.


## My WIKINDX runs on the Internet. However, I'm worried anyone can come in and trash all my entries.

Write-protect WIKINDX from the administrator configuration interface - or use your web server's security protocols (for Apache your .htaccess file) to restrict access. While you are free to do this, take a leaf out of the wikipedia experience and trust to people's better natures. The free sharing of knowledge is at the heart of WIKINDX's philosophy.


## How do I get bibliographies out of WIKINDX and into my word processor?

Using a plug-in, WIKINDX allows you to save the bibliography as a RTF (Rich Text Format) file for you to use in a word processor as you like. If you are using a word processor that supports LaTeX, you may wish to export to BibTeX format instead. Don't forget, you can use the WYSIWYG word processor plug-in that allows you write an article from draft through to publication quality with importing of quotes etc. and automatic formatting of citations and appending of bibliographies.


## Which web browsers can I use?

The default templates that ship with each release, have been tested and found to run acceptably on the following web browsers:

* LINUX:
  * Mozilla
  * Konqueror


* WINDOWS:
  * Mozilla Firefox
  * Netscape
  * Internet Explorer
  * Opera


* APPLE OSX:
  * Mozilla
  * Safari
  * Chrome

## Do I need to allow cookies?

Yes. WIKINDX relies heavily on PHP sessions/cookies for its navigation and environment and, if you are a registered user, you can optionally turn on a further cookie (that stores only your username) in My Wikindx to save you manually logging on each time you use WIKINDX (if you logout rather than just closing the web browser, this optional cookie is removed from the computer you are using). Cookies can be turned on via your web browser preferences.


## How do I pronounce WIKINDX?

'WIKI INDEX'
