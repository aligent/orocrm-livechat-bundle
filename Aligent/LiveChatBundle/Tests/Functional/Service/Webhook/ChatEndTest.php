<?php

namespace Aligent\LiveChatBundle\Tests\Functional\Service\Webhook;

use Aligent\LiveChatBundle\DataTransfer\ChatEndData;
use Aligent\LiveChatBundle\Entity\Repository\ChatTranscriptRepository;
use Aligent\LiveChatBundle\Service\Webhook\ChatException;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserData;

/**
 * Unit tests for Chat End service
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class ChatEndTest extends WebTestCase {

    /** @var  \Aligent\LiveChatBundle\Service\Webhook\ChatEnd */
    protected $chatEndService;

    protected function setUp() {
        parent::setUp();

        $this->initClient();

        $this->loadFixtures([
            'Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEmailData',
            'Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserData',
        ]);

        $this->chatEndService = $this->getContainer()->get('livechat.webhook_chatend');
    }


    /**
     * Smoke test for the "happy path" should not throw exceptions, but no return
     * value means nothing to test or assert here other than... TODO
     */
    public function testHandleRequesthappyPath() {
        $this->chatEndService->handleRequest('{
    "event_type": "chat_ended",
    "event_unique_id": "8dd982355fdae65ded2d44a99f2243e8",
    "token": "710747eff1dee5b3bbeb97186394b8c0",
    "license_id": "8762851",
    "chat": {
        "id": "OQ422UEDW5",
        "started_timestamp": 1494990905,
        "ended_timestamp": 1494998418,
        "url": "http://local.aligent.com/#",
        "messages": [
            {
                "user_type": "agent",
                "author_name": "Agent Smith",
                "agent_id": "smith@thematrix.com.au",
                "text": "Hello. How may I help you?",
                "timestamp": 1494990905
            },
            {
                "user_type": "agent",
                "author_name": "Thomas Anderson",
                "text": "I ordered a bazsqux a week ago and it still hasn\'t arrived!",
                "timestamp": 1494998205
            },
            {
                "user_type": "agent",
                "author_name": "Agent Smith",
                "agent_id": "smith@thematrix.com.au",
                "text": "I\'m sorry to hear that, let me check with the warehouse.",
                "timestamp": 1494998219
            },
            {
                "user_type": "agent",
                "author_name": "Thomas Anderson",
                "text": "Getting concerned, it\'s been a week now.",
                "timestamp": 1494998236
            },
            {
                "user_type": "agent",
                "author_name": "Agent Smith",
                "agent_id": "smith@thematrix.com.au",
                "text": "I understand your concern, I\'m looking into it now.",
                "timestamp": 1494998258
            },
            {
                "user_type": "agent",
                "author_name": "Agent Smith",
                "agent_id": "smith@thematrix.com.au",
                "text": "We\'ve located your order, it was sitting in the corner of the warehouse, the team was using it as a soccer ball during lunch, but it\'ll ship out today.",
                "timestamp": 1494998311
            },
            {
                "user_type": "agent",
                "author_name": "Thomas Anderson",
                "text": "What the?!?!",
                "timestamp": 1494998323
            },
            {
                "user_type": "agent",
                "author_name": "Agent Smith",
                "agent_id": "smith@thematrix.com.au",
                "text": "Your order got \"special treatment\", we\'re shipping it overnight and you\'ll have it tomorrow.",
                "timestamp": 1494998358
            },
            {
                "user_type": "agent",
                "author_name": "Thomas Anderson",
                "text": "Ummm, thanks. I guess?",
                "timestamp": 1494998386
            },
            {
                "user_type": "agent",
                "author_name": "Agent Smith",
                "agent_id": "smith@thematrix.com.au",
                "text": "Is there anything else I can help you with today?",
                "timestamp": 1494998402
            },
            {
                "user_type": "agent",
                "author_name": "Thomas Anderson",
                "text": "No you\'ve done enough.",
                "timestamp": 1494998413
            }
        ],
        "attachments": [],
        "events": [
            {
                "user_type": "visitor",
                "text": "Jim OHalloran closed the chat.",
                "timestamp": 1494998418,
                "type": "closed"
            }
        ],
        "agents": [
            {
                "name": "Agent Smith",
                "login": "jim@aligent.com.au"
            },
            {
                "name": "Another Agent Smith",
                "login": "simple_user@example.com"
            }
            
        ],
        "tags": [],
        "groups": [
            0
        ]
    },
    "visitor": {
        "id": "S1493177572.a78cd5f8c4",
        "name": "Thomas Anderson",
        "email": "test1@test.test",
        "country": "Australia",
        "city": "Adelaide",
        "language": "en",
        "page_current": "http://local.aligent.com/#",
        "timezone": "Australia/South"
    },
    "pre_chat_survey": [
        {
            "id": "2001",
            "type": "name",
            "label": "Name:",
            "answer": "Thomas Anderson"
        },
        {
            "id": "2002",
            "type": "email",
            "label": "E-mail:",
            "answer": "test1@test.test"
        }
    ]
}');
        /** @var Contact $contact */
        $contact = $this->getReference('Contact_' . LoadContactEntitiesData::FIRST_ENTITY_NAME);
        /** @var User $owner */
        $owner = $this->getReference(LoadUserData::SIMPLE_USER);

        /** @var ChatTranscriptRepository $transcriptRepo */
        $transcriptRepo = $this->getContainer()->get('livechat.repository.chattranscript');
        $transcript = $transcriptRepo->findTranscriptByLiveChatId('OQ422UEDW5');

        $this->assertNotNull($transcript);

        // Assert transcript is linked to contact and user
        $this->assertEquals($contact->getId(), $transcript->getContact()->getId());
        $this->assertEquals($owner->getId(), $transcript->getOwner()->getId());

        // Assert other critical fields are set correctly...
        $this->assertEquals('Wed, 17 May 2017 03:15:05 +0000', $transcript->getChatStart()->format(\DATE_RFC2822));
        $this->assertEquals('Wed, 17 May 2017 05:20:18 +0000',  $transcript->getChatEnd()->format(\DATE_RFC2822));
    }


    /**
     * Test that function to parse the necessary options out of the webhook payload
     * throws the appropriate exception when request is malformed in any way.
     *
     * @dataProvider malformedWebhookJson
     */
    public function testParseChatWebhookThrowsExceptionForMalformedRequests($jsonString) {
        $chatEndData = new ChatEndData();
        $this->expectException(ChatException::class);
        $this->chatEndService->parseChatWebhook($jsonString, $chatEndData);
    }


    /**
     * Various malformed requests for testing request parser.
     *
     * @return array
     */
    public function malformedWebhookJson() {
        return [
            ['{"event_type":"chat_started"}'],  // Set 0: Wrong event type
            ['{"event_type":""}'],            // Set 1: Empty event type
            ['{}'],                           // Set 2: Missing all fields but valid JSON
            ['foobar'],                       // Set 3: Invalid JSON

            // Set 4: Missing chat key
            ['{
                "event_type": "chat_ended"
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 5: Empty chat key
            ['{
                "event_type": "chat_ended",
                "chat": {},
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 6: Missing Chat Id
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 7: Empty chat Id
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 8: Missing chat start time
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 9: Missing chat end time
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 10: Missing chat messages key
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 11: Missing chat message user type
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 12: Missing chat message author name
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 13: Missing chat message text
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 14: Missing chat message timestamp
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?"
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 15: Missing chat agents key
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 16: Empty chat agents key
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": []
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 17: Missing chat agent name
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 18: Missing chat agent login
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 19: Missing visitor key
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                }
            }'],

            // Set 20: Missing visitor name
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "email": "tanderson@metacortex.com"
                }
            }'],

            // Set 21: Missing visitor email
            ['{
                "event_type": "chat_ended",
                "chat": {
                    "id": "OQ422UEDW5",
                    "started_timestamp": 1494990905,
                    "ended_timestamp": 1494998418,
                    "messages": [
                        {
                            "user_type": "agent",
                            "author_name": "Agent Smith",
                            "text": "Hello. How may I help you?",
                            "timestamp": 1494990905
                        }
                    ],
                    "agents": [
                        {
                            "name": "Agent Smith",
                            "login": "smith@thematrix.com.au"
                        }
                    ]
                },
                "visitor": {
                    "name": "Thomas Anderson",
                }
            }'],
        ];
    }


    /**
     * Parse data from a valid request and assert that it was parsed correctly
     * and in full.
     */
    public function testParseChatEndWebhookParsesRequiredFields() {
        /** @var ChatEndData $chatEndData */
        $chatEndData = new ChatEndData();
        $this->chatEndService->parseChatWebhook('{
    "event_type": "chat_ended",
    "event_unique_id": "8dd982355fdae65ded2d44a99f2243e8",
    "token": "710747eff1dee5b3bbeb97186394b8c0",
    "license_id": "8762851",
    "chat": {
        "id": "OQ422UEDW5",
        "started_timestamp": 1494990905,
        "ended_timestamp": 1494998418,
        "url": "http://local.aligent.com/#",
        "messages": [
            {
                "user_type": "agent",
                "author_name": "Agent Smith",
                "agent_id": "smith@thematrix.com.au",
                "text": "Hello. How may I help you?",
                "timestamp": 1494990905
            },
            {
                "user_type": "visitor",
                "author_name": "Thomas Anderson",
                "text": "I ordered a bazqux a week ago and it still hasn\'t arrived!",
                "timestamp": 1494998205
            },
            {
                "user_type": "supervisor",
                "author_name": "Agent Smith Clone",
                "agent_id": "smith2@thematrix.com.au",
                "text": "That didn\'t go well",
                "timestamp": 1494998413
            }
        ],
        "attachments": [],
        "events": [
            {
                "user_type": "visitor",
                "text": "Thomas Anderson closed the chat.",
                "timestamp": 1494998418,
                "type": "closed"
            }
        ],
        "agents": [
            {
                "name": "Agent Smith",
                "login": "smith@thematrix.com.au"
            },
            {
                "name": "Agent Smith Clone",
                "login": "smith2@thematrix.com.au"
            }
        ],
        "tags": [],
        "groups": [
            0
        ]
    },
    "visitor": {
        "id": "S1493177572.a78cd5f8c4",
        "name": "Thomas Anderson",
        "email": "tanderson@metacortex.com",
        "country": "Australia",
        "city": "Adelaide",
        "language": "en",
        "page_current": "http://local.aligent.com/#",
        "timezone": "Australia/South"
    },
    "pre_chat_survey": [
        {
            "id": "2001",
            "type": "name",
            "label": "Name:",
            "answer": "Thomas Anderson"
        },
        {
            "id": "2002",
            "type": "email",
            "label": "E-mail:",
            "answer": "tanderson@metacortex.com"
        }
    ]
}', $chatEndData);

        $this->assertEquals('tanderson@metacortex.com', $chatEndData->getVisitorEmail());
        $this->assertEquals('Thomas Anderson', $chatEndData->getVisitorName());
        $this->assertEquals('Agent Smith', $chatEndData->getAgentName());
        $this->assertEquals('smith@thematrix.com.au', $chatEndData->getAgentEmail());
        $this->assertEquals(['smith@thematrix.com.au', 'smith2@thematrix.com.au'], $chatEndData->getAgents());
        $this->assertEquals('OQ422UEDW5', $chatEndData->getChatId());
        $this->assertEquals('Wed, 17 May 2017 03:15:05 +0000', $chatEndData->getChatStart()->format(\DATE_RFC2822));
        $this->assertEquals('Wed, 17 May 2017 05:20:18 +0000',  $chatEndData->getChatEnd()->format(\DATE_RFC2822));
        $this->assertNotNull($chatEndData->getTranscript());
        $this->assertJson($chatEndData->getTranscript());
    }

}