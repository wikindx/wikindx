<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
 * ImageServer
 *
 * The class that displays images (icons and thumbnails)
 *
 * @package wikindx\core\libs\images
 */
class ImageServer
{
    /**
     * Checks if an image is requested and displays one if needed
     *
     * @return bool
     */
    public static function showImage()
    {
        global $_IMAGES;
        if (isset($_GET['img']))
        {
            $mtime = gmdate('r', filemtime($_SERVER['SCRIPT_FILENAME']));
            $etag = md5($mtime . $_SERVER['SCRIPT_FILENAME']);

            if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $mtime)
                || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $etag))
            {
                header('HTTP/1.1 304 Not Modified');

                return TRUE;
            }
            else
            {
                header('ETag: "' . $etag . '"');
                header('Last-Modified: ' . $mtime);
                header('Content-type: image/gif');
                if (mb_strlen($_GET['img']) > 0 && isset($_IMAGES[$_GET['img']]))
                {
                    echo base64_decode($_IMAGES[$_GET['img']]);
                }
                else
                {
                    echo base64_decode($_IMAGES["unknown"]);
                }
            }

            return TRUE;
        }
        elseif (isset($_GET['thumb']))
        {
            if (mb_strlen($_GET['thumb']) > 0)
            {
                $thumb = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES, basename($_GET['thumb'])]);
                ImageServer::showThumbnail($thumb);
            }

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Creates and returns a thumbnail image object from an image file
     *
     * @param string $file Filename path
     *
     * @return resource
     */
    public static function createThumbnail($file)
    {
        $max_width = 200;
        $max_height = 200;

        $image = ImageServer::openImage($file);
        if ($image == NULL)
        {
            return;
        }

        imagealphablending($image, TRUE);
        imagesavealpha($image, TRUE);

        $width = imagesx($image);
        $height = imagesy($image);

        $new_width = $max_width;
        $new_height = $max_height;
        if (($width / $height) > ($new_width / $new_height))
        {
            $new_height = $new_width * ($height / $width);
        }
        else
        {
            $new_width = $new_height * ($width / $height);
        }

        if ($new_width >= $width && $new_height >= $height)
        {
            $new_width = $width;
            $new_height = $height;
        }

        $new_image = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($new_image, TRUE);
        imagesavealpha($new_image, TRUE);
        $trans_colour = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
        imagefill($new_image, 0, 0, $trans_colour);

        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        return $new_image;
    }
    /**
     * Function for displaying the thumbnail.
     * Includes attempts at caching it so that generation is minimised.
     *
     * @param string $file Filename path
     */
    public static function showThumbnail($file)
    {
        if (filemtime($file) < filemtime($_SERVER['SCRIPT_FILENAME']))
        {
            $mtime = gmdate('r', filemtime($_SERVER['SCRIPT_FILENAME']));
        }
        else
        {
            $mtime = gmdate('r', filemtime($file));
        }

        $etag = md5($mtime . $file);

        if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $mtime)
            || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $etag))
        {
            header('HTTP/1.1 304 Not Modified');

            return;
        }
        else
        {
            header('ETag: "' . $etag . '"');
            header('Last-Modified: ' . $mtime);
            header('Content-Type: image/png');
            $image = ImageServer::createThumbnail($file);
            imagepng($image);
        }
    }

    /**
     * A helping function for opening different types of image files
     *
     * @param string $file Filename path
     *
     * @return resource
     */
    public static function openImage($file)
    {
        $size = getimagesize($file);
        switch ($size["mime"]) {
            case "image/jpeg":
                $im = imagecreatefromjpeg($file);

            break;
            case "image/gif":
                $im = imagecreatefromgif($file);

            break;
            case "image/png":
                $im = imagecreatefrompng($file);

            break;
            default:
                $im = NULL;

            break;
        }

        return $im;
    }
}

/**
 * FileManager
 *
 * The class for any kind of file managing (new folder, upload, etc).
 */
