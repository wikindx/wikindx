<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
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
    private $config;
    /** object */
    private $mail;

    /**
     * MAIL
     */
    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();
        if ($this->config->WIKINDX_MAIL_SERVER)
        {
            require WIKINDX_DIR_COMPONENT_VENDOR . '/phpmailer/Exception.php';
            require WIKINDX_DIR_COMPONENT_VENDOR . '/phpmailer/PHPMailer.php';
            require WIKINDX_DIR_COMPONENT_VENDOR . '/phpmailer/SMTP.php';

            $this->mail = new PHPMailer();

            // From (work because it's globaly defined)
            if (filter_var($this->config->WIKINDX_MAIL_FROM, FILTER_VALIDATE_EMAIL) !== FALSE)
            {
                $From = $this->config->WIKINDX_MAIL_FROM;
            }
            else
            {
                $From = \HTML\stripHtml($this->config->WIKINDX_TITLE) . '@' . $_SERVER['HTTP_HOST'];
            }

            $this->mail->setFrom(filter_var($From, FILTER_SANITIZE_EMAIL), 'WIKINDX');

            // ReplyTo (work because it's globaly defined)
            if (filter_var($this->config->WIKINDX_MAIL_REPLYTO, FILTER_VALIDATE_EMAIL) !== FALSE)
            {
                $ReplyTo = $this->config->WIKINDX_MAIL_REPLYTO;
            }
            else
            {
                $ReplyTo = WIKINDX_MAIL_REPLYTO_DEFAULT;
            }

            $this->mail->addReplyTo(filter_var($ReplyTo, FILTER_SANITIZE_EMAIL), 'WIKINDX');

            // ContentType (work because it's globaly defined)
            $this->mail->ContentType = WIKINDX_MIMETYPE_TXT . ';charset=' . WIKINDX_CHARSET;

            if ($this->config->WIKINDX_MAIL_BACKEND == 'smtp')
            {
                $this->mail->isSMTP();
                $this->mail->Host = $this->config->WIKINDX_MAIL_SMTPSERVER;
                $this->mail->Port = $this->config->WIKINDX_MAIL_SMTPPORT;
                $this->mail->SMTPAutoTLS = FALSE; // Never force TLS (some SMTP dislike it)
                $this->mail->SMTPSecure = $this->config->WIKINDX_MAIL_SMTPENCRYPT;

                // Relax verification about certificats and DNS server name
                // We are not in a very sensitive context and certificates tend to pose problems during renewals
                $this->mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => FALSE,
                        'verify_peer_name' => FALSE,
                        'allow_self_signed' => TRUE,
                    ],
                ];

                $this->mail->SMTPKeepAlive = $this->config->WIKINDX_MAIL_SMTPPERSIST;
                $this->mail->SMTPAuth = $this->config->WIKINDX_MAIL_SMTPAUTH;
                if ($this->config->WIKINDX_MAIL_SMTPAUTH)
                {
                    $this->mail->Username = $this->config->WIKINDX_MAIL_SMTPUSERNAME;
                    $this->mail->Password = $this->config->WIKINDX_MAIL_SMTPPASSWORD;
                }
            }
            elseif ($this->config->WIKINDX_MAIL_BACKEND == 'sendmail')
            {
                $this->mail->isSendmail();
                $this->mail->Sendmail = $this->config->WIKINDX_MAIL_SMPATH;
            }
            elseif ($this->config->WIKINDX_MAIL_BACKEND == 'mail')
            {
                $this->mail->isMail();
            }
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
        if ($this->config->WIKINDX_MAIL_SERVER && $this->config->WIKINDX_MAIL_BACKEND == 'smtp' && $this->config->WIKINDX_MAIL_SMTPPERSIST)
        {
            $this->mail->smtpClose();
        }
    }
    /**
     * Send an email
     *
     * @param string[]|string $addresses Either array of addresses or a single address (which might be ,|; delimited string of addresses)
     * @param string $subject the email subject
     * @param string $message the email message
     * @param bool $DebugMode enable the interception of the SMTP transaction log. Default is FALSE
     *
     * @return bool (TRUE on success, FALSE on failure)
     */
    public function sendEmail($addresses, $subject, $message, $DebugMode = FALSE)
    {
        $SendStatus = TRUE;

        if ($this->config->WIKINDX_MAIL_SERVER)
        {
            // Avoid a special case
            if (!is_array($addresses))
            {
                $addresses = [$addresses];
            }

            // To
            $ToArray = [];
            foreach ($addresses as $address)
            {
                // Split a single or multiple addresses in RFC822 format
                $tmpAddresses = $this->mail->parseAddresses(str_replace(';', ',', trim($address)), FALSE);

                // Send one message by address
                foreach ($tmpAddresses as $tmpAddress)
                {
                    $ToArray[] = $tmpAddress;
                }
            }

            // Avoid sending a message if there are no valid address
            if (count($ToArray) > 0)
            {
                // Message
                $this->mail->Subject = $subject;
                $this->mail->Body = $message;

                // If the debug mode is enabled,
                // captures the SMTP log output...
                if ($DebugMode === TRUE)
                {
                    // Use only HTML because Wikindx is not usable with a CLI
                    $this->mail->Debugoutput = 'html';
                    $this->mail->SMTPDebug = 3;
                    $this->TransactionLog = '';
                    ob_start();
                }

                // Send one message by address
                foreach ($ToArray as $To)
                {
                    $this->mail->addAddress($To['address'], $To['name']);
                    $SendStatus = $this->mail->send();

                    if ($DebugMode === TRUE)
                    {
                        if ($SendStatus)
                        {
                            $this->TransactionLog .= "Message sent with " . $this->config->WIKINDX_MAIL_BACKEND . " backend " .
                                "to &lt;" . $To['address'] . "&gt; " . "without error.<br>\n\n";
                        }
                        else
                        {
                            $this->TransactionLog .= $this->mail->ErrorInfo . "<br>\n\n";
                        }
                    }

                    $this->mail->clearAddresses();
                }

                // If the debug mode is enabled,
                // ... and save it
                if ($DebugMode === TRUE)
                {
                    $this->TransactionLog .= trim(ob_get_clean());
                    $this->mail->SMTPDebug = 0;
                }

                // Clear
                $this->mail->clearBCCs();
                $this->mail->Subject = '';
                $this->mail->Body = '';
            }
            else
            {
                if ($DebugMode === TRUE)
                {
                    $this->TransactionLog .= "No valid recipient address to send or addresses not RFC822 compliant.";
                }

                GLOBALS::setError("No valid recipient address to send to or addresses are not RFC822 compliant.");
                $SendStatus = FALSE;
            }
        }

        return $SendStatus;
    }
    /**
     * Get scriptPath
     *
     * @return string
     */
    public function scriptPath()
    {
        return $this->config->WIKINDX_BASE_URL;
    }
    /**
     * Get SCRIPT_NAME if redirect is in force
     *
     * @return string
     */
    private function scriptName()
    {
        if (array_key_exists('REDIRECT_URI', $_SERVER))
        {
            $script = preg_replace("/.*index\\.php/u", '/index.php', $_SERVER['SCRIPT_NAME']);
        }
        else
        {
            $script = $_SERVER['SCRIPT_NAME'];
        }

        return $script;
    }
}