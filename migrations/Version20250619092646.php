<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619092646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cursus ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cursus ADD CONSTRAINT FK_255A0C3B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cursus ADD CONSTRAINT FK_255A0C3896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_255A0C3B03A8386 ON cursus (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_255A0C3896DBBDE ON cursus (updated_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, ADD is_validated TINYINT(1) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F87474F3B03A8386 ON lesson (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F87474F3896DBBDE ON lesson (updated_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme ADD CONSTRAINT FK_9775E708B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme ADD CONSTRAINT FK_9775E708896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9775E708B03A8386 ON theme (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9775E708896DBBDE ON theme (updated_by_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cursus DROP FOREIGN KEY FK_255A0C3B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cursus DROP FOREIGN KEY FK_255A0C3896DBBDE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_255A0C3B03A8386 ON cursus
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_255A0C3896DBBDE ON cursus
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cursus DROP created_by_id, DROP updated_by_id, DROP created_at, DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme DROP FOREIGN KEY FK_9775E708B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme DROP FOREIGN KEY FK_9775E708896DBBDE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_9775E708B03A8386 ON theme
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_9775E708896DBBDE ON theme
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme DROP created_by_id, DROP updated_by_id, DROP created_at, DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3896DBBDE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F87474F3B03A8386 ON lesson
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F87474F3896DBBDE ON lesson
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson DROP created_by_id, DROP updated_by_id, DROP is_validated, DROP created_at, DROP updated_at
        SQL);
    }
}
