<?php
/**
 * $Id: JSON.php 40 2007-06-18 11:43:15Z spocke $
 *
 * @package MCManager.utils
 *
 * @author Moxiecode
 * @copyright Copyright © 2007, Moxiecode Systems AB, All rights reserved.
 */
define('JSON_BOOL', 1);
define('JSON_INT', 2);
define('JSON_STR', 3);
define('JSON_FLOAT', 4);
define('JSON_NULL', 5);
define('JSON_START_OBJ', 6);
define('JSON_END_OBJ', 7);
define('JSON_START_ARRAY', 8);
define('JSON_END_ARRAY', 9);
define('JSON_KEY', 10);
define('JSON_SKIP', 11);

define('JSON_IN_ARRAY', 30);
define('JSON_IN_OBJECT', 40);
define('JSON_IN_BETWEEN', 50);

class Moxiecode_JSONReader
{
    public $_data;
    public $_len;
    public $_pos;
    public $_value;
    public $_token;
    public $_location;
    public $_lastLocations;
    public $_needProp;

    public function __construct($data)
    {
        $this->_data = $data;
        $this->_len = mb_strlen($data);
        $this->_pos = -1;
        $this->_location = JSON_IN_BETWEEN;
        $this->_lastLocations = [];
        $this->_needProp = FALSE;
    }

    public function getToken()
    {
        return $this->_token;
    }

    public function getLocation()
    {
        return $this->_location;
    }