class FileManager
{
    /**
     * Upload a file
     *
     * @param object $location Location
     * @param string $userfile Filename path
     *
     * @return bool
     */
    public function uploadFile($location, $userfile)
    {
        global $_ERROR;
        global $_ERROR2;
        $this->readDir();
        $existingHashes = [];
        foreach ($this->files as $fileName)
        {
            $split = \UTF8\mb_explode('_', $fileName);
            if (count($split) > 1)
            {
                $hashExt = array_pop($split);
                $readableName = implode('_', $split);
                $existingHashes[$readableName] = $hashExt;
            }
        }
        $name = basename($userfile['name']);
        //$name = stripslashes($name);

        $split = \UTF8\mb_explode('.', $name);
        array_pop($split);
        $name = implode('', $split);
        $upload_file = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES, $name]);

        $mime_type = FileServer::getFileMime($userfile['tmp_name']);
        $hash = sha1_file($userfile['tmp_name']);
        $extension = FileServer::getFileExtension($userfile['name']);
        $extensions = ['gif', 'jpeg', 'jpg', 'png', 'webp'];
        $upload_file .= '_' . $hash . '.' . $extension;
        $postMax = FILE\return_bytes(ini_get('post_max_size'));
        $uploadMax = FILE\return_bytes(ini_get('upload_max_filesize'));
        if ($postMax < $uploadMax)
        {
            $maxSize = $postMax;
        }
        else
        {
            $maxSize = $uploadMax;
        }
        if (WIKINDX_IMAGES_MAXSIZE)
        {
            if ($maxSize > (WIKINDX_IMAGES_MAXSIZE * 1024 * 1024))
            {
                $maxSize = WIKINDX_IMAGES_MAXSIZE * 1024 * 1024;
            }
        }
        if ($userfile['size'] > $maxSize)
        {
            $_ERROR = "imageSize";
            $_ERROR2 = WIKINDX_IMAGES_MAXSIZE;

            return;
        }
        if (array_search(mb_strtolower($extension), $extensions) === FALSE)
        {
            $_ERROR = "uploadType";

            return;
        }
        $mimetypes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'];
        if (($key = array_search($hash . '.' . $extension, $existingHashes)) !== FALSE)
        {
            $_ERROR = 'imageExists';
            $_ERROR2 = $key;

            return;
        }
        if (array_search(mb_strtolower($mime_type), $mimetypes) === FALSE)
        {
            $_ERROR = "uploadType";

            return;
        }
        if (!$location->isWritable())
        {
            $_ERROR = "write";

            return;
        }
        elseif (!is_uploaded_file($userfile['tmp_name']))
        {
            $_ERROR = "upload";

            return;
        }
        elseif (!@move_uploaded_file($userfile['tmp_name'], $upload_file))
        {
            $_ERROR = "upload";

            return;
        }
    }
    /**
     * The main function, checks if the user wants to perform any supported operations
     *
     * @param object $location Location
     */
    public function run($location)
    {
        if (isset($_FILES['userfile']['name']) && mb_strlen($_FILES['userfile']['name']) > 0)
        {
            $this->uploadFile($location, $_FILES['userfile']);
        }
    }
    /**
     * Read the file list from the directory
     */
    public function readDir()
    {
        // Reading the data of files and directories
        $open_dir = opendir(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES]));
        $this->dirs = [];
        $this->files = [];
        while ($object = readdir($open_dir))
        {
            if ($object != "." && $object != "..")
            {
                $ext = FileServer::getFileExtension($object);
                if (($ext == 'gif') || ($ext == 'jpeg') || ($ext == 'jpg') || ($ext == 'png') || ($ext == 'webp'))
                {
                    $this->files[] = $object;
                }
            }
        }
        closedir($open_dir);
    }
}

/**
 * FileServer
 *
 * File class holds the information about one file in the list
 */
class FileServer
{
    /** var */
    public $name;
    /** var */
    public $location;
    /** var */
    public $size;
    /** var */
    public $type;
    /** var */
    public $modTime;

