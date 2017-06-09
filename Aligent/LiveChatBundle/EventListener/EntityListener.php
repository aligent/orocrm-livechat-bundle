<?php

namespace Aligent\LiveChatBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use Aligent\LiveChatBundle\Entity\Manager\ChatTranscriptManager;

/**
 * Event Listener
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class EntityListener {
    /** @var ChatTranscriptManager */
    protected $transcriptManager;

    /**
     * @param ChatTranscriptManager $transcriptManager
     */
    public function __construct(ChatTranscriptManager $transcriptManager) {
        $this->transcriptManager = $transcriptManager;
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event) {
        $this->transcriptManager->handleOnFlush($event);
    }
}
