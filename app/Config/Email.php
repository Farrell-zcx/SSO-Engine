<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = '';
    public string $fromName   = '';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = '';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'mail';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Hostname
     */
    public string $SMTPHost = '';

    /**
     * Which SMTP authentication method to use: login, plain
     */
    public string $SMTPAuthMethod = 'login';

    /**
     * SMTP Username
     */
    public string $SMTPUser = '';

    /**
     * SMTP Password
     */
    public string $SMTPPass = '';

    /**
     * SMTP Port
     */
    public int $SMTPPort = 25;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 5;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * SMTP Encryption.
     *
     * @var string '', 'tls' or 'ssl'. 'tls' will issue a STARTTLS command
     *             to the server. 'ssl' means implicit SSL. Connection on port
     *             465 should set this to ''.
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType = 'text';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;

    public function __construct()
    {
        parent::__construct();

        $getVal = function (string $key, $default = '') {
            if (function_exists('env')) {
                $v = env($key);
                if ($v !== null && $v !== '') {
                    return $v;
                }
            }
            $v = getenv($key);
            if ($v !== false && $v !== '') {
                return $v;
            }
            if (!empty($_ENV[$key])) {
                return $_ENV[$key];
            }
            if (!empty($_SERVER[$key])) {
                return $_SERVER[$key];
            }
            return $default;
        };

        // Support standard POSIX environment variables for Docker
        $this->protocol   = $getVal('SMTP_PROTOCOL', $getVal('email.protocol', $getVal('email_protocol', 'smtp')));
        $this->SMTPHost   = $getVal('SMTP_HOST', $getVal('email.SMTPHost', $getVal('email_SMTPHost', 'smtp.gmail.com')));
        $this->SMTPUser   = $getVal('SMTP_USER', $getVal('email.SMTPUser', $getVal('email_SMTPUser', '')));
        $this->SMTPPass   = $getVal('SMTP_PASS', $getVal('email.SMTPPass', $getVal('email_SMTPPass', '')));
        $this->SMTPPort   = (int) $getVal('SMTP_PORT', $getVal('email.SMTPPort', $getVal('email_SMTPPort', 587)));
        $this->SMTPCrypto = $getVal('SMTP_CRYPTO', $getVal('email.SMTPCrypto', $getVal('email_SMTPCrypto', 'tls')));

        // Anti-Spam: Pastikan fromEmail identik dengan SMTP_USER agar SPF/DKIM Gmail 100% Pass
        $this->fromEmail  = $getVal('SMTP_USER', $getVal('email.fromEmail', $getVal('email_fromEmail', '')));
        $this->fromName   = $getVal('SMTP_FROM_NAME', $getVal('email.fromName', $getVal('email_fromName', 'Farrel Labib - SSO Portal')));

        $this->mailType   = $getVal('SMTP_MAIL_TYPE', $getVal('email.mailType', $getVal('email_mailType', 'html')));
        $this->userAgent  = ''; // Hapus jejak bot CodeIgniter agar tidak ditandai sebagai spam
        $this->CRLF       = "\r\n";
        $this->newline    = "\r\n";
    }
}
