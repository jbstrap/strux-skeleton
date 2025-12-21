<?php

use Strux\Component\Database\Migration\Migration;

return new class extends Migration {
    function up(): void
    {
        $queries = [
            'CREATE TABLE IF NOT EXISTS `agents` (
                `agentID` BIGINT AUTO_INCREMENT NOT NULL,
				`userID` BIGINT NULL,
				`agentName` VARCHAR(255) NULL,
				`skillset` VARCHAR(255) NULL,
				`availability` VARCHAR(255) NULL,
				PRIMARY KEY (`agentID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `customers` (
                `customerID` BIGINT AUTO_INCREMENT NOT NULL,
				`userID` BIGINT NULL,
				`customerName` VARCHAR(255) NULL,
				`phone` VARCHAR(255) NULL,
				`address` VARCHAR(255) NULL,
				`accountStatus` VARCHAR(255) NULL,
				PRIMARY KEY (`customerID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `permissions` (
                `permissionID` BIGINT AUTO_INCREMENT NOT NULL,
				`name` VARCHAR(255) NOT NULL,
				`slug` VARCHAR(255) NOT NULL,
				PRIMARY KEY (`permissionID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `permissions_roles` (
                    `roleID` BIGINT NOT NULL,
                    `permissionID` BIGINT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'ALTER TABLE `permissions` ADD UNIQUE INDEX `permissions_slug_unique` (`slug`)',

            'CREATE TABLE IF NOT EXISTS `roles` (
                `roleID` BIGINT AUTO_INCREMENT NOT NULL,
				`name` VARCHAR(255) NOT NULL,
				`slug` VARCHAR(255) NOT NULL,
				`description` VARCHAR(255) NULL,
				PRIMARY KEY (`roleID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `accounts_roles` (
                    `userID` BIGINT NOT NULL,
                    `roleID` BIGINT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'ALTER TABLE `roles` ADD UNIQUE INDEX `roles_slug_unique` (`slug`)',

            'CREATE TABLE IF NOT EXISTS `accounts` (
                `userID` BIGINT AUTO_INCREMENT NOT NULL,
				`firstname` VARCHAR(255) NOT NULL,
				`lastname` VARCHAR(255) NOT NULL,
				`email` VARCHAR(255) NOT NULL,
				`password` VARCHAR(255) NOT NULL,
				`role` ENUM(\'Admin\', \'Agent\', \'Customer\') NULL DEFAULT \'Customer\',
				`createdAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				`updatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`userID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            'ALTER TABLE `accounts` ADD UNIQUE INDEX `accounts_email_unique` (`email`)',

            'CREATE TABLE IF NOT EXISTS `categories` (
                `categoryId` INT AUTO_INCREMENT NOT NULL,
				`parentId` INT NULL,
				`title` VARCHAR(255) NULL,
				`description` VARCHAR(255) NULL,
				`slug` VARCHAR(255) NULL,
				`image` VARCHAR(255) NULL,
				`headerImage` VARCHAR(255) NULL,
				`featured` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`categoryId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `products_categories` (
                    `productId` INT NOT NULL,
                    `categoryId` INT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'ALTER TABLE `categories` ADD UNIQUE INDEX `categories_slug_unique` (`slug`)',

            'CREATE TABLE IF NOT EXISTS `departments` (
                `departmentID` BIGINT AUTO_INCREMENT NOT NULL,
				`departmentName` VARCHAR(255) NOT NULL,
				PRIMARY KEY (`departmentID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            'ALTER TABLE `departments` ADD UNIQUE INDEX `departments_departmentName_unique` (`departmentName`)',

            'CREATE TABLE IF NOT EXISTS `products` (
                `productId` INT AUTO_INCREMENT NOT NULL,
				`title` VARCHAR(255) NULL,
				PRIMARY KEY (`productId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `tickets` (
                `ticketID` BIGINT AUTO_INCREMENT NOT NULL,
				`customerID` BIGINT NULL,
				`subject` VARCHAR(255) NOT NULL,
				`description` TEXT NULL,
				`statusID` BIGINT NULL,
				`priorityID` BIGINT NULL,
				`assignedTo` BIGINT NULL,
				`departmentID` BIGINT NULL,
				`createdAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				`updatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`ticketID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `ticket_attachments` (
                `attachmentID` BIGINT AUTO_INCREMENT NOT NULL,
				`commentID` BIGINT NOT NULL,
				`fileName` VARCHAR(255) NULL,
				`filePath` VARCHAR(500) NULL,
				`uploadedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`attachmentID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `ticket_comments` (
                `commentID` BIGINT AUTO_INCREMENT NOT NULL,
				`ticketID` BIGINT NOT NULL,
				`authorRole` ENUM(\'Admin\', \'Agent\', \'Customer\') NULL DEFAULT \'Customer\',
				`parentCommentID` BIGINT NULL,
				`message` TEXT NOT NULL,
				`createdAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				`updatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`commentID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `ticket_priority` (
                `priorityID` BIGINT AUTO_INCREMENT NOT NULL,
				`priorityName` VARCHAR(255) NULL,
				PRIMARY KEY (`priorityID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `ticket_status` (
                `statusID` BIGINT AUTO_INCREMENT NOT NULL,
				`statusName` VARCHAR(255) NULL,
				PRIMARY KEY (`statusID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',

            'ALTER TABLE `agents` ADD CONSTRAINT `fk_agents_userID` FOREIGN KEY (`userID`) REFERENCES `accounts` (`userID`) ON DELETE CASCADE ON UPDATE NO ACTION',

            'ALTER TABLE `customers` ADD CONSTRAINT `fk_customers_userID` FOREIGN KEY (`userID`) REFERENCES `accounts` (`userID`) ON DELETE CASCADE ON UPDATE NO ACTION',

            'ALTER TABLE `permissions_roles` ADD CONSTRAINT `fk_permissions_roles_permissionID` FOREIGN KEY (`permissionID`) REFERENCES `permissions` (`permissionID`) ON DELETE CASCADE ON UPDATE CASCADE',
            'ALTER TABLE `permissions_roles` ADD CONSTRAINT `fk_permissions_roles_roleID` FOREIGN KEY (`roleID`) REFERENCES `roles` (`roleID`) ON DELETE CASCADE ON UPDATE CASCADE',

            'ALTER TABLE `accounts_roles` ADD CONSTRAINT `fk_accounts_roles_roleID` FOREIGN KEY (`roleID`) REFERENCES `roles` (`roleID`) ON DELETE CASCADE ON UPDATE CASCADE',
            'ALTER TABLE `accounts_roles` ADD CONSTRAINT `fk_accounts_roles_userID` FOREIGN KEY (`userID`) REFERENCES `accounts` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE',

            'ALTER TABLE `products_categories` ADD CONSTRAINT `fk_products_categories_categoryId` FOREIGN KEY (`categoryId`) REFERENCES `categories` (`categoryId`) ON DELETE CASCADE ON UPDATE CASCADE',
            'ALTER TABLE `products_categories` ADD CONSTRAINT `fk_products_categories_productId` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`) ON DELETE CASCADE ON UPDATE CASCADE',

            'ALTER TABLE `tickets` ADD CONSTRAINT `fk_tickets_departmentID` FOREIGN KEY (`departmentID`) REFERENCES `departments` (`departmentID`) ON DELETE CASCADE ON UPDATE NO ACTION',
            'ALTER TABLE `tickets` ADD CONSTRAINT `fk_tickets_priorityID` FOREIGN KEY (`priorityID`) REFERENCES `ticket_priority` (`priorityID`) ON DELETE CASCADE ON UPDATE NO ACTION',
            'ALTER TABLE `tickets` ADD CONSTRAINT `fk_tickets_statusID` FOREIGN KEY (`statusID`) REFERENCES `ticket_status` (`statusID`) ON DELETE CASCADE ON UPDATE NO ACTION',
            'ALTER TABLE `tickets` ADD CONSTRAINT `fk_tickets_customerID` FOREIGN KEY (`customerID`) REFERENCES `customers` (`customerID`) ON DELETE CASCADE ON UPDATE NO ACTION',
            'ALTER TABLE `tickets` ADD CONSTRAINT `fk_tickets_assignedTo` FOREIGN KEY (`assignedTo`) REFERENCES `agents` (`agentID`) ON DELETE SET NULL ON UPDATE NO ACTION',

            'ALTER TABLE `ticket_attachments` ADD CONSTRAINT `fk_ticket_attachments_commentID` FOREIGN KEY (`commentID`) REFERENCES `ticket_comments` (`commentID`) ON DELETE CASCADE ON UPDATE NO ACTION',

            'ALTER TABLE `ticket_comments` ADD CONSTRAINT `fk_ticket_comments_ticketID` FOREIGN KEY (`ticketID`) REFERENCES `tickets` (`ticketID`) ON DELETE CASCADE ON UPDATE NO ACTION',
        ];
        $this->executeQueries($queries);
    }

    function down(): void
    {
        $queries = [
            'ALTER TABLE `ticket_comments` DROP FOREIGN KEY `fk_ticket_comments_ticketID`;',

            'ALTER TABLE `ticket_attachments` DROP FOREIGN KEY `fk_ticket_attachments_commentID`;',

            'ALTER TABLE `tickets` DROP FOREIGN KEY `fk_tickets_assignedTo`;',
            'ALTER TABLE `tickets` DROP FOREIGN KEY `fk_tickets_customerID`;',
            'ALTER TABLE `tickets` DROP FOREIGN KEY `fk_tickets_statusID`;',
            'ALTER TABLE `tickets` DROP FOREIGN KEY `fk_tickets_priorityID`;',
            'ALTER TABLE `tickets` DROP FOREIGN KEY `fk_tickets_departmentID`;',

            'ALTER TABLE `products_categories` DROP FOREIGN KEY `fk_products_categories_productId`;',
            'ALTER TABLE `products_categories` DROP FOREIGN KEY `fk_products_categories_categoryId`;',

            'ALTER TABLE `accounts_roles` DROP FOREIGN KEY `fk_accounts_roles_userID`;',
            'ALTER TABLE `accounts_roles` DROP FOREIGN KEY `fk_accounts_roles_roleID`;',

            'ALTER TABLE `permissions_roles` DROP FOREIGN KEY `fk_permissions_roles_roleID`;',
            'ALTER TABLE `permissions_roles` DROP FOREIGN KEY `fk_permissions_roles_permissionID`;',

            'ALTER TABLE `customers` DROP FOREIGN KEY `fk_customers_userID`;',

            'ALTER TABLE `agents` DROP FOREIGN KEY `fk_agents_userID`;',

            'DROP TABLE IF EXISTS `ticket_status`;',

            'DROP TABLE IF EXISTS `ticket_priority`;',

            'DROP TABLE IF EXISTS `ticket_comments`;',

            'DROP TABLE IF EXISTS `ticket_attachments`;',

            'DROP TABLE IF EXISTS `tickets`;',

            'DROP TABLE IF EXISTS `products`;',

            'ALTER TABLE `departments` DROP INDEX `departments_departmentName_unique`;',
            'DROP TABLE IF EXISTS `departments`;',

            'ALTER TABLE `categories` DROP INDEX `categories_slug_unique`;',

            'DROP TABLE IF EXISTS `products_categories`;',

            'DROP TABLE IF EXISTS `categories`;',

            'ALTER TABLE `accounts` DROP INDEX `accounts_email_unique`;',
            'DROP TABLE IF EXISTS `accounts`;',

            'ALTER TABLE `roles` DROP INDEX `roles_slug_unique`;',

            'DROP TABLE IF EXISTS `accounts_roles`;',

            'DROP TABLE IF EXISTS `roles`;',

            'ALTER TABLE `permissions` DROP INDEX `permissions_slug_unique`;',

            'DROP TABLE IF EXISTS `permissions_roles`;',

            'DROP TABLE IF EXISTS `permissions`;',

            'DROP TABLE IF EXISTS `customers`;',

            'DROP TABLE IF EXISTS `agents`;',
        ];
        $this->executeQueries($queries);
    }
};