<?php
/**
 * $Id: rpc.php 915 2008-09-03 08:45:28Z spocke $
 *
 * @package MCManager.includes
 *
 * @author Moxiecode
 * @copyright Copyright � 2004-2007, Moxiecode Systems AB, All rights reserved.
 */
require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "includes/general.php"]);

// Set RPC response headers
header('Content-Type: text/plain');
header('Content-Encoding: UTF-8');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", FALSE);
header("Pragma: no-cache");

$raw = "";

// Try param
if (isset($_POST["json_data"]))
{
    $raw = getRequestParam("json_data");
}

// Try globals array
if (!$raw && isset($_GLOBALS) && isset($_GLOBALS["HTTP_RAW_POST_DATA"]))
{
    $raw = $_GLOBALS["HTTP_RAW_POST_DATA"];
}

// Try globals variable
if (!$raw && isset($HTTP_RAW_POST_DATA))
{
    $raw = $HTTP_RAW_POST_DATA;
}

// Try stream
if (!$raw)
{
    $raw = "" . file_get_contents("php://input");
}

// No input data
if (!$raw)
{
    die('{"result":null,"id":null,"error":{"errstr":"Could not get raw post data.","errfile":"","errline":null,"errcontext":"","level":"FATAL"}}');
}

// Passthrough request to remote server
if (isset($config['general.remote_rpc_url']))
{
    $url = parse_url($config['general.remote_rpc_url']);

    // Setup request
    $req = "POST " . $url["path"] . " HTTP/1.0" . CR . LF;
    $req .= "Connection: close" . CR . LF;
    $req .= "Host: " . $url['host'] . CR . LF;
    $req .= "Content-Length: " . mb_strlen($raw) . CR . LF;
    $req .= CR . LF . $raw;

    if (!isset($url['port']) || !$url['port'])
    {
        $url['port'] = 80;
    }

    $errno = $errstr = "";

    $socket = fsockopen($url['host'], intval($url['port']), $errno, $errstr, 30);
    if ($socket)
    {
        // Send request headers
        fwrite($socket, $req);

        // Read response headers and data
        $resp = "";
        while (!feof($socket))
        {
            $resp .= fgets($socket, 4096);
        }

        fclose($socket);

        // Split response header/data
        $resp = \UTF8\mb_explode(CR . LF . CR . LF, $resp);
        echo $resp[1]; // Output body
    }

    die();
}

// Get JSON data
$json = new Moxiecode_JSON();
$input = $json->decode($raw);

// Execute RPC
if (isset($config['general.engine']))
{
    $spellchecker = new $config['general.engine']($config);
    $result = call_user_func_array([$spellchecker, $input['method']], $input['params']);
}
else
{
    die('{"result":null,"id":null,"error":{"errstr":"You must choose an spellchecker engine in the config.php file.","errfile":"","errline":null,"errcontext":"","level":"FATAL"}}');
}

// Request and response id should always be the same
$output = [
    "id" => $input->id,
    "result" => $result,
    "error" => NULL,
];

// Return JSON encoded string
echo $json->encode($output);
