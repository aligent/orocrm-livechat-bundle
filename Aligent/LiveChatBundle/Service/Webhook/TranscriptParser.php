<?php

namespace Aligent\LiveChatBundle\Service\Webhook;

use Aligent\LiveChatBundle\Exception\ChatException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Parsong service for transcripts
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class TranscriptParser {

    /** @var JsonEncoder  */
    protected $jsonEncoder;
    /** @var LoggerInterface  */
    protected $logger;

    public function __construct(LoggerInterface $logger, JsonEncoder $jsonEncoder) {
        $this->logger = $logger;
        $this->jsonEncoder = $jsonEncoder;

        $this->logger->debug("Transcript Parser service initialized.");
    }


    /**
     * Validates the API transcript data and extracts just the fields we care
     * about.
     *
     * @param $messages array Raw transcript data from API
     * @return string
     * @throws ChatException
     */
    public function parseApiData($messages) {
        $parsed = [];

        foreach ($messages as $idx => $message) {
            if (isset($message['user_type']) &&
                isset($message['author_name']) &&
                isset($message['text']) &&
                isset($message['timestamp'])) {

                $item = [
                    'author' =>     $message['author_name'],
                    'is_contact' => ($message['user_type'] == 'visitor'),
                    'text' =>       $message['text'],
                    'time' =>       new \DateTime('@'.$message['timestamp']),
                ];

                $parsed[] = $item;
            } else {
                $this->logger->error('One or more required fields missing parsing chat transcript as index: '.$idx);
                throw new ChatException('One or more required fields missing parsing chat transcript as index: '.$idx);
            }
        }

        return (string) $this->jsonEncoder->encode($parsed, JsonEncoder::FORMAT);
    }


    /**
     * Parse the JSON blob attached to an entity.
     *
     * @param $jsonTranscript string Stored transcript
     * @return array
     */
    public function parseEntityData($jsonTranscript) {
        $transcriptArr = $this->jsonEncoder->decode($jsonTranscript, 'json');
        foreach ($transcriptArr as $idx => $item) {
            $timeArr = $item['time'];

            $transcriptArr[$idx]['time'] = new \DateTime($timeArr['date'], new \DateTimeZone($timeArr['timezone']));
        }

        return $transcriptArr;
    }


    /**
     * Returns the customer's first message from the chat to use as a subject.
     *
     * @param $jsonTranscript string Stored transcript
     * @return string Text of first customer message in chat
     */
    public function getFirstCustomerMsg($jsonTranscript) {
        $transcriptArr = $this->parseEntityData($jsonTranscript);
        foreach ($transcriptArr as $item) {
            if ($item['is_contact']) {
                return $item['text'];
            }
        }
        return '';
    }

}