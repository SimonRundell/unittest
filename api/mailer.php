<?php
/**
 * Minimal SMTP-over-SSL mailer. No Composer/PHPMailer dependency - just a
 * plain socket conversation with the mail server, which is all DreamHost's
 * SMTP (port 465, implicit TLS, AUTH LOGIN) needs.
 */
final class SmtpMailer
{
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $username,
        private readonly string $password,
        private readonly string $fromEmail,
        private readonly string $fromName
    ) {
    }

    /**
     * @throws RuntimeException on any SMTP protocol error
     */
    public function send(string $toEmail, string $subject, string $htmlBody): void
    {
        $socket = stream_socket_client(
            "ssl://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            15
        );

        if ($socket === false) {
            throw new RuntimeException("Could not connect to SMTP server: $errstr");
        }

        try {
            $this->expect($socket, '220');
            $this->command($socket, 'EHLO toolsforteaching.co.uk', '250');
            $this->command($socket, 'AUTH LOGIN', '334');
            $this->command($socket, base64_encode($this->username), '334');
            $this->command($socket, base64_encode($this->password), '235');
            $this->command($socket, "MAIL FROM:<{$this->fromEmail}>", '250');
            $this->command($socket, "RCPT TO:<{$toEmail}>", '250');
            $this->command($socket, 'DATA', '354');

            $headers = implode("\r\n", [
                'From: ' . $this->encodeHeader($this->fromName) . " <{$this->fromEmail}>",
                "To: <{$toEmail}>",
                'Subject: ' . $this->encodeHeader($subject),
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Date: ' . date('r'),
            ]);

            // Lines consisting of just a dot must be escaped per RFC 5321.
            $escapedBody = preg_replace('/^\./m', '..', $htmlBody);

            fwrite($socket, $headers . "\r\n\r\n" . $escapedBody . "\r\n.\r\n");
            $this->expect($socket, '250');
            $this->command($socket, 'QUIT', '221');
        } finally {
            fclose($socket);
        }
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    /**
     * @param resource $socket
     */
    private function command($socket, string $line, string $expectedCode): void
    {
        fwrite($socket, $line . "\r\n");
        $this->expect($socket, $expectedCode);
    }

    /**
     * @param resource $socket
     */
    private function expect($socket, string $expectedCode): void
    {
        $line = '';
        do {
            $line = fgets($socket, 1024);
            if ($line === false) {
                throw new RuntimeException('SMTP connection closed unexpectedly');
            }
        } while (isset($line[3]) && $line[3] === '-');

        if (!str_starts_with($line, $expectedCode)) {
            throw new RuntimeException("SMTP server rejected command, expected $expectedCode, got: $line");
        }
    }
}

function sendValidationEmail(array $smtpConfig, string $toEmail, string $validationLink): void
{
    $mailer = new SmtpMailer(
        $smtpConfig['smtpServer'],
        (int) $smtpConfig['smtpPort'],
        $smtpConfig['smtpUser'],
        $smtpConfig['smtpPass'],
        $smtpConfig['smtpFromEmail'],
        $smtpConfig['smtpFrom']
    );

    $safeLink = htmlspecialchars($validationLink, ENT_QUOTES, 'UTF-8');

    $html = <<<HTML
    <!doctype html>
    <html>
    <body style="font-family: Trebuchet MS, Arial, sans-serif; color: #1a1a1a;">
        <h2>Verify your email address</h2>
        <p>Thanks for registering on the Unit Testing exercise site. Click the
           link below to verify your email address and activate your account:</p>
        <p><a href="{$safeLink}">{$safeLink}</a></p>
        <p>This link expires in 24 hours. If you did not request this, you can
           safely ignore this email.</p>
    </body>
    </html>
    HTML;

    $mailer->send($toEmail, 'Verify your email address', $html);
}