    public function getTokenName()
    {
        switch ($this->_token) {
            case JSON_BOOL:
                return 'JSON_BOOL';

            case JSON_INT:
                return 'JSON_INT';

            case JSON_STR:
                return 'JSON_STR';

            case JSON_FLOAT:
                return 'JSON_FLOAT';

            case JSON_NULL:
                return 'JSON_NULL';

            case JSON_START_OBJ:
                return 'JSON_START_OBJ';

            case JSON_END_OBJ:
                return 'JSON_END_OBJ';

            case JSON_START_ARRAY:
                return 'JSON_START_ARRAY';

            case JSON_END_ARRAY:
                return 'JSON_END_ARRAY';

            case JSON_KEY:
                return 'JSON_KEY';
        }

        return 'UNKNOWN';
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function readToken()
    {
        $chr = $this->read();

        if ($chr != NULL)
        {
            switch ($chr) {
                case '[':
                    $this->_lastLocation[] = $this->_location;
                    $this->_location = JSON_IN_ARRAY;
                    $this->_token = JSON_START_ARRAY;
                    $this->_value = NULL;
                    $this->readAway();

                    return TRUE;

                case ']':
                    $this->_location = array_pop($this->_lastLocation);
                    $this->_token = JSON_END_ARRAY;
                    $this->_value = NULL;
                    $this->readAway();

                    if ($this->_location == JSON_IN_OBJECT)
                    {
                        $this->_needProp = TRUE;
                    }

                    return TRUE;

                case '{':
                    $this->_lastLocation[] = $this->_location;
                    $this->_location = JSON_IN_OBJECT;
                    $this->_needProp = TRUE;
                    $this->_token = JSON_START_OBJ;
                    $this->_value = NULL;
                    $this->readAway();

                    return TRUE;

                case '}':
                    $this->_location = array_pop($this->_lastLocation);
                    $this->_token = JSON_END_OBJ;
                    $this->_value = NULL;
                    $this->readAway();

                    if ($this->_location == JSON_IN_OBJECT)
                    {
                        $this->_needProp = TRUE;
                    }

                    return TRUE;

                // String
                case '"':
                case '\'':
                    return $this->_readString($chr);

                // Null
                case 'n':
                    return $this->_readNull();

                // Bool
                case 't':
                case 'f':
                    return $this->_readBool($chr);

                default:
                    // Is number
                    if (is_numeric($chr) || $chr == '-' || $chr == '.')
                    {
                        return $this->_readNumber($chr);
                    }

                    return TRUE;
            }
        }

        return FALSE;
    }

    public function _readBool($chr)
    {
        $this->_token = JSON_BOOL;
        $this->_value = $chr == 't';

        if ($chr == 't')
        {
            $this->skip(3); // rue
        }
        else
        {
            $this->skip(4); // alse
        }

        $this->readAway();

        if ($this->_location == JSON_IN_OBJECT && !$this->_needProp)
        {
            $this->_needProp = TRUE;
        }

        return TRUE;
    }

    public function _readNull()
    {
        $this->_token = JSON_NULL;
        $this->_value = NULL;

        $this->skip(3); // ull
        $this->readAway();

        if ($this->_location == JSON_IN_OBJECT && !$this->_needProp)
        {
            $this->_needProp = TRUE;
        }

        return TRUE;
    }

    public function _readString($quote)
    {
        $output = "";
        $this->_token = JSON_STR;
        $endString = FALSE;

        while (($chr = $this->peek()) != -1)
        {
            switch ($chr) {
                case '\\':
                    // Read away slash
                    $this->read();

                    // Read escape code
                    $chr = $this->read();
                    switch ($chr) {
                            case 't':
                                $output .= "\t";

                                break;

                            case 'b':
                                $output .= "\\b";

                                break;

                            case 'f':
                                $output .= "\f";

                                break;

                            case 'r':
                                $output .= "\r";

                                break;

                            case 'n':
                                $output .= "\n";

                                break;

                            case 'u':
                                $output .= $this->_int2utf8(hexdec($this->read(4)));

                                break;

                            default:
                                $output .= $chr;

                                break;
                    }

                    break;

                    case '\'':
                    case '"':
                        if ($chr == $quote)
                        {
                            $endString = TRUE;
                        }

                        $chr = $this->read();
                        if ($chr != -1 && $chr != $quote)
                        {
                            $output .= $chr;
                        }

                        break;

                    default:
                        $output .= $this->read();
            }

            // String terminated
            if ($endString)
            {
                break;
            }
        }

        $this->readAway();
        $this->_value = $output;

        // Needed a property
        if ($this->_needProp)
        {
            $this->_token = JSON_KEY;
            $this->_needProp = FALSE;

            return TRUE;
        }

        if ($this->_location == JSON_IN_OBJECT && !$this->_needProp)
        {
            $this->_needProp = TRUE;
        }

        return TRUE;
    }

    public function _int2utf8($int)
    {
        $int = intval($int);

        switch ($int) {
            case 0:
                return chr(0);

            case ($int & 0x7F):
                return chr($int);

            case ($int & 0x7FF):
                return chr(0xC0 | (($int >> 6) & 0x1F)) . chr(0x80 | ($int & 0x3F));

            case ($int & 0xFFFF):
                return chr(0xE0 | (($int >> 12) & 0x0F)) . chr(0x80 | (($int >> 6) & 0x3F)) . chr(0x80 | ($int & 0x3F));

            case ($int & 0x1FFFFF):
                return chr(0xF0 | ($int >> 18)) . chr(0x80 | (($int >> 12) & 0x3F)) . chr(0x80 | (($int >> 6) & 0x3F)) . chr(0x80 | ($int & 0x3F));
        }
    }

    public function _readNumber($start)
    {
        $value = "";
        $isFloat = FALSE;

        $this->_token = JSON_INT;
        $value .= $start;

        while (($chr = $this->peek()) != -1)
        {
            if (is_numeric($chr) || $chr == '-' || $chr == '.')
            {
                if ($chr == '.')
                {
                    $isFloat = TRUE;
                }

                $value .= $this->read();
            }
            else
            {
                break;
            }
        }

        $this->readAway();

        if ($isFloat)
        {
            $this->_token = JSON_FLOAT;
            $this->_value = floatval($value);
        }
        else
        {
            $this->_value = intval($value);
        }

        if ($this->_location == JSON_IN_OBJECT && !$this->_needProp)
        {
            $this->_needProp = TRUE;
        }

        return TRUE;
    }

    public function readAway()
    {
        while (($chr = $this->peek()) != NULL)
        {
            if ($chr != ':' && $chr != ',' && $chr != ' ')
            {
                return;
            }

            $this->read();
        }
    }

    public function read($len = 1)
    {
        if ($this->_pos < $this->_len)
        {
            if ($len > 1)
            {
                $str = mb_substr($this->_data, $this->_pos + 1, $len);
                $this->_pos += $len;

                return $str;
            }
            else
            {
                return $this->_data[++$this->_pos];
            }
        }

        return NULL;
    }

    public function skip($len)
    {
        $this->_pos += $len;
    }

    public function peek()
    {
        if ($this->_pos < $this->_len)
        {
            return $this->_data[$this->_pos + 1];
        }

        return NULL;
    }
}

/**
 * This class handles JSON stuff.
 *
 * @package MCManager.utils
 */
class Moxiecode_JSON
{
    public function __construct()
    {
    }

    public function decode($input)
    {
        $reader = new Moxiecode_JSONReader($input);

        return $this->readValue($reader);
    }

    public function readValue(&$reader)
    {
        $this->data = [];
        $this->parents = [];
        $this->cur = &$this->data;
        $key = NULL;
        $loc = JSON_IN_ARRAY;

        while ($reader->readToken())
        {
            switch ($reader->getToken()) {
                case JSON_STR:
                case JSON_INT:
                case JSON_BOOL:
                case JSON_FLOAT:
                case JSON_NULL:
                    switch ($reader->getLocation()) {
                        case JSON_IN_OBJECT:
                            $this->cur[$key] = $reader->getValue();

                            break;

                        case JSON_IN_ARRAY:
                            $this->cur[] = $reader->getValue();

                            break;

                        default:
                            return $reader->getValue();
                    }

                    break;

                case JSON_KEY:
                    $key = $reader->getValue();

                    break;

                case JSON_START_OBJ:
                case JSON_START_ARRAY:
                    if ($loc == JSON_IN_OBJECT)
                    {
                        $this->addArray($key);
                    }
                    else
                    {
                        $this->addArray(NULL);
                    }

                    $cur = &$obj;

                    $loc = $reader->getLocation();

                    break;

                case JSON_END_OBJ:
                case JSON_END_ARRAY:
                    $loc = $reader->getLocation();

                    if (count($this->parents) > 0)
                    {
                        $this->cur = &$this->parents[count($this->parents) - 1];
                        array_pop($this->parents);
                    }

                    break;
            }
        }

        return $this->data[0];
    }

