<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SR\Unicellsms\Model;

use Magento\Framework\Xml\Generator as XmlGenerator;
use Magento\Framework\Xml\Parser as XmlParser;
use Magento\Framework\App\Config\ScopeConfigInterface;

abstract class AbstractService
{
    /**
     * @var mixed|null
     */
    protected $response;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var XmlGenerator
     */
    protected $generator;

    /**
     * @var XmlParser
     */
    protected $parser;

    /**
     * AbstractService constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param XmlGenerator $generator
     * @param XmlParser $parser
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        XmlGenerator $generator,
        XmlParser $parser
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->generator = $generator;
        $this->parser = $parser;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $data = $this->prepareRequest();

        $headers = [
            "Content-type: text/xml",
            "Connection: close",
            "Content-Length: " . strlen($data),
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->scopeConfig->getValue('unicell/general/endpoint_url')); # http://api.soprano.co.il/
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        try {
            $this->handleResponse($response);
        } catch (\Exception $e) {
            throw new \Exception('Unicell SMS XML response can\'t be parsed');
        }

    }

    abstract protected function prepareRequest();

    abstract protected function handleResponse($response);

    abstract public function isSuccessResponse();

    abstract public function getResponseCode();

    abstract public function getResponseMessage();
}
