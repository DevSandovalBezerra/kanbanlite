-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 20/03/2026 às 02:02
-- Versão do servidor: 8.4.7
-- Versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `brunor90_kanban`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `boards`
--

DROP TABLE IF EXISTS `boards`;
CREATE TABLE IF NOT EXISTS `boards` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `boards_project_id_idx` (`project_id`),
  KEY `boards_created_by_idx` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `boards`
--

INSERT INTO `boards` (`id`, `project_id`, `name`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Quadro de Desenvolvimento', 1, '2026-03-19 00:01:39', '2026-03-19 00:01:39'),
(2, 1, 'Design & UX', 1, '2026-03-19 00:01:39', '2026-03-19 00:01:39'),
(3, 2, 'Operações Mova', 2, '2026-03-19 01:11:45', '2026-03-19 01:11:45');

-- --------------------------------------------------------

--
-- Estrutura para tabela `columns`
--

DROP TABLE IF EXISTS `columns`;
CREATE TABLE IF NOT EXISTS `columns` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `board_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `columns_board_position_unique` (`board_id`,`position`),
  UNIQUE KEY `columns_board_name_unique` (`board_id`,`name`),
  KEY `columns_board_id_idx` (`board_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `columns`
--

INSERT INTO `columns` (`id`, `board_id`, `name`, `position`, `created_at`, `updated_at`) VALUES
(1, 1, 'A Fazer', 1, '2026-03-19 00:01:39', '2026-03-19 00:01:39'),
(2, 1, 'Em Progresso', 2, '2026-03-19 00:01:39', '2026-03-19 00:01:39'),
(3, 1, 'Revisão', 3, '2026-03-19 00:01:39', '2026-03-19 00:01:39'),
(4, 1, 'Concluído', 4, '2026-03-19 00:01:39', '2026-03-19 00:01:39'),
(5, 3, 'A Fazer', 1, '2026-03-19 01:11:45', '2026-03-19 01:11:45'),
(6, 3, 'Em Progresso', 2, '2026-03-19 01:11:45', '2026-03-19 01:11:45'),
(7, 3, 'Revisão', 3, '2026-03-19 01:11:45', '2026-03-19 01:11:45'),
(8, 3, 'Concluído', 4, '2026-03-19 01:11:45', '2026-03-19 01:11:45');

-- --------------------------------------------------------

--
-- Estrutura para tabela `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `companies`
--

INSERT INTO `companies` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Software Solutions', 'active', '2026-03-19 00:01:39', '2026-03-19 00:01:39'),
(2, 'Mova Amazon', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `events_company_id_idx` (`company_id`),
  KEY `events_project_id_idx` (`project_id`),
  KEY `events_created_by_idx` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id` bigint UNSIGNED NOT NULL,
  `receiver_id` bigint UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_sender_id_idx` (`sender_id`),
  KEY `messages_receiver_id_idx` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projects_company_id_idx` (`company_id`),
  KEY `projects_created_by_idx` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `projects`
--

INSERT INTO `projects` (`id`, `company_id`, `name`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Projeto Principal', 'Desenvolvimento do Sistema Kanban', 1, '2026-03-19 00:01:39', '2026-03-19 00:01:39'),
(2, 2, 'Mova Amazon - Projeto Alpha', 'Primeiro projeto para testes de fluxo', 2, '2026-03-19 01:11:45', '2026-03-19 01:11:45');

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_members`
--

DROP TABLE IF EXISTS `project_members`;
CREATE TABLE IF NOT EXISTS `project_members` (
  `project_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `role_in_project` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`project_id`,`user_id`),
  KEY `project_members_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_permissions_permission_id_idx` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tasks`
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `column_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `priority` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deadline` datetime DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_column_position_idx` (`column_id`,`position`),
  KEY `tasks_assigned_to_idx` (`assigned_to`),
  KEY `tasks_created_by_idx` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tasks`
--

INSERT INTO `tasks` (`id`, `column_id`, `title`, `description`, `assigned_to`, `priority`, `deadline`, `status`, `position`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 3, 'Definir Requisitos', 'Mapear todas as funcionalidades do MVP', NULL, 'high', NULL, 'todo', 2, 1, '2026-03-19 00:01:39', '2026-03-19 05:34:25'),
(2, 3, 'Arquitetura de Dados', 'Modelar o banco de dados MySQL', NULL, 'medium', NULL, 'todo', 2, 1, '2026-03-19 00:01:39', '2026-03-19 05:34:21'),
(3, 1, 'Implementar Auth', 'Sistema de login e registro', NULL, 'high', NULL, 'in_progress', 1, 1, '2026-03-19 00:01:39', '2026-03-19 05:34:30'),
(4, 5, 'Análise de KPIs Mensais', 'Revisar os números de desempenho do último mês.', NULL, 'high', NULL, 'todo', 1, 2, '2026-03-19 01:11:45', '2026-03-19 01:11:45'),
(5, 5, 'Revisão de Infraestrutura', 'Verificar estabilidade dos servidores em Manaus.', NULL, 'medium', NULL, 'todo', 2, 2, '2026-03-19 01:11:45', '2026-03-19 01:11:45'),
(6, 5, 'Planejamento de Marketing', 'Definir próximas campanhas regionais.', NULL, 'low', NULL, 'todo', 3, 2, '2026-03-19 01:11:45', '2026-03-19 01:11:45');

-- --------------------------------------------------------

--
-- Estrutura para tabela `task_comments`
--

DROP TABLE IF EXISTS `task_comments`;
CREATE TABLE IF NOT EXISTS `task_comments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task_comments_task_id_idx` (`task_id`),
  KEY `task_comments_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `task_history`
--

DROP TABLE IF EXISTS `task_history`;
CREATE TABLE IF NOT EXISTS `task_history` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` bigint UNSIGNED NOT NULL,
  `action` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task_history_task_id_idx` (`task_id`),
  KEY `task_history_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_company_email_unique` (`company_id`,`email`),
  KEY `users_company_id_idx` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `company_id`, `name`, `email`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrador', 'admin@kanban.com', '$2y$12$kNIVYZtCOdO30nW4cJDeMehxW7L7CG0P95qusPXaqB6/6EMi8Jfqu', 'active', '2026-03-19 00:01:39', '2026-03-19 00:01:39'),
(2, 2, 'Administrador', 'admin@movamazon.com.br', '$2y$12$CYl1ztfVfQW/7hKNoC5ubePPoMW8rTDnmhtllyqSHaAF0UnYD04ii', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `user_roles_role_id_idx` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `boards`
--
ALTER TABLE `boards`
  ADD CONSTRAINT `boards_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `boards_project_id_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Restrições para tabelas `columns`
--
ALTER TABLE `columns`
  ADD CONSTRAINT `columns_board_id_fk` FOREIGN KEY (`board_id`) REFERENCES `boards` (`id`);

--
-- Restrições para tabelas `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_company_id_fk` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `events_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `events_project_id_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Restrições para tabelas `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_fk` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_sender_id_fk` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_company_id_fk` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `projects_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_project_id_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `project_members_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_fk` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`),
  ADD CONSTRAINT `role_permissions_role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Restrições para tabelas `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_assigned_to_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_column_id_fk` FOREIGN KEY (`column_id`) REFERENCES `columns` (`id`),
  ADD CONSTRAINT `tasks_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_task_id_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_comments_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `task_history`
--
ALTER TABLE `task_history`
  ADD CONSTRAINT `task_history_task_id_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_history_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_company_id_fk` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Restrições para tabelas `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `user_roles_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
