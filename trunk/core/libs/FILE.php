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
 * Common FILE routines
 *
 * @package wikindx\core\libs\FILE
 */
namespace FILE
{
    /**
     * Set download headers
     *
     * @param string $type
     * @param int $size
     * @param string $filename
     * @param string $lastmodified
     * @param string $charset Default is ''
     */
    function setHeaders($type, $size, $filename, $lastmodified, $charset = '')
    {
        header("Content-type: $type" . (($charset != '') ? "; charset=$charset" : ''));
        header("Content-Disposition: inline; filename=\"$filename\"; size=\"$size\"");
        header("Content-Length: $size");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        header("Last-Modified: $lastmodified");
    }

    /**
     * Download file to user
     *
     * From http://uk3.php.net/function.readfile
     *
     * @param string $file
     *
     * @return bool
     */
    function readfile_chunked($file)
    {
        if ($handle = fopen($file, 'rb')) {
            // Always clear the buffer before sending
            // because sometimes there are barbage
            // and it's very difficult to find
            // where it is badded to the buffer
            ob_get_clean();
            ob_start();

            $chunksize = (1024 * 1024); // how many bytes per chunk

            // Never remove the buffer variable otherwise the fread()
            // while not send the data to the output
            $buffer = '';

            // Send the file by chunk
            while (!feof($handle)) {
                $buffer = fread($handle, $chunksize);
                echo $buffer;
                ob_flush();
                flush();
            }

            fclose($handle);

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * create a fileName for this file.  If directory based on session ID does not exist, create it.
     *
     * @param string $dirName
     * @param string $string File contents
     * @param string $extension File extension
     *
     * @return array (filename, full filepath)
     */
    function createFileName($dirName, $string, $extension)
    {
        $fileName = sha1(\UTILS\uuid() . $string) . $extension;

        return [$fileName, $dirName . DIRECTORY_SEPARATOR . $fileName];
    }

    /**
     * Get file max upload size
     *
     * @return int
     */
    function fileMaxSize()
    {
        $postMax = return_bytes(ini_get('post_max_size'));
        $uploadMax = return_bytes(ini_get('upload_max_filesize'));
        if ($postMax < $uploadMax) {
            return $postMax;
        } else {
            return $uploadMax;
        }
    }

    /**
     * Convert some ini values to numerical values (to bytes)
     *
     * @param string $val
     *
     * @return int
     */
    function return_bytes($val)
    {
        // cf. https://secure.php.net/manual/en/ini.core.php#ini.post-max-size
        // cf. https://secure.php.net/manual/en/faq.using.php#faq.using.shorthandbytes

        // Unit extraction
        $val = mb_strtolower(trim($val));
        $unit = mb_substr($val, -1);

        // If the unit is not defined, VAL is already in bytes
        // Otherwise, compute it
        if (!is_int($unit)) {
            $val = intval(mb_substr($val, 0, mb_strlen($val) - 1));

            $factor = 1024;
            switch ($unit) {
                case 'g':
                    $val *= $factor;
                    // no break
                case 'm':
                    $val *= $factor;
                    // no break
                case 'k':
                    $val *= $factor;
            }
        }

        return intval($val);
    }

    /**
     * Enumerate files and subdirectories of a directory except . and .. subdirectories
     *
     * @param string $dir A directory to explore
     *
     * @return array An array of file and subdirectory names
     */
    function dirToArray($dir)
    {
        $result = [];

        if (file_exists($dir)) {
            $cdir = scandir($dir);

            if ($cdir !== FALSE) {
                foreach ($cdir as $v) {
                    // Without hidden files
                    if (!in_array($v, ['.', '..'])) {
                        $result[] = $v;
                    }
                }
            }

            unset($cdir);
        }

        return $result;
    }

    /**
     * Enumerate subdirectories of a directory except . and .. subdirectories
     *
     * @param string $dir A directory to explore
     *
     * @return array An array of subdirectory names
     */
    function dirInDirToArray($dir)
    {
        $result = [];

        $cdir = dirToArray($dir);

        if (count($cdir) > 0) {
            foreach ($cdir as $v) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $v)) {
                    $result[] = $v;
                }
            }
        }

        unset($cdir);

