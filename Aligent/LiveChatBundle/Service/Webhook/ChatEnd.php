<?php

namespace Aligent\LiveChatBundle\Service\Webhook;

use Aligent\LiveChatBundle\DataTransfer\AbstractDTO;
use Aligent\LiveChatBundle\DataTransfer\ChatEndData;
use Aligent\LiveChatBundle\Entity\ChatTranscript;
use Aligent\LiveChatBundle\Entity\Repository\ChatTranscriptRepository;
use Aligent\LiveChatBundle\Entity\Repository\ContactRepository;
use Aligent\LiveChatBundle\Entity\Repository\UserRepository;
use Aligent\LiveChatBundle\Exception\ChatException;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\UserBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;


/**
 * Service class for handling the "chat_end" webhook.
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class ChatEnd extends ChatEventAbstract {

    const EVENT_TYPE = 'chat_ended';

    /** @var TranscriptParser  */
    protected $transcriptParser;

    /** @var ChatTranscriptRepository  */
    protected $transcriptRepository;

    /** @var ObjectManager  */
    protected $manager;

    /** @var ActivityManager */
    protected $activityManager;

    /** @var  UserRepository */
    protected $userRepo;

    public function __construct(
        LoggerInterface $logger,
        JsonEncoder $jsonEncoder,
        ContactRepository $contactRepo,
        TranscriptParser $transcriptParser,
        UserRepository $userRepo,
        ObjectManager $manager,
        ActivityManager $activityManager
    ) {
        parent::__construct($logger, $jsonEncoder, $contactRepo);
        $this->transcriptParser = $transcriptParser;
        $this->transcriptRepository = $manager->getRepository(ChatTranscript::class);;
        $this->userRepo = $userRepo;
        $this->manager = $manager;
        $this->activityManager = $activityManager;

        $this->logger->debug("Webhook ChatEnd service initialized.");
    }


    /**
     * @inheritdoc
     */
    public function handleRequest($jsonString) {
        $chatEndData = new ChatEndData();

        $this->parseChatWebhook($jsonString, $chatEndData);

        $contact = $this->contactRepo->getContactForEmail($chatEndData->getVisitorEmail());
        $users = $this->userRepo->getUsersForAgents($chatEndData->getAgents());

        $this->persistTranscript($chatEndData, $contact, $users);
    }


    /**
     * Extract the required fields from the chatEnd request payload.
     *
     * @param $jsonString string Raw JSON from request
     * @return array Parsed JSON data
     * @throws ChatException
     */
    public function parseChatWebhook($jsonString, AbstractDTO $chatEndData) {
        $jsonData = parent::parseChatWebhook($jsonString, $chatEndData);

        if ($this->hasNonEmptyKey('chat', $jsonData)) {
            $chat = $jsonData['chat'];
            if ($this->hasNonEmptyKey('id', $chat) &&
                $this->hasNonEmptyKey('started_timestamp', $chat) &&
                $this->hasNonEmptyKey('ended_timestamp', $chat) &&
                $this->hasNonEmptyKey('messages', $chat) &&
                $this->hasNonEmptyKey('agents', $chat)) {

                $chatEndData
                    ->setChatId($chat['id'])
                    ->setChatStart(new \DateTime('@'.$chat['started_timestamp']))
                    ->setChatEnd(new \DateTime('@'.$chat['ended_timestamp']))
                    ->setTranscript($this->transcriptParser->parseApiData($chat['messages']));

                $this->parseAgents($chat['agents'], $chatEndData);
            } else {
                $this->parseError("Required chat fields missing.");
            }
        } else {
            $this->parseError("chat key missing.");
        }

        if ($this->hasNonEmptyKey('visitor', $jsonData)) {
            $visitor = $jsonData['visitor'];
            if ($this->hasNonEmptyKey('name', $visitor)) {
                $chatEndData->setVisitorName($visitor['name']);
            } else {
                $this->parseError("Visitor name missing.");
            }
        } else {
            $this->parseError("Visitor key missing");
        }

        return $jsonData;
    }


    /**
     * Test that array key exists and doesn't contain an empty array or string.
     *
     * @param $key string They key to test for
     * @param $array array The array to test
     * @return bool TRUE if key exists and has value, FALSE otherwise
     */
    protected function hasNonEmptyKey($key, $array) {
        if (array_key_exists($key, $array)) {
            if (is_array($array[$key])) {
                return count($array[$key]) > 0;
            } else {
                return trim($array[$key]) != '';
            }
        }
        return false;
    }


    /**
     * Log the error and throw the appropriate exception.  Provides a shortcut
     * for bAiling out of webhook parsing without duplicating these two lines
     * everywhere.
     *
     * @param $msg string Error message
     * @throws ChatException
     */
    protected function parseError($msg) {
        $error = "Malformed chatEnd webhook.  ".$msg;
        $this->logger->error($error);
        throw new ChatException($error);
    }


    /**
     * Extract agent name and email data into local properties.
     *
     * @param array $agents Array of chat agents
     * @param ChatEndData $chatEndData Chat data transfer object
     */
    protected function parseAgents($agents, ChatEndData $chatEndData) {
        $parsed = [];

        $chatEndData->setAgentName(null);

        foreach ($agents as $idx => $agent) {
            if ($this->hasNonEmptyKey('name', $agent) &&
                $this->hasNonEmptyKey('login', $agent)) {

                // Capture email addresses for all agents, to link to all user accounts.
                $parsed[] = $agent['login'];
                // Capture name and email for first agent for the chat transcript record.
                if ($chatEndData->getAgentName(null) === null) {
                    $chatEndData->setAgentName($agent['name']);
                    $chatEndData->setAgentEmail($agent['login']);
                }

            } else {
                $this->parseError('Required agent fields missing at index: '.$idx);
            }
        }

        $chatEndData->setAgents($parsed);
    }


    /**
     * Persist the parsed chat transcript to the database, linking to contact
     * and users as appropriate.
     *
     * @param ChatEndData $chatEndData Chat DTO
     * @param $contact
     * @param $users
     */
    protected function persistTranscript($chatEndData, $contact, $users) {
        $contexts = [];

        // Don't duplicate the transcript in the case where we get the webhook
        // repeatedly.
        $chatTranscript = $this->transcriptRepository->findTranscriptByLiveChatId($chatEndData->getChatId());
        if ($chatTranscript === null) {
            $chatTranscript = new ChatTranscript();
        }

        $chatTranscript->setAgentName($chatEndData->getAgentName())
            ->setChatStart($chatEndData->getChatStart())
            ->setChatEnd($chatEndData->getChatEnd())
            ->setContactName($chatEndData->getVisitorName())
            ->setEmail($chatEndData->getVisitorEmail())
            ->setLivechatChatId($chatEndData->getChatId())
            ->setTranscript($chatEndData->getTranscript());

        if ($contact !== null) {
            $chatTranscript->setContact($contact);
            $contexts[] = $contact;
        }

        if (count($users) > 0) {
            $indexes = array_keys($users);
            $firstIndex = array_shift($indexes);
            /** @var User $user */
            $user = $users[$firstIndex];
            $chatTranscript->setOwner($user);
            $chatTranscript->setOrganization($user->getOrganization());

            $contexts = array_merge($contexts, $users);
        }

        $this->activityManager->setActivityTargets($chatTranscript, $contexts);

        $this->manager->persist($chatTranscript);
        $this->manager->flush();
    }
}