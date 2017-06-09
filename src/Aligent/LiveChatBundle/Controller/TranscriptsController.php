<?php

namespace Aligent\LiveChatBundle\Controller;


use Aligent\LiveChatBundle\Entity\ChatTranscript;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * Transcripts cpntroller
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 *
 * @Route("/transcripts")
 */
class TranscriptsController extends Controller {

    /**
     * @Route("/", name="livechat_transcript_index")
     * @Template()
     * @AclAncestor("livechat_transcript_view")
     */
    public function indexAction() {
        return array();
    }


    /**
     * @Route("/view/{id}", name="livechat_transcript_view", options= {"expose"= true})
     * @Template()
     * @AclAncestor("livechat_transcript_view")
     */
    public function viewAction(ChatTranscript $entity) {
        return [
            'entity' => $entity,
        ];
    }


    /**
     * @Route(
     *      "/widget/metadata/{id}/{renderContexts}",
     *      name="livechat_transcript_widget_metadata",
     *      requirements={"id"="\d+", "renderContexts"="\d+"},
     *      defaults={"renderContexts"=true},
     *      options={"expose"=true}
     * )
     * @Template("AligentLiveChatBundle:Transcripts/widget:metadata.html.twig")
     * @AclAncestor("livechat_transcript_view")
     */
    public function metadataAction(ChatTranscript $entity, $renderContexts) {
        return [
            'entity'         => $entity,
            'renderContexts' => (bool)$renderContexts
        ];
    }


    /**
     * @Route(
     *      "/widget/transcript/{id}/{renderContexts}",
     *      name="livechat_transcript_widget_transcript",
     *      requirements={"id"="\d+", "renderContexts"="\d+"},
     *      defaults={"renderContexts"=true},
     *      options={"expose"=true}
     * )
     * @Template("AligentLiveChatBundle:Transcripts/widget:transscript.html.twig")
     * @AclAncestor("livechat_transcript_view")
     */
    public function transcriptAction(ChatTranscript $entity, $renderContexts) {
        $transscriptParser = $this->container->get('livechat.webhook_transcriptparser');

        return [
            'entity'         => $entity,
            'transcript'    => $transscriptParser->parseEntityData($entity->getTranscript()),
            'renderContexts' => (bool)$renderContexts
        ];
    }



    /**
     * This action is used to render the list of chats associated with the given entity
     * on the view page of this entity
     *
     * @Route(
     *      "/activity/view/{entityClass}/{entityId}",
     *      name="livechat_activity_view"
     * )
     *
     * @AclAncestor("livechat_transcript_view")
     * @Template
     */
    public function activityAction($entityClass, $entityId) {
        $entity = $this->get('oro_entity.routing_helper')->getEntity($entityClass, $entityId);
        if (!$this->get('oro_security.security_facade')->isGranted('VIEW', $entity)) {
            throw new AccessDeniedException();
        }

        return [
            'entity' => $entity
        ];
    }
}