        return $result;
    }

    /**
     * Enumerate files of a directory except . and .. subdirectories
     *
     * @param string $dir A directory to explore
     *
     * @return array An array of filenames
     */
    function fileInDirToArray($dir)
    {
        $result = [];

        $cdir = dirToArray($dir);

        if (count($cdir) > 0) {
            foreach ($cdir as $v) {
                if (is_file($dir . DIRECTORY_SEPARATOR . $v)) {
                    $result[] = $v;
                }
            }
        }

        unset($cdir);

        return $result;
    }

    /**
     * Enumerate recursively files and subdirectories of a directory except . and .. subdirectories
     *
     * @param string $dir A directory to explore
     *
     * @return array An array of file and subdirectory absolute paths
     */
    function recurse_AbsoluteDirToArray($dir)
    {
        $result = [];
        
        if (file_exists($dir)) {
            $cdir = scandir($dir);
            
            if ($cdir !== FALSE) {
                foreach ($cdir as $k => $v) {
                    if (!in_array($v, ['.', '..'])) {
                        $result[] = $dir . DIRECTORY_SEPARATOR . $v;
                        if (is_dir($dir . DIRECTORY_SEPARATOR . $v)) {
                            $result = array_merge($result, recurse_AbsoluteDirToArray($dir . DIRECTORY_SEPARATOR . $v));
                        }
                    }
                }
            }
            
            unset($cdir);
        }
        
        return $result;
    }
    

    /**
     * Enumerate recursively files and subdirectories of a directory except . and .. subdirectories
     *
     * @param string $dir A directory to explore
     *
     * @return array An array of file and subdirectory paths
     */
    function recurse_fileInDirToArray($dir)
    {
        $result = [];
        
        $cdir = dirToArray($dir);
        
        if (count($cdir) > 0) {
            foreach ($cdir as $k => $v) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $v)) {
                    foreach (recurse_fileInDirToArray($dir . DIRECTORY_SEPARATOR . $v) as $sk => $sv) {
                        $result[] = $v . DIRECTORY_SEPARATOR . (($sv == ".") ? "" : $sv);
                    }
                } else {
                    $result[] = $v;
                }
            }
        } else {
            $result[] = ".";
        }
        
        unset($cdir);
        
        return $result;
    }
    
    /**
     * Change recursively the last access/modification datetime of files and subdirectories of a directory
     *
     * @param string $dir A directory to explore
     * @param int $time A Unix timestamp
     */
    function recurse_ChangeDateOfFiles($dir, $time)
    {
        touch($dir, $time, $time);
        $cdir = dirToArray($dir);
        
        if (count($cdir) > 0) {
            foreach ($cdir as $k => $v) {
                touch($dir . DIRECTORY_SEPARATOR . $v, $time, $time);

                if (is_dir($dir . DIRECTORY_SEPARATOR . $v)) {
                    recurse_ChangeDateOfFiles($dir . DIRECTORY_SEPARATOR . $v, $time);
                }
            }
        }
    }

    /**
     * Copy recursively a folder
     *
     * @param string $src Source directory
     * @param string $dst Destination directory
     */
    function recurse_dir_copy($src, $dst)
    {
        if (PHP_SAPI === 'cli') {
            echo "COPY $src\n";
        }
        if (!file_exists($dst)) {
            mkdir($dst, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
        }
        
        foreach (dirToArray($src) as $fsobject) {
            if (is_dir($src . DIRECTORY_SEPARATOR . $fsobject)) {
                recurse_dir_copy($src . DIRECTORY_SEPARATOR . $fsobject, $dst . DIRECTORY_SEPARATOR . $fsobject);
            } else {
                if (PHP_SAPI === 'cli') {
                    echo "COPY " . $src . DIRECTORY_SEPARATOR . $fsobject . "\n";
                }
                copy($src . DIRECTORY_SEPARATOR . $fsobject, $dst . DIRECTORY_SEPARATOR . $fsobject);
            }
        }
    }

    /**
     * Remove recursively a folder
     *
     * @param string $dir Target directory
     */
    function recurse_rmdir($dir)
    {
        foreach (dirToArray($dir) as $fsobject) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $fsobject)) {
                recurse_rmdir($dir . DIRECTORY_SEPARATOR . $fsobject);
            } else {
                unlink($dir . DIRECTORY_SEPARATOR . $fsobject);
            }
        }
        
        if (file_exists($dir)) {
            if (is_dir($dir)) {
                if (PHP_SAPI === 'cli') {
                    echo "RMDIR $dir\n";
                }
                rmdir($dir);
            } else {
                unlink($dir);
            }
        }
    }

    /**
     * Remove a file
     *
     * @param string $file Target filename
     */
    function rmfile($file)
    {
        if (file_exists($file)) {
            if (PHP_SAPI === 'cli') {
                echo "RMFILE $file\n";
            }
            unlink($file);
        }
    }

    /**
     * Return filename, hash, type and size of an uploaded file or an array of such information for each file uploaded
     *
     * @param false|string $filename
     * @param bool $multiple multiple files (default FALSE)
     *
     * @return array (filename, hash, type, size) or array of these
     */
    function fileUpload($filename = FALSE, $multiple = FALSE)
    {
        if (isset($_FILES) && array_key_exists('file', $_FILES)) {
            $finfo = new \finfo(FILEINFO_MIME); // return mime type ala mimetype extension
            if ($multiple) {
                $fileArrays = rearrangeFilesArray($_FILES);
                $array = [];
                $fileArray = $fileArrays['file'];
                foreach ($fileArray['name'] as $index => $value) {
                    if (!$value) {
                        return [];
                    }
                    if (trim($filename)) {
                        $fileName = trim($filename);
                    } else {
                        $fileName = addslashes($value);
                    }
                    if (($fileName == '.') || ($fileName == '..')) {
                        continue;
                    }
                    $info = \UTF8\mb_explode(';', $finfo->file($fileArray['tmp_name'][$index]));
                    $array[] = [$fileName, sha1_file($fileArray['tmp_name'][$index]), $info[0], $fileArray['size'][$index], $index];
                }

                return $array;
            } elseif ($_FILES['file']['tmp_name']) {
                if (trim($filename)) {
                    $fileName = trim($filename);
                } else {
                    $fileName = addslashes($_FILES['file']['name']);
                }
                if (($fileName == '.') || ($fileName == '..')) {
                    return [FALSE, FALSE, FALSE, FALSE];
                }
                $info = \UTF8\mb_explode(';', $finfo->file($_FILES['file']['tmp_name']));
                return [$fileName, sha1_file($_FILES['file']['tmp_name']),
                    $info[0], $_FILES['file']['size'], ];
            } else {
                return [FALSE, FALSE, FALSE, FALSE];
            }
        }

        return [FALSE, FALSE, FALSE, FALSE];
    }
    /**
     * Rearrange the $_FILES array for multiple file uploads
     *
     * @param array $files
     *
     * @return array
     */
    function rearrangeFilesArray($files)
    {
        $names = ['name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1];
        foreach ($files as $key => $part) {
            // only deal with valid keys and multiple files
            $key = (string) $key;
            if (isset($names[$key]) && is_array($part)) {
                foreach ($part as $position => $value) {
                    $files[$position][$key] = $value;
                }
                // remove old key reference
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * Store uploaded file in given directory with given name
     *
     * @param string $dirName
     * @param string $name
     * @param false|int $index if moving multiple file uploads
     *
     * @return bool
     */
    function fileStore($dirName, $name, $index = FALSE)
    {
        if ($index !== FALSE) {
            if (!move_uploaded_file($_FILES['file']['tmp_name'][$index], $dirName . DIRECTORY_SEPARATOR . $name)) {
                return FALSE;
            }
        } else {
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $dirName . DIRECTORY_SEPARATOR . $name)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * list and HTML format all files for sessionID
     *
     * @return array (filesDir, fileDeleteSecs, fileArray)
     */
    function listFiles()
    {
        $session = \FACTORY_SESSION::getInstance();

        $fileExports = $session->getVar("fileExports");
        if (!$fileExports) {
            // no files in directory
            return [FALSE, FALSE, FALSE];
        }
        if (!file_exists(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]))) {
            return [FALSE, FALSE, TRUE];
        }

        $fileArray = [];

        if ($fileExports) {
            $files = array_intersect($fileExports, fileInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES])));

            foreach ($files as $file) {
                $fileArray[$file] = filemtime(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES, $file]));
            }

            asort($fileArray, SORT_NUMERIC);
            $fileArray = array_reverse($fileArray);
        }

        return [implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]), WIKINDX_FILE_DELETE_SECONDS, $fileArray];
    }

    /**
     * tidy up the files directory by removing all files and folders older than WIKINDX_FILE_DELETE_SECONDS
     */
    function tidyFiles()
    {
        if (file_exists(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]))) {
            $now = time();
            $maxTime = WIKINDX_FILE_DELETE_SECONDS;
            
            $fileDeleteArray = [];
            $fileKeepArray = [];
            
            foreach (fileInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES])) as $f) {
                $file = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES, $f]);
                if (($now - filemtime($file)) >= $maxTime) {
                    @unlink($file);
                    $fileDeleteArray[] = $f;
                } else {
                    $fileKeepArray[] = $f;
                }
            }

            // Remove reference to these files in session
            $session = \FACTORY_SESSION::getInstance();
            if (($sessArray = $session->getVar("fileExports")) && !empty($fileDeleteArray)) {
                foreach ($fileDeleteArray as $f) {
                    unset($sessArray[array_search($f, $sessArray)]);
                }
                if (!empty($sessArray)) {
                    $session->setVar("fileExports", $sessArray);
                } else {
                    $session->delVar("fileExports");
                }
            } elseif (!empty($fileKeepArray)) {
                $session->setVar("fileExports", $fileKeepArray);
            }
        }
    }

    /**
     * Zip up an array of files.  File is stored in files dir.
     *
     * @param array $files unqualified filenames (key is label of file, value is filename on disk)
     * @param string $path file path
     *
     * @return mixed unqualified SHA1'ed filename of zip or FALSE if failure
     */
    function zip($files, $path)
    {
        // Compute a filename with a SHA1 hash of all filenames concatenated
        $zipFile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES, sha1(implode('', $files)) . '.zip']);

        // If we can't create a Zip archive or add all files to it,
        // abort, clean Zip archive and return FALSE
        $allFilesZipped = TRUE;
        if (!class_exists('\ZipArchive')) {
            return FALSE;
        }
        $zip = new \ZipArchive;

        if ($zip->open($zipFile, \ZipArchive::CREATE)) {
            foreach ($files as $label => $file) {
                if (!$zip->addFile($path . DIRECTORY_SEPARATOR . $file, $label)) {
                    $allFilesZipped = FALSE;

                    break;
                }
            }

            $zip->close();
        } else {
            $allFilesZipped = FALSE;
        }


        if ($allFilesZipped) {
            return $zipFile;
        } else {
            if (file_exists($zipFile)) {
                unlink($zipFile);
            }

            return FALSE;
        }
    }
    
    /**
     * Extract a WIKINDX Component Package to a folder
     *
     * The .tar.gz and .tar.bz2 archives are decompressed
     * in two stages and require the creation of a temporary
     * .tar file in the same directory as the archive to uncompress.
     *
     * If missing, the destination folder is created silently.
     * otherwise its contents is overwritten and existing files
     * that are not in the archive are deleted.
     *
     * It is not necessary to delete the files of the previous component
     * that occupies the same destination, which limits the loss of an already
     * installed component if the operation does not complete.
     *
     * However, it is always possible to end up in an inconsistent state
     * if the operation failed between the decompression of two files.
     * It should be very rare.
     *
     * So a component should never create files and folders in
     * its code directories otherwise they will be deleted.
     * Use the private cache and data folders of a component for this task.*
     *
     * BUGS: https://bugs.php.net/bug.php?id=79912
     * Phar crashs with an exception on Gzip/Bzip2 archives
     *
     * @param string $ComponentPackageFile Absolute or relative path to an archive file created with \FILE\createComponentPackage
     * @param string $DestinationFolder Absolute or relative path of a folder where the archive is extracted
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    function extractComponentPackage($ComponentPackageFile, $DestinationFolder)
    {
        if (\UTILS\matchSuffix($ComponentPackageFile, ".zip")) {
            $success = TRUE;
            $zip = new \ZipArchive;
            
            if ($zip->open($ComponentPackageFile)) {
                // On macOS extractTo() doesn't work, so we emulate it
                for ($k = 0; $k < $zip->numFiles; $k++) {
                    // Get a stream from the original name
                    $filename = $zip->getNameIndex($k);
                    $fp_src = $zip->getStream($filename);
                    
                    // Change the directory path of the filename
                    $filename = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $filename);
                    
                    // Restore the directory of the file first
                    $dir = dirname($filename);
                    if ($dir != "." && $dir != DIRECTORY_SEPARATOR) {
                        $dir = $DestinationFolder . DIRECTORY_SEPARATOR . $dir;
                        if (!file_exists($dir)) {
                            if (!mkdir($dir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE)) {
                                $success = FALSE;
                            }
                        }
                    }
                    
                    // Don't restore config.php and "plugintype.txt files of plugins if they already exist
                    // THIS IS A TEMPORARY FIX FOR THE 5.9.1 MIGRATION
                    if (in_array(basename($filename), ["config.php", "plugintype.txt"]) && dirname(dirname($filename)) == ".") {
                        if (file_exists($DestinationFolder . DIRECTORY_SEPARATOR . $filename)) {
                            // continue;
                        }
                    }
                    
                    // Restore the file
                    if ($fp_src !== FALSE) {
                        $fp_dst = fopen($DestinationFolder . DIRECTORY_SEPARATOR . $filename, "wb");
                        if ($fp_dst !== FALSE) {
                            stream_copy_to_stream($fp_src, $fp_dst);
                            fclose($fp_dst);
                        } else {
                            $success = FALSE;
                        }
                        fclose($fp_src);
                    }
                }
                
                // Clear existing files or empty directories not in the archive
                // A directory that become empty after the cleaning will not be removed.
                // To treat this case would add a lot of complexity and bring no added value.
                // A second decompression will remove the empty folder later anyway.
                $aExtractedFiles = [];
                for ($k = 0; $k < $zip->numFiles; $k++) {
                    $aExtractedFiles[] = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $zip->getNameIndex($k));
                }
                
                // Descends a folder lower than the destination because the package contains a root folder named with the component id
                $cmprootfolder = strrev(basename(strrev($aExtractedFiles[0])));
                $aExistingFiles = \FILE\recurse_fileInDirToArray($DestinationFolder . DIRECTORY_SEPARATOR . $cmprootfolder);
                foreach ($aExistingFiles as $k => $v) {
                    $aExistingFiles[$k] = $cmprootfolder . DIRECTORY_SEPARATOR . $v;
                }
                
                $aFilesInExcess = array_diff($aExistingFiles, $aExtractedFiles);
                foreach ($aFilesInExcess as $file) {
                    $file = $DestinationFolder . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($file)) {
                        rmdir($file);
                    } else {
                        unlink($file);
                    }
                }
                
                $zip->close();
            } else {
                $success = FALSE;
            }
            
            unset($zip);

            return $success;
        } elseif (\UTILS\matchSuffix($ComponentPackageFile, ".tar.gz") || \UTILS\matchSuffix($ComponentPackageFile, ".tar.bz2")) {
            try {
                $phar = new \PharData($ComponentPackageFile);
            } catch (UnexpectedValueException $e) {
                return FALSE;
            }
            
            // creates a temporary tar file
            try {
                $tar = $phar->decompress();
            } catch (BadMethodCallException $e) {
                return FALSE;
            }
            
            // Clear existing files or empty directories not in the archive
            // A directory that become empty after the cleaning will not be removed.
            // To treat this case would add a lot of complexity and bring no added value.
            // A second decompression will remove the empty folder later anyway.
            $aExtractedFiles = [];
            
            // Files in a tar phar archive are name with the scheme:
            // phar://path/to/the/archive.tar/directory/file.example
            // So we have to remove the root before a comparison
            $tarroot = "phar://" . $tar->getPath() . "/";
            $tarroot = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $tarroot);
            
            // On macOS extractTo() doesn't work, so we emulate it
            foreach (new \RecursiveIteratorIterator($tar) as $file) {
                $fileorig = $file;
                $file = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $file);
                $file = str_replace($tarroot, "", $file);
                
                // Restore the directory of the file first
                $dir = dirname($file);
                if ($dir != "." && $dir != DIRECTORY_SEPARATOR) {
                    $dir = $DestinationFolder . DIRECTORY_SEPARATOR . $dir;
                    if (!file_exists($dir)) {
                        if (!mkdir($dir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE)) {
                            $success = FALSE;
                        }
                    }
                }
                
                // Don't restore config.php and "plugintype.txt files of plugins if they already exist
                // THIS IS A TEMPORARY FIX FOR THE 5.9.1 MIGRATION
                if (in_array(basename($file), ["config.php", "plugintype.txt"]) && dirname(dirname($file)) == ".") {
                    if (file_exists($DestinationFolder . DIRECTORY_SEPARATOR . $file)) {
                        //continue;
                    }
                }
                
                // Restore the file
                try {
                    try {
                        $filephar = new PharFileInfo($fileorig);
                        file_put_contents($DestinationFolder . DIRECTORY_SEPARATOR . $file, $filephar->getContent());
                        unset($filephar);
                    } catch (BadMethodCallException $e) {
                        $success = FALSE;
                    }
                } catch (UnexpectedValueException $e) {
                    $success = FALSE;
                }
                
                $aExtractedFiles[] = $file;
            }
            
            // Descends a folder lower than the destination because the package contains a root folder named with the component id
            $cmprootfolder = strrev(basename(strrev($aExtractedFiles[0])));
            $aExistingFiles = \FILE\recurse_fileInDirToArray($DestinationFolder . DIRECTORY_SEPARATOR . $cmprootfolder);
            foreach ($aExistingFiles as $k => $v) {
                $aExistingFiles[$k] = $cmprootfolder . DIRECTORY_SEPARATOR . $v;
            }
            
            $aFilesInExcess = array_diff($aExistingFiles, $aExtractedFiles);
            foreach ($aFilesInExcess as $file) {
                $file = $DestinationFolder . DIRECTORY_SEPARATOR . $file;
                if (is_dir($file)) {
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }
            
            // Remove the temporary tar file
            unlink($tar->getPath());
            
            unset($tar);
            unset($phar);
            
            return TRUE;
        } else {
            // Unsupported format
            return FALSE;
        }
    }
    /**
     * Extract the component.json file of a WIKINDX Component Package to an array
     *
     * The .tar.gz and .tar.bz2 archives are decompressed
     * in two stages and require the creation of a temporary
     * .tar file in the same directory as the archive to uncompress.
     *
     * The component.json file is read in memory before parsing.
     *
     * @param string $ComponentPackageFile Absolute or relative path to an archive file created with \FILE\createComponentPackage
     *
     * @return array Structured definition of the component
     */
    function extractComponentPackageDefinition($ComponentPackageFile)
    {
        if (\UTILS\matchSuffix($ComponentPackageFile, ".zip")) {
            $componentDef = [];
            $zip = new \ZipArchive;
            
            if ($zip->open($ComponentPackageFile)) {
                // Search for the component.json only in the root directory
                for ($k = 0; $k < $zip->numFiles; $k++) {
                    $filename = $zip->getNameIndex($k);
                    if (basename($filename) == "component.json" && dirname(dirname($filename)) == ".") {
                        $fp = $zip->getStream($filename);
                        if ($fp !== FALSE) {
                            $json = stream_get_contents($fp);
                            fclose($fp);
                            
                            if ($json !== FALSE) {
                                $componentDef = json_decode($json, TRUE);
                            }
                        }

                        break;
                    }
                }
                
                $zip->close();
            }
            
            unset($zip);
            
            return $componentDef;
        } elseif (\UTILS\matchSuffix($ComponentPackageFile, ".tar.gz") || \UTILS\matchSuffix($ComponentPackageFile, ".tar.bz2")) {
            $componentDef = [];

            try {
                $phar = new \PharData($ComponentPackageFile);
            } catch (UnexpectedValueException $e) {
                return FALSE;
            }
            
            // creates a temporary tar file
            try {
                $tar = $phar->decompress();
            } catch (BadMethodCallException $e) {
                return FALSE;
            }
            
            // Files in a tar phar archive are name with the scheme:
            // phar://path/to/the/archive.tar/directory/file.example
            // So we have to remove the root before a comparison
            $tarroot = "phar://" . $tar->getPath() . "/";
            $tarroot = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $tarroot);
            
            // Search for the component.json only in the root directory
            foreach (new \RecursiveIteratorIterator($tar) as $file) {
                $fileorig = $file;
                $file = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $file);
                $file = str_replace($tarroot, "", $file);
                
                if (basename($file) == "component.json" && dirname(dirname($file)) == ".") {
                    try {
                        try {
                            $filephar = new PharFileInfo($fileorig);
                            $json = $filephar->getContent();
                            unset($filephar);
                        } catch (BadMethodCallException $e) {
                            return [];
                        }
                    } catch (UnexpectedValueException $e) {
                        return [];
                    }
                    
                    $componentDef = json_decode($json, TRUE);
                    
                    break;
                }
            }
            
            // Remove the temporary tar file
            unlink($tar->getPath());
            
            unset($tar);
            unset($phar);
            
            return $componentDef;
        } else {
            // Unsupported format
            return [];
        }
    }


    /**
     * Create a compressed package for the release of Wikindx core, manual or one of its components
     *
     * This function is a wrapper that hide the specifics of compression formats.
     *
     * @param string $SrcDir Absolute or relative path to a source directory
     * @param string $DstDir Absolute or relative path to a destination directory
     * @param string $Archive Name of the package file, without path and extension
     * @param string $Format Code of a package format (ZIP, GZ, BZIP2)
     *
     * @return string Absolute or relative path to the final package file with its extension
     */
    function createComponentPackageReproducible($SrcDir, $DstDir, $Archive, $Format)
    {
        recurse_ChangeDateOfFiles($SrcDir, WIKINDX_RELEASE_TIMESTAMP);

        $Format = strtoupper(trim($Format));
        // Unsupported format is replaced by a ZIP archive
        if (!in_array($Format, ["ZIP", "GZ", "BZIP2"])) {
            $Format = "ZIP";
        }
        
        switch ($Format) {
            case 'ZIP':
                $finalArchiveName = $DstDir . DIRECTORY_SEPARATOR . $Archive . ".zip";
                createComponentPackageZip($SrcDir, $finalArchiveName);

            break;
            
            case 'GZ':
                $finalArchiveName = $DstDir . DIRECTORY_SEPARATOR . $Archive . ".tar.gz";
                createComponentPackageGzUnix($SrcDir, $finalArchiveName);

            break;
            
            case 'BZIP2':
                $finalArchiveName = $DstDir . DIRECTORY_SEPARATOR . $Archive . ".tar.bz2";
                createComponentPackageBzip2Unix($SrcDir, $finalArchiveName);

            break;
        }
        
        return $finalArchiveName;
    }

    
    /**
     * Create a compressed package for the release of Wikindx core, manual or one of its components
     *
     * This function is a wrapper that hide the specifics of compression formats.
     *
     * @param string $SrcDir Absolute or relative path to a source directory
     * @param string $DstDir Absolute or relative path to a destination directory
     * @param string $Archive Name of the package file, without path and extension
     * @param string $Format Code of a package format (ZIP, GZ, BZIP2)
     *
     * @return string Absolute or relative path to the final package file with its extension
     */
    function createComponentPackageUnix($SrcDir, $DstDir, $Archive, $Format)
    {
        recurse_ChangeDateOfFiles($SrcDir, WIKINDX_RELEASE_TIMESTAMP);

        $Format = strtoupper(trim($Format));
        // Unsupported format is replaced by a ZIP archive
        if (!in_array($Format, ["ZIP", "GZ", "BZIP2"])) {
            $Format = "ZIP";
        }
        
        switch ($Format) {
            case 'ZIP':
                $finalArchiveName = $DstDir . DIRECTORY_SEPARATOR . $Archive . ".zip";
                createComponentPackageZipUnix($SrcDir, $finalArchiveName);

            break;
            
            case 'GZ':
                $finalArchiveName = $DstDir . DIRECTORY_SEPARATOR . $Archive . ".tar.gz";
                createComponentPackageGzUnix($SrcDir, $finalArchiveName);

            break;
            
            case 'BZIP2':
                $finalArchiveName = $DstDir . DIRECTORY_SEPARATOR . $Archive . ".tar.bz2";
                createComponentPackageBzip2Unix($SrcDir, $finalArchiveName);

            break;
        }
        
        return $finalArchiveName;
    }
    
    /**
     * Create a compressed package in .tar.bz2 format for the release of Wikindx core, manual or one of its components
     *
     * This function must be called from createComponentPackage() only.
     *
     * @param string $SrcDir Absolute or relative path to a source directory
     * @param string $DstFile Absolute or relative path to a destination package file
     */
    function createComponentPackageBzip2Unix($SrcDir, $DstFile)
    {
        $DstFileTar = dirname($DstFile) . DIRECTORY_SEPARATOR . basename($DstFile, ".bz2");
        $RootDir = dirname($SrcDir);
        $LastDir = basename($SrcDir);
        
        // Use flags to make the archive reproductible
        // cf. https://wiki.debian.org/ReproducibleBuilds/Howto#Identified_problems.2C_and_possible_solutions
        // Set unique perms: --mode=go=rX,u+rw,a-s
        // Sort filenames: --sort=name
        // Force a single timestamp: --mtime @1 --clamp-mtime
        // Set a single owner: --owner=0 --group=0 --numeric-owner
        
        exec("cd \"$RootDir\"; tar -cf \"$DstFileTar\" \"$LastDir\" --mode=go=rX,u+rw,a-s --sort=name --mtime=@1 --clamp-mtime --owner=0 --group=0 --numeric-owner; bzip2 -9 \"$DstFileTar\"");
    }
    
    /**
     * Create a compressed package in .tar.gz format for the release of Wikindx core, manual or one of its components
     *
     * This function must be called from createComponentPackage() only.
     *
     * @param string $SrcDir Absolute or relative path to a source directory
     * @param string $DstFile Absolute or relative path to a destination package file
     */
    function createComponentPackageGzUnix($SrcDir, $DstFile)
    {
        $DstFileTar = dirname($DstFile) . DIRECTORY_SEPARATOR . basename($DstFile, ".gz");
        $RootDir = dirname($SrcDir);
        $LastDir = basename($SrcDir);
        
        // Use flags to make the archive reproductible
        // cf. https://wiki.debian.org/ReproducibleBuilds/Howto#Identified_problems.2C_and_possible_solutions
        // Set unique perms: --mode=go=rX,u+rw,a-s
        // Sort filenames: --sort=name
        // Force a single timestamp: --mtime @1 --clamp-mtime
        // Set a single owner: --owner=0 --group=0 --numeric-owner
        
        exec("cd \"$RootDir\"; tar -cf \"$DstFileTar\" \"$LastDir\" --mode=go=rX,u+rw,a-s --sort=name --mtime=@1 --clamp-mtime --owner=0 --group=0 --numeric-owner; gzip -9 --no-name \"$DstFileTar\"");
    }
    
    /**
     * Create a compressed package in .zip format for the release of Wikindx core, manual or one of its components
     *
     * This function must be called from createComponentPackage() only.
     *
     * @param string $SrcDir Absolute or relative path to a source directory
     * @param string $DstFile Absolute or relative path to a destination package file
     */
    function createComponentPackageZipUnix($SrcDir, $DstFile)
    {
        $RootDir = dirname($SrcDir);
        $LastDir = basename($SrcDir);
        
        // Use flags to make the archive reproductible
        // cf. https://wiki.debian.org/ReproducibleBuilds/TimestampsInZip
        // Remove extra headers: -X
        // Force a single timestamp: --latest-time
        
        exec("cd \"$RootDir\"; zip -r -9 -X --latest-time \"$DstFile\" \"$LastDir\"");
    }
    
    /**
     * Create a compressed package for the release of Wikindx core, manual or one of its components
     *
     * This function is a wrapper that hide the specifics of compression formats.
     *
     * @param string $SrcDir Absolute or relative path to a source directory
     * @param string $DstDir Absolute or relative path to a destination directory
     * @param string $Archive Name of the package file, without path and extension
     * @param string $Format Code of a package format (ZIP, GZ, BZIP2)
     *
     * @return string Absolute or relative path to the final package file with its extension
     */
    function createComponentPackage($SrcDir, $DstDir, $Archive, $Format)
    {
        recurse_ChangeDateOfFiles($SrcDir, 0);

        $Format = strtoupper(trim($Format));
        // Unsupported format is replaced by a ZIP archive
        if (!in_array($Format, ["ZIP", "GZ", "BZIP2"])) {
            $Format = "ZIP";
        }
        
        switch ($Format) {
            case 'ZIP':
                $finalArchiveName = $DstDir . DIRECTORY_SEPARATOR . $Archive . ".zip";
                createComponentPackageZip($SrcDir, $finalArchiveName);

            break;
            
            case 'GZ':
                $finalArchiveName = $DstDir . DIRECTORY_SEPARATOR . $Archive . ".tar.gz";
                $TarArchive = createComponentPackageTar($SrcDir, $DstDir);
                createComponentPackageGz($TarArchive, $finalArchiveName);
                unlink($TarArchive);

            break;
            
            case 'BZIP2':
                $finalArchiveName = $DstDir . DIRECTORY_SEPARATOR . $Archive . ".tar.bz2";
                $TarArchive = createComponentPackageTar($SrcDir, $DstDir);
                createComponentPackageBzip2($TarArchive, $finalArchiveName);
                unlink($TarArchive);

            break;
        }
        
        return $finalArchiveName;
    }
    
    /**
     * Create a compressed package in .tar.bz2 format for the release of Wikindx core, manual or one of its components
     *
     * This function must be called from createComponentPackage() only.
     *
     * @param string $SrcFile Absolute or relative path to a source TAR file
     * @param string $DstFile Absolute or relative path to a destination package file
     */
    function createComponentPackageBzip2($SrcFile, $DstFile)
    {
        $fp = fopen($SrcFile, "rb");
        $data = fread($fp, filesize($SrcFile));
        fclose($fp);
        
        $zp = bzopen($DstFile, "w");
        bzwrite($zp, $data);
        bzclose($zp);
    }
    
    /**
     * Create a compressed package in .tar.gz format for the release of Wikindx core, manual or one of its components
     *
     * This function must be called from createComponentPackage() only.
     *
     * @param string $SrcFile Absolute or relative path to a source TAR file
     * @param string $DstFile Absolute or relative path to a destination package file
     */
    function createComponentPackageGz($SrcFile, $DstFile)
    {
        $fp = fopen($SrcFile, "rb");
        $data = fread($fp, filesize($SrcFile));
        fclose($fp);
        
        $zp = gzopen($DstFile, "w9");
        gzwrite($zp, $data);
        gzclose($zp);
    }
    
    /**
     * Create a compressed package in .zip format for the release of Wikindx core, manual or one of its components
     *
     * This function must be called from createComponentPackage() only.
     *
     * @param string $SrcDir Absolute or relative path to a source directory
     * @param string $DstFile Absolute or relative path to a destination package file
     */
    function createComponentPackageZip($SrcDir, $DstFile)
    {
        $rootdir = basename($SrcDir);
        
        $zip = new \ZipArchive;
        if ($zip->open($DstFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            foreach (recurse_fileInDirToArray($SrcDir) as $f) {
                if (\UTILS\matchSuffix($f, DIRECTORY_SEPARATOR) && $f != DIRECTORY_SEPARATOR) {
                    $f = mb_substr($f, 0, mb_strlen($f) - mb_strlen(DIRECTORY_SEPARATOR));
                    $zip->addEmptyDir($rootdir . DIRECTORY_SEPARATOR . $f);
                } else {
                    $zip->addFile($SrcDir . DIRECTORY_SEPARATOR . $f, $rootdir . DIRECTORY_SEPARATOR . $f);
                }
            }
            
            $zip->close();
        } else {
            if (PHP_SAPI === 'cli') {
                echo "Could not open " . $DstFile . "\n";
            }
        }
    }
    
    /**
     * Create a compressed package in .tar format for the release of Wikindx core, manual or one of its components
     *
     * This function must be called from createComponentPackage() only.
     *
     * @param string $SrcDir Absolute or relative path to a source directory
     * @param string $DstDir Absolute or relative path to a destination directory
     *
     * @return string Absolute or relative path to an intermediate TAR file (generate a random name itself)
     */
    function createComponentPackageTar($SrcDir, $DstDir)
    {
        // We must operate with an unique filename when it is tared
        // because there are a bug in Phar cache and no alternative for tar file in native PHP
        $Archive = $DstDir . DIRECTORY_SEPARATOR . \UTILS\uuid() . ".tar";
        
        // Open temporary tar file
        try {
            $phar = new \PharData($Archive);
        } catch (UnexpectedValueException $e) {
            if (PHP_SAPI === 'cli') {
                echo "Could not open " . $Archive . "\n";
            }

            return;
        }
        
        // Create temporary tar file
        try {
            $basedir = mb_substr($SrcDir, 0, mb_strlen($SrcDir) - mb_strlen(basename($SrcDir)));
            
            foreach (recurse_AbsoluteDirToArray($SrcDir) as $dir => $v) {
                $tarpath = mb_substr($v, 0 + mb_strlen($basedir), mb_strlen($v) - mb_strlen($basedir));

                if (is_dir($v)) {
                    $phar->addEmptyDir($tarpath);
                } else {
                    $phar->addFile($v, $tarpath);
                }
            }
        } catch (PharException $e) {
            if (PHP_SAPI === 'cli') {
                echo "Fail to make " . $Archive . "\n";
            }
        }
        
        // Clear temporary tar file pointer
        unset($phar);
        
        return $Archive;
    }

    /**
     * Does an unix command exist?
     *
     * @param string $command Command to test with the default shell
     *
     * @return bool TRUE on success
     */
    function command_exists($command)
    {
        if (suhosin_function_exists('proc_open')) {
            $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';

            $process = proc_open(
                "$whereIsCommand $command",
                [
                    0 => ["pipe", "r"], // STDIN
                    1 => ["pipe", "w"], // STDOUT
                    2 => ["pipe", "w"], // STDERR
                ],
                $pipes
            );

            if ($process !== FALSE) {
                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                return ($stdout != '');
            }
        }

        // Fallback
        return FALSE;
    }

    /**
     * Does a function exist ou is enabled with or without the suhosin security extension?
     *
     * @see https://suhosin.org/stories/index.html
     *
     * @author webmaster@mamo-net.de
     *
     * @param string $func Function name
     *
     * @return bool TRUE if the function is enabled
     */
    function suhosin_function_exists($func)
    {
        if (extension_loaded('suhosin')) {
            $suhosin = @ini_get("suhosin.executor.func.blacklist");
            if (empty($suhosin) == FALSE) {
                $suhosin = explode(',', $suhosin);
                $suhosin = array_map('trim', $suhosin);
                $suhosin = array_map('strtolower', $suhosin);

                return (function_exists($func) == TRUE && array_search($func, $suhosin) === FALSE);
            }
        }

        return function_exists($func);
    }

    /**
     * Read a JSON file to a data structure
     *
     * Read a JSON file and unserialize its content to a data structure with json_decode()
     *
     * @see https://www.php.net/manual/fr/function.json-decode.php
     *
     * @param string $file An absolute or relative path to a file
     *
     * @return null|mixed A value, a data structure, or NULL on error
     */
    function read_json_file($file)
    {
        if (file_exists($file)) {
            if (is_readable($file)) {
                $data = file_get_contents($file);
                if ($data !== FALSE) {
                    $data = json_decode($data, TRUE);
                    if (json_last_error() == JSON_ERROR_NONE) {
                        return $data;
                    }
                }
            }
        }

        return NULL;
    }

    /**
     * Write a data structure to a JSON file
     *
     * Serialize a data structure with json_encode() and write it in a file
     *
     * @see https://www.php.net/manual/fr/function.json-encode.php
     *
     * @param string $file An absolute or relative path to a file
     * @param mixed $data A data structure or a value
     *
     * @return int JSON_ERROR_NONE on success, a JSON error constant on encoding error, or JSON_ERROR_NONE - 1 on file writing error
     */
    function write_json_file($file, $data)
    {
        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($data !== FALSE) {
            return (file_put_contents($file, $data) === FALSE) ? JSON_ERROR_NONE - 1 : JSON_ERROR_NONE;
        } else {
            return json_last_error();
        }
    }
    /**
     * Format a file size in bytes to the greater multiple for display
     *
     * @param int $size In bytes
     *
     * @return string
     */
    function formatSize($size)
    {
        $sizes = [0 => 'B', 1024 => 'KB', 1048576 => 'MB', 1073741824 => 'GB', 1099511627776 => 'TB', 1125899906842624 => 'PB', 1152921504606846976 => 'EB'];

        $size = round($size, 2);

        foreach ($sizes as $s => $p) {
            if ($size >= $s) {
                $prefix = $p;
                $factor = $s;
            } else {
                break;
            }
        }

        if (!$factor) {
            $factor = 1;
        }

        return round(($size / $factor), 2) . "&nbsp;" . $prefix;
    }
}
