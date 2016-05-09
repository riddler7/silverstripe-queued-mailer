<?php namespace WebTorque\QueuedMailer\Transport;


class SendinBlueTransport implements Transport
{
    /**
     * @var \Sendinblue\Mailin
     */
    private $mailin;

    /**
     * IP Address of SendinBlue server
     * @var string
     */
    private $ipAddress;

    public function __construct($url, $accessKey, $ipAddress = null)
    {
        $this->mailin = new \Sendinblue\Mailin($url, $accessKey);
        $this->ipAddress = $ipAddress;
    }

    /**
     * @param string $app Name of app sending email
     * @param string $identifier Unique identifier for the email
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $html
     * @param string $plain
     * @param string $cc
     * @param string $bcc
     * @param array $attachments
     * @param array $headers
     * @param string|null $replyTo
     * @return bool|array
     */
    public function send($app, $identifier, $to, $from, $subject, $html, $plain, $cc, $bcc, $attachments, $headers, $replyTo = null)
    {
        if (empty($headers)) $headers = array();

        //add some extra info for tracking etc
        $headers['X-Mailin-custom'] = $identifier;
        $headers['X-Mailin-Tag'] = $app;

        if (!empty($this->ipAddress)) {
            $headers['X-Mailin-IP'] = $this->ipAddress;
        }

        $name = '';

        if (stripos($to, '<') !== false) {

            $parts = explode('<', $to);
            $name = $parts[0];

            preg_match('/\\<(.*?)\\>/', $to, $matches);

            if (!empty($matches)) {
                $to = $matches[1];
            }
        }

        $data = array(
            'to' => array($to => $name),
            'from' => array($from),
            'subject' => $subject,
            'html' => !empty($html) ? $html : $plain,
            'headers' => $headers
        );

        if (!empty($attachments)) {
            $data['attachment'] = $attachments;
        }

        if (!empty($replyTo)) {
            $data['replyTo'] = $replyTo;
        }

        if (!empty($cc)) {
            $data['cc'] = $cc;
        }

        if (!empty($bcc)) {
            $data['bcc'] = $bcc;
        }

        $result = $this->mailin->send_email($data);

        return $result['code'] === 'success' ? $result['data']['message-id'] : false;
    }
}