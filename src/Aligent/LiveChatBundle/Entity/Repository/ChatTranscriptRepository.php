<?php

namespace Aligent\LiveChatBundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Aligent\LiveChatBundle\Entity\ChatTranscript;

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
