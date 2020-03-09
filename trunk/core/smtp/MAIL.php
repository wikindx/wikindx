<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * MAIL
 *
 * Common email functions. Updated 2016 to use PhpMailer classes
 *
 * @package wikindx\core\smtp
 */
class MAIL
{
    /** string */
    public $TransactionLog = '';
    /** object */
    private $mail;

    /**
     * MAIL
     */
    public function __construct()
    {
        // If messaging is turned off, just do nothing
        if (!WIKINDX_MAIL_USE) {
            return;
        }
        
        require WIKINDX_DIR_COMPONENT_VENDOR . '/phpmailer/Exception.php';
        require WIKINDX_DIR_COMPONENT_VENDOR . '/phpmailer/PHPMailer.php';
        require WIKINDX_DIR_COMPONENT_VENDOR . '/phpmailer/SMTP.php';

        $this->mail = new PHPMailer();

        // From (work because it's globaly defined)
        if (filter_var(WIKINDX_MAIL_FROM, FILTER_VALIDATE_EMAIL) !== FALSE) {
            $From = WIKINDX_MAIL_FROM;
        } else {
            // The fallback of HTTP_HOST is used for a CLI context only
            $From = \HTML\stripHtml(WIKINDX_TITLE) . '@' . (PHP_SAPI !== 'cli') ? $_SERVER["HTTP_HOST"] : "localhost";
        }

        $this->mail->setFrom(filter_var($From, FILTER_SANITIZE_EMAIL), WIKINDX_TITLE);

        // ReplyTo (work because it's globaly defined)
        if (filter_var(WIKINDX_MAIL_REPLYTO, FILTER_VALIDATE_EMAIL) !== FALSE) {
            $ReplyTo = WIKINDX_MAIL_REPLYTO;
        } else {
            $ReplyTo = WIKINDX_MAIL_REPLYTO_DEFAULT;
        }
        
        if ($ReplyTo != "") {
            $this->mail->addReplyTo(filter_var($ReplyTo, FILTER_SANITIZE_EMAIL), WIKINDX_TITLE);
        }
        
        // ContentType (work because it's globaly defined)
        $this->mail->ContentType = WIKINDX_MIMETYPE_TXT . ';charset=' . WIKINDX_CHARSET;

        if (WIKINDX_MAIL_BACKEND == 'smtp') {
            $this->mail->isSMTP();
            $this->mail->Host = WIKINDX_MAIL_SMTP_SERVER;
            $this->mail->Port = WIKINDX_MAIL_SMTP_PORT;
            $this->mail->SMTPAutoTLS = FALSE; // Never force TLS (some SMTP dislike it)
            $this->mail->SMTPSecure = WIKINDX_MAIL_SMTP_ENCRYPT;

            // Relax verification about certificats and DNS server name
            // We are not in a very sensitive context and certificates tend to pose problems during renewals
            $this->mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => FALSE,
                    'verify_peer_name' => FALSE,
                    'allow_self_signed' => TRUE,
                ],
            ];

