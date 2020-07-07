<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/*
 * This file is extracted from the Symfony package.
 *
 * This code is responsible for providing access to the functions 
 * of the latest versions and PHP while using the minimum version 
 * accepted by Wikindx.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */


//---[PHP 8.0]---------------------------------------------------------
// Extracted from https://github.com/symfony/polyfill-php80
if (PHP_VERSION_ID < 80000) {
	define('FILTER_VALIDATE_BOOL', FILTER_VALIDATE_BOOLEAN);
	
	function fdiv(float $dividend, float $divisor): float
	{
	    return @($dividend / $divisor);
	}
	
	function get_debug_type($value): string
	{
	    switch (true) {
	        case null === $value: return 'null';
	        case \is_bool($value): return 'bool';
	        case \is_string($value): return 'string';
	        case \is_array($value): return 'array';
	        case \is_int($value): return 'int';
	        case \is_float($value): return 'float';
	        case \is_object($value): break;
	        case $value instanceof \__PHP_Incomplete_Class: return '__PHP_Incomplete_Class';
	        default:
	            if (null === $type = @get_resource_type($value)) {
	                return 'unknown';
	            }
	
	            if ('Unknown' === $type) {
	                $type = 'closed';
	            }
	
	            return "resource ($type)";
	    }
	
	    $class = \get_class($value);
	
	    if (false === strpos($class, '@')) {
	        return $class;
	    }
	
	    return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class').'@anonymous';
	}
	
	function get_resource_id($res): int
	{
	    if (!\is_resource($res) && null === @get_resource_type($res)) {
	        throw new \TypeError(sprintf('Argument 1 passed to get_resource_id() must be of the type resource, %s given', get_debug_type($res)));
	    }
	
	    return (int) $res;
	}
	
	function preg_last_error_msg(): string
	{
	    switch (preg_last_error()) {
	        case PREG_INTERNAL_ERROR:
	            return 'Internal error';
	        case PREG_BAD_UTF8_ERROR:
	            return 'Malformed UTF-8 characters, possibly incorrectly encoded';
	        case PREG_BAD_UTF8_OFFSET_ERROR:
	            return 'The offset did not correspond to the beginning of a valid UTF-8 code point';
	        case PREG_BACKTRACK_LIMIT_ERROR:
	            return 'Backtrack limit exhausted';
	        case PREG_RECURSION_LIMIT_ERROR:
	            return 'Recursion limit exhausted';
	        case PREG_JIT_STACKLIMIT_ERROR:
	            return 'JIT stack limit exhausted';
	        case PREG_NO_ERROR:
	            return 'No error';
	        default:
	            return 'Unknown error';
	    }
	}
	
	function str_contains(string $haystack, string $needle): bool
	{
	    return '' === $needle || false !== strpos($haystack, $needle);
	}
	
	function str_starts_with(string $haystack, string $needle): bool
	{
	    return 0 === \strncmp($haystack, $needle, \strlen($needle));
	}
	
	function str_ends_with(string $haystack, string $needle): bool
	{
	    return '' === $needle || ('' !== $haystack && 0 === \substr_compare($haystack, $needle, -\strlen($needle)));
	}
}


