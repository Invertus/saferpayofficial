<?php


namespace Invertus\SaferPay\DTO\Request;


class PendingNotification
{
    /**
     * @var array
     */
    private $merchantEmails;

    /**
     * @var string
     */
    private $notifyUrl;

    public function __construct($notifyUrl, $merchantEmails)
    {
        $this->notifyUrl = $notifyUrl;
        $this->merchantEmails = $merchantEmails;
    }

    public function getMerchantEmails()
    {
        return $this->merchantEmails;
    }

    /**
     * @param array $merchantEmails
     * @return PendingNotification
     */
    public function setMerchantEmails($merchantEmails)
    {
        $this->merchantEmails = $merchantEmails;

        return $this;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    /**
     * @param string $notifyUrl
     * @return PendingNotification
     */
    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;

        return $this;
    }
}