    /**
     * Handler for files
     *
     * @param string $name Filename
     * @param object $location Location
     */
    public function __construct($name, $location)
    {
        $this->name = $name;
        $this->location = $location;

        $this->type = FileServer::getFileType(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES, $this->getName()]));
        $this->size = FileServer::getFileSize(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES, $this->getName()]));
        $this->modTime = filemtime(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES, $this->getName()]));
    }
    /**
     * Get file name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Get raw url encoded file name
     *
     * @return string
     */
    public function getNameEncoded()
    {
        return rawurlencode($this->name);
    }
    /**
     * Get file name encoded for special chars
     *
     * @return string
     */
    public function getNameHtml()
    {
        return htmlspecialchars($this->name);
    }

    /**
     * Get file size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get file mime type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * Get file modification time
     *
     * @return string
     */
    public function getModTime()
    {
        return $this->modTime;
    }

    /**
     * Get file size
     *
     * @param string $file
     *
     * @return int
     */
    public static function getFileSize($file)
    {
        return filesize($file);
    }
    /**
     * Get file mime type
     *
     * @param string $filepath
     *
     * @return string
     */
    public static function getFileType($filepath)
    {
        /*
         * This extracts the information from the file contents.
         * Unfortunately it doesn't properly detect the difference between text-based file types.
         *
        $mime_type = FileServer::getMimeType($filepath);
        $mime_type_chunks = \UTF8\mb_explode("/", $mime_type, 2);
        $type = $mime_type_chunks[1];
        */
        return FileServer::getFileExtension($filepath);
    }

    /**
     * Get file mime type
     *
     * @param string $filepath
     *
     * @return string
     */
    public static function getFileMime($filepath)
    {
        $fhandle = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($fhandle, $filepath);

        return $mime_type;
    }
    /**
     * Get file extension
     *
     * @param string $filepath
     *
     * @return string
     */
    public static function getFileExtension($filepath)
    {
        return mb_strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    }

    /**
     * Is the file an image?
     *
     * @return bool
     */
    public function isImage()
    {
        return in_array($this->getType(), ['gif', 'jpeg', 'jpg', 'png', 'webp']);
    }
    /**
     * Is the file an image and therefore valid for thumbnail treatment?
     *
     * @return bool
     */
    public function isValidForThumb()
    {
        return $this->isImage();
    }
}

/**
 * Location
 */
class Location
{
    /** var */
    public $path;
    
    /**
     * Is directory writable
     *
     * @return bool
     */
    public function isWritable()
    {
        return is_writable(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES]));
    }
}

/**
 * EncodeExplorer
 */
class EncodeExplorer
{
    /** object */
    public $location;
    /** array */
    public $dirs;
    /** array */
    public $files;
    /** string */
    public $sort_by;
    /** string */
    public $sort_as;
    /** object */
    public $messages;
    /** object */
    public $errors;
    /** object */
    public $session;

    /**
     * get filename for display
     *
     * @param string $file Filename
     *
     * @return string modified filename
     */
    public static function getFilename($file)
    {
        $filenameArray = \UTF8\mb_explode('_', $file->getNameHtml());
        if (count($filenameArray) > 1)
        {
            array_pop($filenameArray);

            return implode('_', $filenameArray) . '.' . $file->getFileExtension($file->getName());
        }
        else
        {
            return $file->getNameHtml();
        }
    }
    /**
     * Determine sorting
     */
    public function init()
    {
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $default_sort_by = "name";
        $default_sort_as = "desc";


        if (isset($_GET["sort_by"]))
        {
            $this->sort_by = $_GET["sort_by"];
        }
        else
        {
            $this->sort_by = $default_sort_by;
        }

        if (isset($_GET["sort_as"]))
        {
            $this->sort_as = $_GET["sort_as"];
        }
        else
        {
            $this->sort_as = $default_sort_as;
        }


        if (in_array($this->sort_by, ["name", "size", "mod"]) === FALSE)
        {
            $this->sort_by = $default_sort_by;
        }

        if (in_array($this->sort_as, ["asc", "desc", "mod"]) === FALSE)
        {
            $this->sort_as = $default_sort_as;
        }
    }

