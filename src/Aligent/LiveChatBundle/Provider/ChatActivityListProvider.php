<?php

namespace Aligent\LiveChatBundle\Provider;

use Oro\Bundle\ActivityBundle\Tools\ActivityAssociationHelper;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\ActivityListBundle\Model\ActivityListDateProviderInterface;
use Oro\Bundle\CommentBundle\Model\CommentProviderInterface;
use Oro\Bundle\CommentBundle\Tools\CommentAssociationHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;
use Aligent\LiveChatBundle\Entity\ChatTranscript;
use Aligent\LiveChatBundle\Service\Webhook\TranscriptParser;

class ChatActivityListProvider implements
    ActivityListProviderInterface,
    CommentProviderInterface,
    ActivityListDateProviderInterface
{
    const ACTIVITY_CLASS = 'Aligent\LiveChatBundle\Entity\ChatTranscript';
    const ACL_CLASS = 'Aligent\LiveChatBundle\Entity\ChatTranscript';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ServiceLink */
    protected $entityOwnerAccessorLink;

    /** @var ActivityAssociationHelper */
    protected $activityAssociationHelper;

    /** @var CommentAssociationHelper */
    protected $commentAssociationHelper;

    /** @var TranscriptParser  */
    protected $transcriptParser;

    /**
     * @param DoctrineHelper            $doctrineHelper
     * @param ServiceLink               $entityOwnerAccessorLink
     * @param ActivityAssociationHelper $activityAssociationHelper
     * @param CommentAssociationHelper  $commentAssociationHelper
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ServiceLink $entityOwnerAccessorLink,
        ActivityAssociationHelper $activityAssociationHelper,
        CommentAssociationHelper $commentAssociationHelper,
        TranscriptParser $transcriptParser
    ) {
        $this->doctrineHelper            = $doctrineHelper;
        $this->entityOwnerAccessorLink   = $entityOwnerAccessorLink;
        $this->activityAssociationHelper = $activityAssociationHelper;
        $this->commentAssociationHelper  = $commentAssociationHelper;
        $this->transcriptParser          = $transcriptParser;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableTarget($entityClass, $accessible = true) {
        return $this->activityAssociationHelper->isActivityAssociationEnabled(
            $entityClass,
            self::ACTIVITY_CLASS,
            $accessible
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($entity) {
        /** @var $entity ChatTranscript */
        return trim(strip_tags($this->transcriptParser->getFirstCustomerMsg($entity->getTranscript())));return $entity->getLivechatChatId();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($entity) {
        /** @var $entity ChatTranscript */
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner($entity) {
        /** @var $entity ChatTranscript */
        return $entity->getOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt($entity) {
        /** @var $entity ChatTranscript */
        return $entity->getCreatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt($entity) {
        /** @var $entity ChatTranscript */
        return $entity->getCreatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getData(ActivityList $activityListEntity) {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization($activityEntity) {
        /** @var $activityEntity ChatTranscript */
        return $activityEntity->getOrganization();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate() {
        return 'AligentLiveChatBundle:Transcripts:js/activityItemTemplate.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes() {
        return [
            'itemView'   => 'livechat_transcript_widget_metadata',
            'itemEdit'   => '',
            'itemDelete' => ''
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityClass() {
        return self::ACTIVITY_CLASS;
    }

    /**
     * {@inheritdoc}
     */
    public function getAclClass() {
        return self::ACL_CLASS;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityId($entity) {
        return $this->doctrineHelper->getSingleEntityIdentifier($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($entity) {
        if (is_object($entity)) {
            $entity = $this->doctrineHelper->getEntityClass($entity);
        }

        return $entity == self::ACTIVITY_CLASS;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntities($entity) {
        return $entity->getActivityTargets();
    }

    /**
     * {@inheritdoc}
     */
    public function isCommentsEnabled($entityClass) {
        return $this->commentAssociationHelper->isCommentAssociationEnabled($entityClass);
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityOwners($entity, ActivityList $activityList) {
        $organization = $this->getOrganization($entity);
        $owner = $this->entityOwnerAccessorLink->getService()->getOwner($entity);

        if (!$organization || !$owner) {
            return [];
        }

        $activityOwner = new ActivityOwner();
        $activityOwner->setActivity($activityList);
        $activityOwner->setOrganization($organization);
        $activityOwner->setUser($owner);
        return [$activityOwner];
    }
}
