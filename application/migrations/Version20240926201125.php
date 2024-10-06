<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240926201125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store message in db';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE message (id VARCHAR(255) NOT NULL, content TEXT NOT NULL, is_me BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN message.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE message');
    }
}
