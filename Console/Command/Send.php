<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SR\Unicellsms\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use SR\Unicellsms\Model\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Send extends Command
{
    const SMS_PHONE_NUMBER = 'phone_number';

    const SMS_TEXT_MESSGAE = 'text_message';

    /**
     * @var Service
     */
    protected $smsService;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Send constructor.
     * @param Service $smsService
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Service $smsService,
        ObjectManagerInterface $objectManager
    ) {
        $this->smsService = $smsService;
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName("sr:unicellsms:send")
            ->setDescription("A command the programmer was too lazy to enter a description for.")
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * Get list of options and arguments for the command
     * @return array
     */
    public function getInputList()
    {
        return [
            new InputArgument(
                self::SMS_PHONE_NUMBER,
                InputArgument::REQUIRED,
                'SMS telephone number'
            ),
            new InputArgument(
                self::SMS_TEXT_MESSGAE,
                InputArgument::REQUIRED,
                'SMS text message'
            )
        ];
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->smsService->sendMessage(
            $input->getArgument(self::SMS_PHONE_NUMBER),
            $input->getArgument(self::SMS_TEXT_MESSGAE)
        );

        $output->writeln(sprintf('Response code: %s, response message: %s', $this->smsService->getResponseCode(), $this->smsService->getResponseMessage()));
    }
}
