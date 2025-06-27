<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626192037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_lesson (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, lesson_id INT NOT NULL, purchased_at DATETIME NOT NULL, INDEX IDX_9D266FCEA76ED395 (user_id), INDEX IDX_9D266FCECDF80196 (lesson_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_lesson ADD CONSTRAINT FK_9D266FCEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_lesson ADD CONSTRAINT FK_9D266FCECDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_lesson DROP FOREIGN KEY FK_9D266FCEA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_lesson DROP FOREIGN KEY FK_9D266FCECDF80196
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_lesson
        SQL);
    }
}
