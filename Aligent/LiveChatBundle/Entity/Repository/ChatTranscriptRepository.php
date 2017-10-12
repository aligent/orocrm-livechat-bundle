<?php

namespace Aligent\LiveChatBundle\Entity\Repository;

use Aligent\LiveChatBundle\Entity\ChatTranscript;
use Doctrine\ORM\EntityRepository;

/**
 * Transcript Repository Manager
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class ChatTranscriptRepository extends EntityRepository {

    /**
     * Get chat transcripts based on LiveChat Chat Ids
     *
     * @param string $id Chat Id to fetch
     *
     * @return ChatTranscript
     */
    public function findTranscriptByLiveChatId($id) {
        return $this->findOneBy(['livechatChatId' => $id]);
    }

}