    // This method was needed since PHP is crapy and doesn't have pointers/references
    public function addArray($key)
    {
        $this->parents[] = &$this->cur;
        $ar = [];

        if ($key)
        {
            $this->cur[$key] = &$ar;
        }
        else
        {
            $this->cur[] = &$ar;
        }

        $this->cur = &$ar;
    }

    public function getDelim($index, &$reader)
    {
        switch ($reader->getLocation()) {
            case JSON_IN_ARRAY:
            case JSON_IN_OBJECT:
                if ($index > 0)
                {
                    return ",";
                }

                break;
        }

        return "";
    }

    public function encode($input)
    {
        switch (gettype($input)) {
            case 'boolean':
                return $input ? 'true' : 'false';

            case 'integer':
                return (int) $input;

            case 'float':
            case 'double':
                return (float) $input;

            case 'NULL':
                return 'null';

            case 'string':
                return $this->encodeString($input);

            case 'array':
                return $this->_encodeArray($input);

            case 'object':
                return $this->_encodeArray(get_object_vars($input));
        }

        return '';
    }

    public function encodeString($input)
    {
        // Needs to be escaped
        if (preg_match('/[^a-zA-Z0-9]/u', $input))
        {
            $output = '';

            for ($i = 0; $i < mb_strlen($input); $i++)
            {
                switch ($input[$i]) {
                    case "\\b":
                        $output .= "\\b";

                        break;

                    case "\t":
                        $output .= "\\t";

                        break;

                    case "\f":
                        $output .= "\\f";

                        break;

                    case "\r":
                        $output .= "\\r";

                        break;

                    case "\n":
                        $output .= "\\n";

                        break;

                    case '\\':
                        $output .= "\\\\";

                        break;

                    case '\'':
                        $output .= "\\'";

                        break;

                    case '"':
                        $output .= '\"';

                        break;

                    default:
                        $byte = ord($input[$i]);

                        if (($byte & 0xE0) == 0xC0)
                        {
                            $char = pack('C*', $byte, ord($input[$i + 1]));
                            $i += 1;
                            $output .= sprintf('\u%04s', bin2hex($this->_utf82utf16($char)));
                        } if (($byte & 0xF0) == 0xE0)
                        {
                            $char = pack('C*', $byte, ord($input[$i + 1]), ord($input[$i + 2]));
                            $i += 2;
                            $output .= sprintf('\u%04s', bin2hex($this->_utf82utf16($char)));
                        } if (($byte & 0xF8) == 0xF0)
                        {
                            $char = pack('C*', $byte, ord($input[$i + 1]), ord($input[$i + 2]), ord($input[$i + 3]));
                            $i += 3;
                            $output .= sprintf('\u%04s', bin2hex($this->_utf82utf16($char)));
                        } if (($byte & 0xFC) == 0xF8)
                        {
                            $char = pack('C*', $byte, ord($input[$i + 1]), ord($input[$i + 2]), ord($input[$i + 3]), ord($input[$i + 4]));
                            $i += 4;
                            $output .= sprintf('\u%04s', bin2hex($this->_utf82utf16($char)));
                        } if (($byte & 0xFE) == 0xFC)
                        {
                            $char = pack('C*', $byte, ord($input[$i + 1]), ord($input[$i + 2]), ord($input[$i + 3]), ord($input[$i + 4]), ord($input[$i + 5]));
                            $i += 5;
                            $output .= sprintf('\u%04s', bin2hex($this->_utf82utf16($char)));
                        }
                        elseif ($byte < 128)
                        {
                            $output .= $input[$i];
                        }
                }
            }

            return '"' . $output . '"';
        }

        return '"' . $input . '"';
    }

    public function _utf82utf16($utf8)
    {
        return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
    }

    public function _encodeArray($input)
    {
        $output = '';
        $isIndexed = TRUE;

        $keys = array_keys($input);
        for ($i = 0; $i < count($keys); $i++)
        {
            if (!is_int($keys[$i]))
            {
                $output .= $this->encodeString($keys[$i]) . ':' . $this->encode($input[$keys[$i]]);
                $isIndexed = FALSE;
            }
            else
            {
                $output .= $this->encode($input[$keys[$i]]);
            }

            if ($i != count($keys) - 1)
            {
                $output .= ',';
            }
        }

        return $isIndexed ? '[' . $output . ']' : '{' . $output . '}';
    }
}
