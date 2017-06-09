<?php

namespace Aligent\LiveChatBundle\Entity\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Aligent\LiveChatBundle\Entity\ChatTranscript;

class ChatTranscriptManager {

    /** @var ActivityManager */
    protected $activityManager;

    /**
     * @param ActivityManager $activityManager
     */
    public function __construct(ActivityManager $activityManager) {
        $this->activityManager = $activityManager;
    }


    /**
     * @param ChatTranscript $transcript
     * @param object $target
     *
     * @return bool TRUE if the association was added; otherwise, FALSE
     */
    public function addAssociation(ChatTranscript $transcript, $target) {
        return $this->activityManager->addActivityTarget($transcript, $target);
    }


    /**
     * Handle onFlush event
     *
     * @param OnFlushEventArgs $event
     */
    public function handleOnFlush(OnFlushEventArgs $event)
    {
        $em  = $event->getEntityManager();
        $uow = $em->getUnitOfWork();

        $newEntities = $uow->getScheduledEntityInsertions();
        foreach ($newEntities as $entity) {
            if ($entity instanceof ChatTranscript) {
                $hasChanges = $this->activityManager->addActivityTarget($entity, $entity->getOwner());
                // recompute change set if needed
                if ($hasChanges) {
                    $uow->computeChangeSet(
                        $em->getClassMetadata(ClassUtils::getClass($entity)),
                        $entity
                    );
                }
            }
        }

        $changedEntities = $uow->getScheduledEntityUpdates();
        foreach ($changedEntities as $entity) {
            if ($entity instanceof Call) {
                $hasChanges = false;
                $changeSet  = $uow->getEntityChangeSet($entity);
                foreach ($changeSet as $field => $values) {
                    if ($field === 'owner' || $field === 'contact' ) {
                        list($oldValue, $newValue) = $values;
                        if ($oldValue !== $newValue) {
                            $hasChanges |= $this->activityManager->replaceActivityTarget(
                                $entity,
                                $oldValue,
                                $newValue
                            );
                        }
                    }
                }
                // recompute change set if needed
                if ($hasChanges) {
                    $uow->computeChangeSet(
                        $em->getClassMetadata(ClassUtils::getClass($entity)),
                        $entity
                    );
                }
            }
        }
    }

}