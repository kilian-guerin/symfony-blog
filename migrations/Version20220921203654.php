<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220921203654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F8697D13');
        $this->addSql('DROP INDEX IDX_23A0E66F8697D13 ON article');
        $this->addSql('ALTER TABLE article DROP comment_id');
        $this->addSql('ALTER TABLE comment ADD article_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('CREATE INDEX IDX_9474526C7294869C ON comment (article_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('CREATE INDEX IDX_23A0E66F8697D13 ON article (comment_id)');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7294869C');
        $this->addSql('DROP INDEX IDX_9474526C7294869C ON comment');
        $this->addSql('ALTER TABLE comment DROP article_id');
    }
}