    /**
     * Read the file list from the directory
     */
    public function readDir()
    {
        // Reading the data of files and directories
        $open_dir = opendir(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES]));
        $this->dirs = [];
        $this->files = [];
        while ($object = readdir($open_dir))
        {
            if ($object != "." && $object != "..")
            {
                if (in_array(FileServer::getFileExtension($object), ['gif', 'jpeg', 'jpg', 'png', 'webp']) === TRUE)
                {
                    $this->files[] = new FileServer($object, $this->location);
                }
            }
        }
        closedir($open_dir);
    }
    /**
     * Sort the file list
     */
    public function sort()
    {
        if (is_array($this->files))
        {
            usort($this->files, "EncodeExplorer::cmp_" . $this->sort_by);
            if ($this->sort_as == "desc")
            {
                $this->files = array_reverse($this->files);
            }
        }

        if (is_array($this->dirs))
        {
            usort($this->dirs, "EncodeExplorer::cmp_name");
            if ($this->sort_by == "name" && $this->sort_as == "desc")
            {
                $this->dirs = array_reverse($this->dirs);
            }
        }
    }
    /**
     * Make the arrow images
     *
     * @param string $sort_by Column to sort on
     *
     * @return string
     */
    public function makeArrow($sort_by)
    {
        if ($this->sort_by == $sort_by && $this->sort_as == "asc")
        {
            $sort_as = "desc";
        }
        else
        {
            $sort_as = "asc";
        }

        $img = "arrows.jpg";

        if ($sort_by == "name")
        {
            $text = $this->messages->text('tinymce', "fileName");
        }
        elseif ($sort_by == "size")
        {
            $text = $this->messages->text('tinymce', "size");
        }
        elseif ($sort_by == "mod")
        {
            $text = $this->messages->text('tinymce', "lastUpdated");
        }

        return "<a href=\"" . $this->makeLink($sort_by, $sort_as, implode("/", [WIKINDX_URL_BASE, WIKINDX_URL_DATA_IMAGES]) . "/") . "\">
			$text <img style=\"border:0;\" alt=\"" . $sort_as . "\" src=\"img/" . $img . "\"></a>";
    }
    /**
     * make an image link
     *
     * @param string $sort_by Which sort by arrow?
     * @param string $sort_as Sort by ascending or descending?
     * @param string $dir Directory with arrow images
     *
     * @return string
     */
    public function makeLink($sort_by, $sort_as, $dir)
    {
        $link = "?";

        $link .= "lang=en&amp;";

        if ($sort_by != NULL && mb_strlen($sort_by) > 0)
        {
            $link .= "sort_by=" . $sort_by . "&amp;";
        }

        if ($sort_as != NULL && mb_strlen($sort_as) > 0)
        {
            $link .= "sort_as=" . $sort_as . "&amp;";
        }

        $link .= "dir=" . $dir;

        return $link;
    }
    /**
     * Make the image icon link
     *
     * @param string $l extension
     *
     * @return string
     */
    public function makeIcon($l)
    {
        $l = mb_strtolower($l);

        if (($l == 'jpeg') || ($l == 'jpg'))
        {
            $img = 'img/jpeg.png';
        }
        elseif ($l == 'gif')
        {
            $img = 'img/gif.png';
        }
        elseif ($l == 'png')
        {
            $img = 'img/png.png';
        }
        elseif ($l == 'webp')
        {
            $img = 'img/webp.webp';
        }
        else
        {
            $img = $l;
        }

        return $img;
    }
    /**
     * Format the file modification time for display
     *
     * @param int $time Unix file time
     *
     * @return string
     */
    public function formatModTime($time)
    {
        $timeformat = "d.m.y H:i:s";

        return date($timeformat, $time);
    }
    /**
     * Comparison callback for sorting by name
     *
     * @param string $b
     * @param string $a
     *
     * @return string
     */
    public static function cmp_name($b, $a)
    {
        return \UTF8\mb_strcasecmp($a->name, $b->name);
    }
    /**
     * Comparison callback for sorting by size
     *
     * @param int $a
     * @param int $b
     *
     * @return int
     */
    public static function cmp_size($a, $b)
    {
        return ($a->size - $b->size);
    }
    /**
     * Comparison callback for sorting by Unix time
     *
     * @param int $b
     * @param int $a
     *
     * @return int
     */
    public static function cmp_mod($b, $a)
    {
        return ($a->modTime - $b->modTime);
    }

    /**
     * Main function, activating tasks
     *
     * @param object $location
     * @param bool $delete Default is FALSE (don't output html, just return $this->files object). If TRUE, output html with no return
     *
     * @return object Optional
     */
    public function run($location, $delete = FALSE)
    {
        global $_ERROR;
        global $_ERROR2;
        $this->location = $location;
        $this->readDir();
        $this->sort();
        if ($delete)
        {
            return $this->files;
        }
        $pString = '';
        if (isset($_ERROR) && $_ERROR)
        {
            if (isset($_ERROR2) && $_ERROR2)
            {
                $pString = $this->errors->text('file', $_ERROR, $_ERROR2);
            }
            else
            {
                $pString = $this->errors->text('file', $_ERROR);
            }
        }
        $pString .= $this->outputHtml();
        GLOBALS::addTplVar('content', $pString);
    }

    /**
     * Output the HTML
     *
     * @return string
     */
    public function outputHtml()
    {
        $pString = '<link rel="stylesheet" href="images.css?ver=' . WIKINDX_PUBLIC_VERSION . '" type="text/css">';
        $pString .= '<script src="' . WIKINDX_URL_BASE . "/" . WIKINDX_URL_COMPONENT_VENDOR . '/jquery/jquery.min.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        $pString .= LF . "<script>" . LF;
        $pString .= '
			var $table = $("table.table");
		    var $bodyCells = $table.find("tbody tr:first").children();
		    var colWidth;
	    ';
        
        // Adjust the width of table thead cells when window resizes
        $pString .= '$(window).resize(function() {
		    // Get the tbody columns width array
		    colWidth = $bodyCells.map(function() {
		        return $(this).width();
		    }).get();
		
		    // Set the width of thead columns
		    $table.find("thead tr").children().each(function(i, v) {
		        $(v).width(colWidth[i]);
		    });
		}).resize(); '; // Trigger resize handler
        
        $pString .= '$(document).ready(function()
			{
				function positionThumbnail(e)
				{
					xOffset = 30;
					yOffset = 10;
					$("#thumb").css("left",(e.clientX + xOffset) + "px");
		
					diff = 0;
					if (e.clientY + $("#thumb").height() > $(window).height())
						diff = e.clientY + $("#thumb").height() - $(window).height();
		
					$("#thumb").css("top",(e.pageY - yOffset - diff) + "px");
				}
		
				$("a.thumb").hover(function(e)
				{
					var hrefString = $(this).attr("href");
					var parts = hrefString.split("/");
					var image = parts.pop();
					parts = image.split("?");
		            var width = 0;
		            var height = 0;
		
					if (parts.length == 2) // xxx.jpg?width=64&height=5684
					{
		                var query = parts.pop();
		                image = parts.pop();
		
		                parts = query.split("&");
		                if (parts.length == 2) // width=64&height=5684
		                {
		                    var q1 = parts.pop();
		                    var p1 = q1.split("=");
		                    if (p1.length == 2) // width=64
		                    {
		                        var v1 = p1.pop();
		                        var n1 = p1.pop();
		                        if (n1 == "width") width = v1;
		                        if (n1 == "height") height = v1;
		                    }
		                    var q2 = parts.pop();
		                    var p2 = q2.split("=");
		                    if (p2.length == 2) // height=5684
		                    {
		                        var v2 = p2.pop();
		                        var n2 = p2.pop();
		                        if (n2 == "width") width = v2;
		                        if (n2 == "height") height = v2;
		                    }
		                }
					}
		
					$("#thumb").remove();
					$("body").append("<div id=\"thumb\"><img src=\"' . implode("/", [WIKINDX_URL_BASE, "core", "tiny_mce", "plugins", "wikindxImage", "dialog.php"]) . '?thumb=" + image + "\" alt=\"Preview\"><\/div>");
					positionThumbnail(e);
					$("#thumb").fadeIn("medium");
				},
				function(){
					$("#thumb").remove();
				});
				$("a.thumb").click(function (e) {
					$("#thumb").remove();
		
					var hrefString = $(this).attr("href");
					var parts = hrefString.split("/");
					var image = parts.pop();
					parts = image.split("?");
		            var width = 0;
		            var height = 0;
		
					if (parts.length == 2) // xxx.jpg?width=64&height=5684
					{
		                var query = parts.pop();
		                image = parts.pop();
		
		                parts = query.split("&");
		                if (parts.length == 2) // width=64&height=5684
		                {
		                    var q1 = parts.pop();
		                    var p1 = q1.split("=");
		                    if (p1.length == 2) // width=64
		                    {
		                        var v1 = p1.pop();
		                        var n1 = p1.pop();
		                        if (n1 == "width") width = v1;
		                        if (n1 == "height") height = v1;
		                    }
		                    var q2 = parts.pop();
		                    var p2 = q2.split("=");
		                    if (p2.length == 2) // height=5684
		                    {
		                        var v2 = p2.pop();
		                        var n2 = p2.pop();
		                        if (n2 == "width") width = v2;
		                        if (n2 == "height") height = v2;
		                    }
		                }
					}
					var path = "' . WIKINDX_URL_BASE . '/' . WIKINDX_URL_DATA_IMAGES . '/" + image;
		
					imageDialogBrowse(path, width, height);
				});
				$("a.thumb").mousemove(function(e){
					positionThumbnail(e);
				});
			}); ';
        $pString .= LF . "</script>" . LF;

        $pString .= LF . "<div id=\"frame\">" . LF;

        $pString .= "<table class=\"table\">"
            . "<thead class=\"fixedHeader\">"
            . "<tr class=\"row one header\">" . LF
            . "<th class=\"iconH\">&nbsp;</th>" . LF
            . "<th class=\"nameH\">" . EncodeExplorer::makeArrow("name") . "</th>" . LF
            . "<th class=\"sizeH\">" . EncodeExplorer::makeArrow("size") . "</th>" . LF
            . "<th class=\"changedH\">" . EncodeExplorer::makeArrow("mod") . "</th>" . LF
            . "</tr>" . LF
            . "</thead>" . LF;
        //
        // Ready to display folders and files.
        //
        $row = 1;

        //
        // Now the files
        //
        if ($this->files)
        {
            $pString .= '<tbody class="scrollContent">' . LF;
            
            $count = 0;
            foreach ($this->files as $file)
            {
                $filename = EncodeExplorer::getFilename($file);
                list($width, $height, $type, $attr) = getimagesize(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES, $file->getName()]));

                // We limit the display size of images. This can be changed by the user afterwards in the textarea.
                $widthMax = WIKINDX_IMG_WIDTH_LIMIT;
                $heightMax = WIKINDX_IMG_HEIGHT_LIMIT;
                if (($width > $widthMax) && ($widthMax > 0))
                {
                    $height *= $widthMax / $width;
                    $height = floor($height);
                    $width = $widthMax;
                }
                if (($height > $heightMax) && ($heightMax > 0))
                {
                    $width *= $heightMax / $height;
                    $width = floor($width);
                    $height = $heightMax;
                }
                $row_style = ($row ? "one" : "two");
                $pString .= '<tr class="row ' . $row_style . (++$count == count($this->files) ? ' last' : '') . '">' . LF;
                //$pString .= '<tbody><tr class="row ' . $row_style . (++$count == count($this->files) ? ' last' : '') . '">'. LF;
                $pString .= '<td class="icon"><img alt="' . $file->getType() . '" src="' . $this->makeIcon($file->getType()) . '"></td>' . LF;
                $pString .= '<td class="name">' . LF;
                // For some reason, only width is accepted here. But, adding width automatically proportionately sets height when the image is inserted
                $pString .= '<a href="' . implode("/", [WIKINDX_URL_DATA_IMAGES, $file->getNameEncoded()]) . '?width=' . $width . '&amp;height=' . $height . '"';
                $pString .= ' class="item file';
                if ($file->isValidForThumb())
                {
                    $pString .= ' thumb';
                }
                $pString .= '">';
                $pString .= $filename;
                $pString .= '</a>';
                $pString .= '</td>' . LF;
                $pString .= '<td class="size">' . \FILE\formatSize($file->getSize()) . '</td>' . LF;
                $pString .= '<td class="changed">' . $this->formatModTime($file->getModTime()) . '</td>' . LF;
                //$pString .= "</tr></tbody>". LF;
                $pString .= '</tr>' . LF;
                $row = !$row;
            }

            $pString .= '</tbody>' . LF;
        }

        $pString .= '</table>' . LF;

        $pString .= '</div>' . LF;

//
        // The files have been displayed
//
        $pString .= '<!-- START: Upload area -->';
        $pString .= '<form enctype="multipart/form-data" method="post"><div id="upload"><div id="upload_container">';
        $pString .= '<input name="userfile" type="file" class="upload_file">';
        $pString .= '<input type="submit" value="' . $this->messages->text('tinymce', "upload") . '" class="upload_sumbit">';
        $pString .= '</div><div class="bar"></div></div></form><!-- END: Upload area -->';

        return $pString;
    }
}
