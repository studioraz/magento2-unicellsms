<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SR\Unicellsms\Model;

class Service extends AbstractService
{
    const RESPONSE_CODE_SUCCESS = '0';
    const RESPONSE_CODE_INVALID_CREDENTIALS = '203';
    const RESPONSE_CODE_MISSING_CELLPHONE = '341';

    /**
     * @var mixed;
     */
    private $recipients = [];

    /**
     * @var string|null
     */
    private $message;

    /**
     * @param mixed|array $recipients
     * @param string|null $message
     * @throws \Exception
     */
    public function sendMessage($recipients, $message = null)
    {
        $this->setRecipients($recipients);
        $this->setMessage($message);
        $this->execute();
    }

    /**
     * @param mixed|array $recipients
     * @return $this
     */
    public function setRecipients($recipients)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $this->recipients = $recipients;
        return $this;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param string|null $message
     * @return $this
     */
    public function setMessage($message = null)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     * @throws \DOMException
     */
    protected function prepareRequest()
    {
        $this->generator->setIndexedArrayItemName('cellphone');

        $request = [
            'sms' => [
                'account' => [
                    'id' => $this->scopeConfig->getValue('unicell/general/account_id'),
                    'password' => $this->scopeConfig->getValue('unicell/general/account_password')
                ],
                'attributes' => [
                    'replyPath' => $this->scopeConfig->getValue('unicell/general/from_cellphone')
                ],
                'schedule' => [
                    'relative' => '0'
                ],
                'targets' => $this->getCellPhones(),
                'data' => $this->getMessage(),
            ]
        ];

        $xmlRequest = $this->generator->arrayToXml($request);

        return (string)$xmlRequest;
    }

    /**
     * @param $response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function handleResponse($response)
    {
        $xmlParser = $this->parser->loadXML($response);
        $this->response = $xmlParser->getDom();
    }

    /**
     * @return bool
     */
    public function isSuccessResponse()
    {
        return $this->getResponseCode() == self::RESPONSE_CODE_SUCCESS || strtolower($this->getResponseMessage()) == 'success';
    }

    /**
     * @return string
     */
    public function getResponseCode()
    {
        return $this->response->getElementsByTagName('code')->item(0)->nodeValue;
    }

    /**
     * @return string
     */
    public function getResponseMessage()
    {
        return $this->response->getElementsByTagName('message')->item(0)->nodeValue;
    }

    /**
     * @param $number
     * @return string|string[]|null
     */
    public function formatCellPhone($number)
    {
        return preg_replace('/^0(.*)$/', '972$1', $number);
    }

    /**
     * @return array
     */
    public function getCellPhones()
    {
        $cellphones = [];

        foreach (array_map([$this, 'formatCellPhone'], $this->recipients) as $recipient) {
            $cellphones[] = $recipient;
        }

        return $cellphones;
    }
}