//---[PHP 7.4]---------------------------------------------------------
// Extracted from https://github.com/symfony/polyfill-php74
if (PHP_VERSION_ID < 70400) {
	function get_mangled_object_vars($obj)
	{
	    if (!\is_object($obj)) {
	        trigger_error('get_mangled_object_vars() expects parameter 1 to be object, '.\gettype($obj).' given', E_USER_WARNING);
	
	        return null;
	    }
	
	    if ($obj instanceof \ArrayIterator || $obj instanceof \ArrayObject) {
	        $reflector = new \ReflectionClass($obj instanceof \ArrayIterator ? 'ArrayIterator' : 'ArrayObject');
	        $flags = $reflector->getMethod('getFlags')->invoke($obj);
	        $reflector = $reflector->getMethod('setFlags');
	
	        $reflector->invoke($obj, ($flags & \ArrayObject::STD_PROP_LIST) ? 0 : \ArrayObject::STD_PROP_LIST);
	        $arr = (array) $obj;
	        $reflector->invoke($obj, $flags);
	    } else {
	        $arr = (array) $obj;
	    }
	
	    return array_combine(array_keys($arr), array_values($arr));
	}
	
	function mb_str_split($string, $split_length = 1, $encoding = null)
	{
	    if (null !== $string && !\is_scalar($string) && !(\is_object($string) && \method_exists($string, '__toString'))) {
	        trigger_error('mb_str_split() expects parameter 1 to be string, '.\gettype($string).' given', E_USER_WARNING);
	
	        return null;
	    }
	
	    if (1 > $split_length = (int) $split_length) {
	        trigger_error('The length of each segment must be greater than zero', E_USER_WARNING);
	
	        return false;
	    }
	
	    if (null === $encoding) {
	        $encoding = mb_internal_encoding();
	    }
	
	    if ('UTF-8' === $encoding || \in_array(strtoupper($encoding), array('UTF-8', 'UTF8'), true)) {
	        return preg_split("/(.{{$split_length}})/u", $string, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	    }
	
	    $result = array();
	    $length = mb_strlen($string, $encoding);
	
	    for ($i = 0; $i < $length; $i += $split_length) {
	        $result[] = mb_substr($string, $i, $split_length, $encoding);
	    }
	
	    return $result;
	}
	
	function password_algos()
	{
	    $algos = array();
	
	    if (\defined('PASSWORD_BCRYPT')) {
	        $algos[] = PASSWORD_BCRYPT;
	    }
	
	    if (\defined('PASSWORD_ARGON2I')) {
	        $algos[] = PASSWORD_ARGON2I;
	    }
	
	    if (\defined('PASSWORD_ARGON2ID')) {
	        $algos[] = PASSWORD_ARGON2ID;
	    }
	
	    return $algos;
	}
}


//---[PHP 7.3]---------------------------------------------------------
// Extracted from https://github.com/symfony/polyfill-php73
if (PHP_VERSION_ID < 70300) {
	function array_key_first(array $array) {
		foreach ($array as $key => $value) {
			return $key;
		}
	}
	
	function array_key_last(array $array) {
		end($array);
		return key($array);
	}
	
	function is_countable($var) {
		return is_array($var) || $var instanceof Countable || $var instanceof ResourceBundle || $var instanceof SimpleXmlElement;
	}
	
	function hrtime($asNum = false)
	{
		$startAt = 1533462603;
	    $ns = microtime(false);
	    $s = substr($ns, 11) - $startAt;
	    $ns = 1E9 * (float) $ns;
	
	    if ($asNum) {
	        $ns += $s * 1E9;
	
	        return \PHP_INT_SIZE === 4 ? $ns : (int) $ns;
	    }
	
	    return array($s, (int) $ns);
	}
}



//---[PHP 7.2]---------------------------------------------------------
// Extracted from https://github.com/symfony/polyfill-php72
if (PHP_VERSION_ID < 70200) {
	define('PHP_FLOAT_DIG', 15);
	define('PHP_FLOAT_EPSILON', 2.2204460492503E-16);
	define('PHP_FLOAT_MIN', 2.2250738585072E-308);
	define('PHP_FLOAT_MAX', 1.7976931348623157E+308);
	
	if (!extension_loaded("libxml"))
	{
    	function utf8_encode($s)
    	{
    	    $s .= $s;
    	    $len = \strlen($s);
    	
    	    for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
    	        switch (true) {
    	            case $s[$i] < "\x80": $s[$j] = $s[$i]; break;
    	            case $s[$i] < "\xC0": $s[$j] = "\xC2"; $s[++$j] = $s[$i]; break;
    	            default: $s[$j] = "\xC3"; $s[++$j] = \chr(\ord($s[$i]) - 64); break;
    	        }
    	    }
    	
    	    return substr($s, 0, $j);
    	}
    	
    	function utf8_decode($s)
    	{
    	    $s = (string) $s;
    	    $len = \strlen($s);
    	
    	    for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) {
    	        switch ($s[$i] & "\xF0") {
    	            case "\xC0":
    	            case "\xD0":
    	                $c = (\ord($s[$i] & "\x1F") << 6) | \ord($s[++$i] & "\x3F");
    	                $s[$j] = $c < 256 ? \chr($c) : '?';
    	                break;
    	
    	            case "\xF0":
    	                ++$i;
    	                // no break
    	
    	            case "\xE0":
    	                $s[$j] = '?';
    	                $i += 2;
    	                break;
    	
    	            default:
    	                $s[$j] = $s[$i];
    	        }
    	    }
    	
    	    return substr($s, 0, $j);
    	}
	}
	
	function php_os_family()
	{
	    if ('\\' === \DIRECTORY_SEPARATOR) {
	        return 'Windows';
	    }
	
	    $map = array(
	        'Darwin' => 'Darwin',
	        'DragonFly' => 'BSD',
	        'FreeBSD' => 'BSD',
	        'NetBSD' => 'BSD',
	        'OpenBSD' => 'BSD',
	        'Linux' => 'Linux',
	        'SunOS' => 'Solaris',
	    );
	
	    return isset($map[PHP_OS]) ? $map[PHP_OS] : 'Unknown';
	}
	
	define('PHP_OS_FAMILY', php_os_family());
	
	function spl_object_id($object)
	{
	    if (null === $hash = spl_object_hash($object)) {
	        return;
	    }
	    
	    // BEGIN - init the default hash mask
	    // Code of initHashMask()
	    // cf. https://github.com/symfony/polyfill-php72/blob/master/Php72.php
	    $obj = (object) array();
	    $hashMask = -1;
	
	    // check if we are nested in an output buffering handler to prevent a fatal error with ob_start() below
	    $obFuncs = array('ob_clean', 'ob_end_clean', 'ob_flush', 'ob_end_flush', 'ob_get_contents', 'ob_get_flush');
	    foreach (debug_backtrace(\PHP_VERSION_ID >= 50400 ? DEBUG_BACKTRACE_IGNORE_ARGS : false) as $frame) {
	        if (isset($frame['function'][0]) && !isset($frame['class']) && 'o' === $frame['function'][0] && \in_array($frame['function'], $obFuncs)) {
	            $frame['line'] = 0;
	            break;
	        }
	    }
	    if (!empty($frame['line'])) {
	        ob_start();
	        debug_zval_dump($obj);
	        $hashMask = (int) substr(ob_get_clean(), 17);
	    }
	
	    $hashMask ^= hexdec(substr(spl_object_hash($obj), 16 - (\PHP_INT_SIZE * 2 - 1), (\PHP_INT_SIZE * 2 - 1)));
	    // END - init the default hash mask
	    
	    // On 32-bit systems, PHP_INT_SIZE is 4,
	    return $hashMask ^ hexdec(substr($hash, 16 - (\PHP_INT_SIZE * 2 - 1), (\PHP_INT_SIZE * 2 - 1)));
	}
	
	function stream_isatty($stream)
	{
	    if (!\is_resource($stream)) {
	        trigger_error('stream_isatty() expects parameter 1 to be resource, '.\gettype($stream).' given', E_USER_WARNING);
	
	        return false;
	    }
	
	    if ('\\' === \DIRECTORY_SEPARATOR) {
	        $stat = @fstat($stream);
	        // Check if formatted mode is S_IFCHR
	        return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
	    }
	
	    return \function_exists('posix_isatty') && @posix_isatty($stream);
	}
	
	function sapi_windows_vt100_support($stream, $enable = null)
	{
	    if (!\is_resource($stream)) {
	        trigger_error('sapi_windows_vt100_support() expects parameter 1 to be resource, '.\gettype($stream).' given', E_USER_WARNING);
	
	        return false;
	    }
	
	    $meta = stream_get_meta_data($stream);
	
	    if ('STDIO' !== $meta['stream_type']) {
	        trigger_error('sapi_windows_vt100_support() was not able to analyze the specified stream', E_USER_WARNING);
	
	        return false;
	    }
	
	    // We cannot actually disable vt100 support if it is set
	    if (false === $enable || !stream_isatty($stream)) {
	        return false;
	    }
	
	    // The native function does not apply to stdin
	    $meta = array_map('strtolower', $meta);
	    $stdin = 'php://stdin' === $meta['uri'] || 'php://fd/0' === $meta['uri'];
	
	    return !$stdin
	        && (false !== getenv('ANSICON')
	        || 'ON' === getenv('ConEmuANSI')
	        || 'xterm' === getenv('TERM')
	        || 'Hyper' === getenv('TERM_PROGRAM'));
	}
	
	function mb_chr($code, $encoding = null)
	{
	    if (0x80 > $code %= 0x200000) {
	        $s = \chr($code);
	    } elseif (0x800 > $code) {
	        $s = \chr(0xC0 | $code >> 6).\chr(0x80 | $code & 0x3F);
	    } elseif (0x10000 > $code) {
	        $s = \chr(0xE0 | $code >> 12).\chr(0x80 | $code >> 6 & 0x3F).\chr(0x80 | $code & 0x3F);
	    } else {
	        $s = \chr(0xF0 | $code >> 18).\chr(0x80 | $code >> 12 & 0x3F).\chr(0x80 | $code >> 6 & 0x3F).\chr(0x80 | $code & 0x3F);
	    }
	
	    if ('UTF-8' !== $encoding) {
	        $s = mb_convert_encoding($s, $encoding, 'UTF-8');
	    }
	
	    return $s;
	}
	
	function mb_ord($s, $encoding = null)
	{
	    if (null == $encoding) {
	        $s = mb_convert_encoding($s, 'UTF-8');
	    } elseif ('UTF-8' !== $encoding) {
	        $s = mb_convert_encoding($s, 'UTF-8', $encoding);
	    }
	
	    if (1 === \strlen($s)) {
	        return \ord($s);
	    }
	
	    $code = ($s = unpack('C*', substr($s, 0, 4))) ? $s[1] : 0;
	    if (0xF0 <= $code) {
	        return (($code - 0xF0) << 18) + (($s[2] - 0x80) << 12) + (($s[3] - 0x80) << 6) + $s[4] - 0x80;
	    }
	    if (0xE0 <= $code) {
	        return (($code - 0xE0) << 12) + (($s[2] - 0x80) << 6) + $s[3] - 0x80;
	    }
	    if (0xC0 <= $code) {
	        return (($code - 0xC0) << 6) + $s[2] - 0x80;
	    }
	
	    return $code;
	}
}


//---[PHP 7.1]---------------------------------------------------------
// Extracted from https://github.com/symfony/polyfill-php71
if (PHP_VERSION_ID < 70100) {
	/**
	 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
	 *
	 * @internal
	 */
	function is_iterable($var)
	{
	    return \is_array($var) || $var instanceof \Traversable;
	}
}