            $this->mail->SMTPKeepAlive = WIKINDX_MAIL_SMTP_PERSIST;
            $this->mail->SMTPAuth = WIKINDX_MAIL_SMTP_AUTH;
            if (WIKINDX_MAIL_SMTP_AUTH) {
                $this->mail->Username = WIKINDX_MAIL_SMTP_USERNAME;
                $this->mail->Password = WIKINDX_MAIL_SMTP_PASSWORD;
            }
        } elseif (WIKINDX_MAIL_BACKEND == 'sendmail') {
            $this->mail->isSendmail();
            $this->mail->Sendmail = WIKINDX_MAIL_SENDMAIL_PATH;
        }
    }
    /**
     * Destructor
     *
     * @return null
     */
    public function __destruct()
    {
        // We have to close the SMTP connection just before the object is destroyed
        // because we have enabled KeepAlive mode for SMTP
        if (WIKINDX_MAIL_USE && WIKINDX_MAIL_BACKEND == 'smtp' && WIKINDX_MAIL_SMTP_PERSIST) {
            $this->mail->smtpClose();
        }
    }
    /**
     * Send an email
     *
     * @param string|string[] $addresses Either array of addresses or a single address (which might be ,|; delimited string of addresses)
     * @param string $subject the email subject
     * @param string $message the email message
     * @param bool $DebugMode enable the interception of the SMTP transaction log. Default is FALSE
     *
     * @return bool (TRUE on success, FALSE on failure)
     */
    public function sendEmail($addresses, $subject, $message, bool $DebugMode = FALSE)
    {
        $SendStatus = TRUE;

        // If the debug mode is enabled,
        // captures the SMTP log output...
        if ($DebugMode) {
            // Use only HTML because Wikindx is not usable with a CLI
            $this->TransactionLog = '';
            
            // Display the current config at the top of the log
            $this->TransactionLog .= "---[CONFIGURATION]------------------------------------------------------";
            $this->TransactionLog .= BR . BR;
            foreach ([
                "WIKINDX_MAIL_BACKEND",
                "WIKINDX_MAIL_FROM",
                "WIKINDX_MAIL_REPLYTO",
                "WIKINDX_MAIL_RETURN_PATH",
                "WIKINDX_MAIL_SENDMAIL_PATH",
                "WIKINDX_MAIL_SMTP_AUTH",
                "WIKINDX_MAIL_SMTP_ENCRYPT",
                "WIKINDX_MAIL_SMTP_PASSWORD",
                "WIKINDX_MAIL_SMTP_PERSIST",
                "WIKINDX_MAIL_SMTP_PORT",
                "WIKINDX_MAIL_SMTP_SERVER",
                "WIKINDX_MAIL_SMTP_USERNAME",
                "WIKINDX_MAIL_USE",
            ] as $k) {
                if ($k == "WIKINDX_MAIL_SMTP_PASSWORD") {
                    $this->TransactionLog .= $k . " = " . str_repeat("*", 8) . " (hidden value for security; its length is meaningless)" . BR;
                } else {
                    $this->TransactionLog .= $k . " = " . (constant($k) !== FALSE ? constant($k) : "0") . BR;
                }
            }
            $this->TransactionLog .= BR;
            $this->TransactionLog .= "---[LOG]----------------------------------------------------------------";
            $this->TransactionLog .= BR . BR;
            
            ob_start();
        }
        
        // If messaging is turned on
        if (WIKINDX_MAIL_USE) {
            // Avoid a special case
            if (!is_array($addresses)) {
                $addresses = [$addresses];
            }
            // To
            $ToArray = [];
            foreach ($addresses as $address) {
                // Split a single or multiple addresses in RFC822 format
                $tmpAddresses = $this->mail->parseAddresses(str_replace(';', ',', trim($address)), FALSE);
    
                // Send one message by address
                foreach ($tmpAddresses as $tmpAddress) {
                    $ToArray[] = $tmpAddress;
                }
            }
    
            // Avoid sending a message if there are no valid address
            if (count($ToArray) > 0) {
                // Message
                $this->mail->Subject = $subject;
                $this->mail->Body = $message;
    
                // If the debug mode is enabled,
                // captures the SMTP log output...
                if ($DebugMode) {
                    $this->mail->Debugoutput = 'echo';
                    $this->mail->SMTPDebug = 3;
                }
    
                // Send one message by address
                foreach ($ToArray as $To) {
                    $this->mail->addAddress($To['address'], $To['name']);
                    $SendStatus = $this->mail->send();
    
                    if ($DebugMode) {
                        echo BR . BR;
                        if ($SendStatus) {
                            echo "Message sent with " . WIKINDX_MAIL_BACKEND . " backend " .
                                 "to &lt;" . $To['address'] . "&gt; " . "without error.";
                        } else {
                            echo $this->mail->ErrorInfo;
                        }
                        echo BR . BR;
                    }
    
                    $this->mail->clearAddresses();
                }
    
                // If the debug mode is enabled,
                // ... and save it
                if ($DebugMode) {
                    $this->mail->SMTPDebug = 0;
                }
    
                // Clear
                $this->mail->clearBCCs();
                $this->mail->Subject = '';
                $this->mail->Body = '';
            } else {
                if ($DebugMode) {
                    echo "No valid recipient address to send or addresses not RFC822 compliant." . BR;
                }
    
                GLOBALS::setError("No valid recipient address to send to or addresses are not RFC822 compliant.");
                $SendStatus = FALSE;
            }
        } else {
            if ($DebugMode) {
                echo "The email sending function is disabled." . BR;
            }
        }

        // If the debug mode is enabled,
        // ... and save it
        if ($DebugMode) {
            $this->TransactionLog .= trim(ob_get_clean());
        }

        return $SendStatus;
    }
}
