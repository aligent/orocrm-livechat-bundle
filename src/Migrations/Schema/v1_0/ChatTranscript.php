<?php

namespace Aligent\LiveChatBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Create Table Migration
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class ChatTranscript implements Migration, OrderedMigrationInterface {

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 1;
    }

    public function up(Schema $schema, QueryBag $queries) {
        $table = $schema->createTable('aligent_lc_chattranscript');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('contact_name', 'string', ['length' => 255]);
        $table->addColumn('agent_name', 'string', ['length' => 255]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('transcript', 'text', []);
        $table->addColumn('livechat_chat_id', 'string', ['length' => 255]);
        $table->addColumn('chat_start', 'datetime', []);
        $table->addColumn('chat_end', 'datetime', []);

        // Add owner (user) and contact linkage columns.
        $table->addColumn('owner_id', 'integer', []);
        $table->addColumn('contact_id', 'integer', []);

        // Add owner (organisation) column.
        $table->addColumn('organization_id', 'integer', []);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['livechat_chat_id'], 'IDX_livechat_chat_id', []);

    }
}