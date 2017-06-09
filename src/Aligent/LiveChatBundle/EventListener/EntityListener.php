<?php

namespace Aligent\LiveChatBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use Aligent\LiveChatBundle\Entity\Manager\ChatTranscriptManager;

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
