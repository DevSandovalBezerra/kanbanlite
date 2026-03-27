-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 23/03/2026 às 20:55
-- Versão do servidor: 5.7.23-23
-- Versão do PHP: 8.1.34

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
-- Estrutura para tabela `admin_audit_log`
--

CREATE TABLE `admin_audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `admin_audit_log`
--

INSERT INTO `admin_audit_log` (`id`, `actor_id`, `action`, `target_user_id`, `meta`, `created_at`) VALUES
(1, 1, 'user_updated', 2, '{\"after\": {\"name\": \"Auditado\"}, \"before\": {\"name\": \"Regular User\", \"email\": \"regular@test.com\", \"is_admin\": 0}}', '2026-03-21 14:14:26'),
(2, 1, 'user_created', 3, '{\"name\": \"Eudimaci\", \"email\": \"eudimaci08@yahoo.com.br\", \"is_admin\": true}', '2026-03-21 18:32:15'),
(3, 1, 'user_created', 4, '{\"name\": \"Marcio\", \"email\": \"nandesinfo@gmail.com\", \"is_admin\": true}', '2026-03-21 21:10:49'),
(4, 4, 'user_updated', 4, '{\"after\": {\"name\": \"Marcio Fernandes\", \"email\": \"nandesinfo@gmail.com\", \"is_admin\": true}, \"before\": {\"name\": \"Marcio Fernandes\", \"email\": \"nandesinfo@gmail.com\", \"is_admin\": 1}}', '2026-03-21 21:55:48'),
(5, 4, 'user_updated', 4, '{\"after\": {\"name\": \"Marcio Fernandes\", \"email\": \"nandesinfo@gmail.com\", \"is_admin\": false}, \"before\": {\"name\": \"Marcio Fernandes\", \"email\": \"nandesinfo@gmail.com\", \"is_admin\": 1}}', '2026-03-21 22:01:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `boards`
--

CREATE TABLE `boards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `boards`
--

INSERT INTO `boards` (`id`, `project_id`, `name`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, 'MovAmazon', 3, '2026-03-21 18:51:17', '2026-03-21 18:51:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `columns`
--

CREATE TABLE `columns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `board_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `columns`
--

INSERT INTO `columns` (`id`, `board_id`, `name`, `position`, `created_at`, `updated_at`) VALUES
(1, 1, 'A Fazer', 1, '2026-03-21 18:51:17', '2026-03-21 18:51:17'),
(2, 1, 'Em Progresso', 2, '2026-03-21 18:51:17', '2026-03-21 18:51:17'),
(3, 1, 'Revisão', 3, '2026-03-21 18:51:17', '2026-03-21 18:51:17'),
(4, 1, 'Concluído', 4, '2026-03-21 18:51:17', '2026-03-21 18:51:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `companies`
--

INSERT INTO `companies` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Test Corp', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `labels`
--

CREATE TABLE `labels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6200EE',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `projects`
--

INSERT INTO `projects` (`id`, `company_id`, `name`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Test Project', 'Desc', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 1, 'MovAmazon', 'Uma plataforma de eventos de Corrida', 3, '2026-03-21 18:38:05', '2026-03-21 18:38:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_members`
--

CREATE TABLE `project_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_in_project` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invited_by` bigint(20) UNSIGNED DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `user_id`, `role_in_project`, `invited_by`, `accepted_at`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'owner', 3, '2026-03-21 18:38:05', '2026-03-21 18:38:05', '2026-03-21 18:38:05'),
(2, 2, 4, 'editor', 3, '2026-03-21 23:03:06', '2026-03-21 23:03:06', '2026-03-21 23:03:06'),
(3, 2, 1, 'editor', 3, '2026-03-21 23:03:15', '2026-03-21 23:03:15', '2026-03-21 23:03:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `column_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `priority` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `story_points` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Estimativa Fibonacci: 1, 2, 3, 5, 8, 13, 21',
  `deadline` datetime DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tasks`
--

INSERT INTO `tasks` (`id`, `column_id`, `title`, `description`, `assigned_to`, `priority`, `story_points`, `deadline`, `status`, `position`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Criar os usuários para acessar o projeto', '<p><br></p>', 3, 'medium', NULL, '2026-03-21 00:00:00', 'active', 1, 3, '2026-03-21 18:52:09', '2026-03-21 21:56:57'),
(2, 1, 'O treino gerado não está respeitando a data prova', '<p>Inclua uma visão geral das metas e do contexto do projeto</p>', 1, 'medium', NULL, '2026-03-23 16:17:14', 'active', 1, 4, '2026-03-23 16:17:14', '2026-03-23 16:54:43'),
(3, 1, 'Criar uma função para outra pessoa fazer inscrição por outrem', '<p>Segue um modelo para criar essa função.</p>', 1, 'high', NULL, '2026-03-23 16:45:01', 'active', 1, 4, '2026-03-23 16:45:01', '2026-03-23 16:55:30'),
(4, 1, 'No processo de cadastro de uma nova prova não está salvando a seção das fases anteriores', '<p>Segue a imagem no último estágio de criação de uma nova prova.</p>', 1, 'high', NULL, '2026-03-23 16:48:30', 'active', 1, 4, '2026-03-23 16:48:30', '2026-03-23 16:49:40'),
(5, 2, 'Interface de pagamento', '<p>Sandoval está terminando os testes e parametrizações. </p>', 1, 'high', NULL, '2026-03-23 16:56:56', 'active', 1, 4, '2026-03-23 16:56:56', '2026-03-23 17:01:35'),
(6, 2, 'Não está autorizando o cadastro de novas provas de corrida', '<p>Sobre o projeto</p>', 1, 'high', NULL, '2026-03-23 16:59:23', 'active', 1, 4, '2026-03-23 16:59:23', '2026-03-23 16:59:50'),
(7, 2, 'Atualizar o Termo de Confidencialidade e reenviar.', '<p><br></p>', 3, 'medium', NULL, '2026-03-23 17:03:38', 'active', 1, 4, '2026-03-23 17:03:38', '2026-03-23 17:03:38'),
(8, 2, 'Verificar se existe no módulo financeiro um campo de repasse de recursos para o Organizador', '<p>Criar um campo no Dashboard do Organizador para que ele possa solicitar o repasse e acompanhe esse controle pelo Dashboard do mesmo.</p>', 1, 'high', NULL, '2026-03-23 17:05:52', 'active', 1, 4, '2026-03-23 17:05:52', '2026-03-23 17:07:11'),
(9, 2, 'Resolver a pendência do Mercado Pago', '<p>A impossibilidade dessa ação está dificultando a integração de pagamentos no site  MovAmazon </p>', 3, 'high', NULL, '2026-03-23 17:08:02', 'active', 1, 4, '2026-03-23 17:08:02', '2026-03-23 17:09:17'),
(10, 3, 'Restrição de acesso por login', '<p>Não deveria ver as provas de outra pessoas</p>', 1, 'high', NULL, '2026-03-23 17:11:46', 'active', 1, 4, '2026-03-23 17:11:46', '2026-03-23 17:11:46'),
(11, 3, 'Retirar uma data do processo de registro de uma nova prova', '<p>Inclua uma visão geral das metas e do contexto do projeto</p>', 1, 'low', NULL, '2026-03-23 17:13:50', 'active', 1, 4, '2026-03-23 17:13:50', '2026-03-23 17:13:50'),
(12, 3, 'Não está aparecendo legível o campo recuperar senha', '<p>Conforme a mensagem abaixo:</p>', 1, 'high', NULL, '2026-03-23 17:16:10', 'active', 1, 4, '2026-03-23 17:16:10', '2026-03-23 17:16:10'),
(13, 3, 'Produtos extras devem ser opcionais', '<p>Conforme o quadro abaixo:</p>', 1, 'low', NULL, '2026-03-23 17:18:25', 'active', 1, 4, '2026-03-23 17:18:25', '2026-03-23 17:18:25'),
(14, 3, 'Falhas nas datas de lançamento na Plataforma', '<p>Falhas nas datas de lançamento na Plataforma</p><p>Conforme o quadro abaixo:</p>', 1, 'high', NULL, '2026-03-23 17:21:06', 'active', 1, 4, '2026-03-23 17:21:06', '2026-03-23 17:21:46'),
(15, 3, 'Criar listagem de Organizador e assessoria para visão do Administrador', '<p><br></p>', 1, 'medium', NULL, '2026-03-23 17:23:54', 'active', 1, 4, '2026-03-23 17:23:54', '2026-03-23 17:23:54'),
(16, 3, 'A cor da imagem do cashback do atleta precisa ser mudada', '<p><br></p>', 1, 'medium', NULL, '2026-03-23 17:25:16', 'active', 1, 4, '2026-03-23 17:25:16', '2026-03-23 17:25:16');
INSERT INTO `tasks` (`id`, `column_id`, `title`, `description`, `assigned_to`, `priority`, `story_points`, `deadline`, `status`, `position`, `created_by`, `created_at`, `updated_at`) VALUES
(17, 3, 'Taxas cobradas pela Plataforma', '<p>Corrigir o quadro abaixo:<img src=\"data:image/png;base64,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', 1, 'medium', NULL, '2026-03-23 17:30:14', 'active', 1, 4, '2026-03-23 17:30:14', '2026-03-23 17:30:14');
INSERT INTO `tasks` (`id`, `column_id`, `title`, `description`, `assigned_to`, `priority`, `story_points`, `deadline`, `status`, `position`, `created_by`, `created_at`, `updated_at`) VALUES
(18, 3, 'No processo de cadastro de uma  nova prova, no resumo deverá conter a data da prova.', '<p>Segue anexo</p>', 1, 'high', NULL, '2026-03-23 17:32:46', 'active', 9, 4, '2026-03-23 17:32:46', '2026-03-23 17:37:37'),
(19, 3, 'Não está aparecendo todos os estados', '<p><br></p>', 1, 'medium', NULL, '2026-03-23 17:36:13', 'active', 1, 4, '2026-03-23 17:36:13', '2026-03-23 17:36:13'),
(20, 3, 'Contratação de uma empresa de contabilidade', '<p>Amanhã, dia 27/02/2026, às 10h00 (horário de Manaus), tenho uma reunião com a empresa que irá acompanhar a EBL eventos esportivos </p>', 3, 'medium', NULL, '2026-03-23 17:38:36', 'active', 1, 4, '2026-03-23 17:38:36', '2026-03-23 17:39:25'),
(21, 3, 'Retirar a opção de pagamento com boleto', '<p>Para se cadastrar na Plataforma peço para retirar os dados de endereço, manter somente o CPF, e-mail, idade e telefone. </p>', 1, 'high', NULL, '2026-03-23 17:41:15', 'active', 1, 4, '2026-03-23 17:41:15', '2026-03-23 17:41:15'),
(22, 3, 'Criar o campo para incluir novo Kit', '<p>Incluir ou deixar visível o campo criar um novo Kit.</p><p>Fazer novos testes</p>', 1, 'medium', NULL, '2026-03-23 17:44:28', 'active', 1, 4, '2026-03-23 17:44:28', '2026-03-23 17:44:28');

-- --------------------------------------------------------

--
-- Estrutura para tabela `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filepath` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `task_attachments`
--

INSERT INTO `task_attachments` (`id`, `task_id`, `uploaded_by`, `filename`, `filepath`, `mime_type`, `size_bytes`, `created_at`) VALUES
(4, 2, 4, 'image.jpeg', 'tasks/2/0ce6e8df-2dd3-47fa-aaa3-e7a7a2b4e5c3.jpeg', 'image/jpeg', 92360, '2026-03-23 16:39:05'),
(6, 3, 4, 'criar funcao.png', 'tasks/3/8fb4365a-5ca3-4e4d-9b6e-5595a88f3a95.png', 'image/png', 201423, '2026-03-23 16:46:18'),
(7, 4, 4, 'no processo de cadastro.png', 'tasks/4/053b5ffe-8ea7-486a-b502-e7a07f050914.png', 'image/png', 191836, '2026-03-23 16:51:06'),
(8, 6, 4, 'cad novas provas.png', 'tasks/6/f002b2e1-f8bc-464e-8522-5309bef62548.png', 'image/png', 228003, '2026-03-23 17:00:32'),
(9, 10, 4, 'acesso login.png', 'tasks/10/4cecba23-ce27-417a-a867-9f32f228de46.png', 'image/png', 373762, '2026-03-23 17:12:25'),
(10, 11, 4, 'retirar data.png', 'tasks/11/bff08907-b7d4-4ad8-be30-09054f1353fa.png', 'image/png', 227895, '2026-03-23 17:14:32'),
(11, 12, 4, 'recuperar senha.png', 'tasks/12/6c20785e-71a1-407c-92ec-1bf3336da7f0.png', 'image/png', 41354, '2026-03-23 17:17:04'),
(12, 13, 4, 'produto extras.png', 'tasks/13/3c469f97-d61c-4405-bcb2-bfa7d46a3f7d.png', 'image/png', 221032, '2026-03-23 17:18:57'),
(13, 14, 4, 'falhas datas.png', 'tasks/14/64d2be6a-ef21-4da7-9d89-6930a23bb748.png', 'image/png', 224221, '2026-03-23 17:22:33'),
(14, 16, 4, 'cor img cashback.png', 'tasks/16/72f150c1-9229-432f-9f2b-2ecef0ce3b36.png', 'image/png', 116848, '2026-03-23 17:27:04'),
(15, 17, 4, 'taxas cobradas.png', 'tasks/17/18cf1bc1-3e24-4237-ac9f-4112ac61af8f.png', 'image/png', 286298, '2026-03-23 17:31:48'),
(16, 18, 4, 'cad nova prova resumo.png', 'tasks/18/2681806f-2473-44e5-a07f-31af5a21adc1.png', 'image/png', 123509, '2026-03-23 17:34:47'),
(17, 19, 4, 'estados.png', 'tasks/19/b17cba3a-1490-46eb-82c8-da6ddeebcc44.png', 'image/png', 177866, '2026-03-23 17:37:19'),
(18, 22, 4, 'inc novo kit.png', 'tasks/22/ebc046fe-19c3-419b-a274-3b1486b3eee7.png', 'image/png', 185440, '2026-03-23 17:45:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `task_checklists`
--

CREATE TABLE `task_checklists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Checklist',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `task_checklist_items`
--

CREATE TABLE `task_checklist_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `checklist_id` bigint(20) UNSIGNED NOT NULL,
  `body` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT '0',
  `position` smallint(6) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `task_comments`
--

CREATE TABLE `task_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `task_dependencies`
--

CREATE TABLE `task_dependencies` (
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `depends_on_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `task_history`
--

CREATE TABLE `task_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `task_history`
--

INSERT INTO `task_history` (`id`, `task_id`, `action`, `old_value`, `new_value`, `user_id`, `created_at`) VALUES
(1, 1, 'moved', '{\"column_id\":1,\"position\":1}', '{\"column_id\":2,\"position\":1}', 3, '2026-03-21 19:06:10'),
(2, 1, 'moved', '{\"column_id\":2,\"position\":1}', '{\"column_id\":3,\"position\":1}', 3, '2026-03-21 19:06:11'),
(3, 1, 'moved', '{\"column_id\":3,\"position\":1}', '{\"column_id\":2,\"position\":1}', 3, '2026-03-21 19:06:17'),
(4, 1, 'moved', '{\"column_id\":2,\"position\":1}', '{\"column_id\":1,\"position\":1}', 3, '2026-03-21 19:06:17'),
(5, 1, 'moved', '{\"column_id\":1,\"position\":1}', '{\"column_id\":2,\"position\":1}', 3, '2026-03-21 19:11:42'),
(6, 1, 'moved', '{\"column_id\":2,\"position\":1}', '{\"column_id\":1,\"position\":1}', 3, '2026-03-21 20:30:01'),
(7, 1, 'moved', '{\"column_id\":1,\"position\":1}', '{\"column_id\":1,\"position\":1}', 3, '2026-03-21 20:30:15'),
(8, 1, 'moved', '{\"column_id\":1,\"position\":1}', '{\"column_id\":2,\"position\":1}', 3, '2026-03-21 20:31:21'),
(9, 1, 'moved', '{\"column_id\":2,\"position\":1}', '{\"column_id\":3,\"position\":1}', 3, '2026-03-21 20:31:22'),
(10, 1, 'moved', '{\"column_id\":3,\"position\":1}', '{\"column_id\":1,\"position\":1}', 3, '2026-03-21 20:31:24'),
(11, 18, 'moved', '{\"column_id\":3,\"position\":1}', '{\"column_id\":3,\"position\":9}', 4, '2026-03-23 17:37:35'),
(12, 18, 'moved', '{\"column_id\":3,\"position\":9}', '{\"column_id\":3,\"position\":9}', 4, '2026-03-23 17:37:37');

-- --------------------------------------------------------

--
-- Estrutura para tabela `task_labels`
--

CREATE TABLE `task_labels` (
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `label_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `company_id`, `name`, `email`, `avatar`, `password`, `status`, `is_admin`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sandoval', 'sandoval@sbsystems.com.br', NULL, '$2y$10$kA15H4DeAermNHPL0MW8JuoDn5tQVvBWAJOACJqTDMrUl1T7RhnPC', 'active', 1, '2026-01-01 00:00:00', '2026-03-21 11:07:43'),
(2, 1, 'Auditado', 'regular@test.com', NULL, '$2y$12$B4bhpOf8Hx.SmItyBd0O9.v8..uqmNf94Cj0.sBJgVeZujy1NXza2', 'active', 0, '2026-01-01 00:00:00', '2026-03-21 14:14:26'),
(3, 1, 'Eudimaci', 'eudimaci08@yahoo.com.br', NULL, '$2y$10$kA15H4DeAermNHPL0MW8JuoDn5tQVvBWAJOACJqTDMrUl1T7RhnPC', 'active', 1, '2026-03-21 18:32:15', '2026-03-21 18:32:15'),
(4, 1, 'Marcio Fernandes', 'nandesinfo@gmail.com', NULL, '$2y$10$3ekMQa5WqgvOkUVuNxjypenMK8Dio632WCHcoLr1S0nup8xd4yhEW', 'active', 0, '2026-03-21 21:10:49', '2026-03-21 22:01:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_audit_actor` (`actor_id`);

--
-- Índices de tabela `boards`
--
ALTER TABLE `boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `boards_project_id_idx` (`project_id`),
  ADD KEY `boards_created_by_idx` (`created_by`);

--
-- Índices de tabela `columns`
--
ALTER TABLE `columns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `columns_board_position_unique` (`board_id`,`position`),
  ADD UNIQUE KEY `columns_board_name_unique` (`board_id`,`name`),
  ADD KEY `columns_board_id_idx` (`board_id`);

--
-- Índices de tabela `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_company_id_idx` (`company_id`),
  ADD KEY `events_project_id_idx` (`project_id`),
  ADD KEY `events_created_by_idx` (`created_by`);

--
-- Índices de tabela `labels`
--
ALTER TABLE `labels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_label` (`company_id`,`name`),
  ADD KEY `labels_company_id_idx` (`company_id`);

--
-- Índices de tabela `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_sender_id_idx` (`sender_id`),
  ADD KEY `messages_receiver_id_idx` (`receiver_id`);

--
-- Índices de tabela `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Índices de tabela `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projects_company_id_idx` (`company_id`),
  ADD KEY `projects_created_by_idx` (`created_by`);

--
-- Índices de tabela `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_project_member` (`project_id`,`user_id`),
  ADD KEY `project_members_user_id_idx` (`user_id`),
  ADD KEY `project_members_invited_by_fk` (`invited_by`);

--
-- Índices de tabela `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Índices de tabela `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_idx` (`permission_id`);

--
-- Índices de tabela `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_column_position_idx` (`column_id`,`position`),
  ADD KEY `tasks_assigned_to_idx` (`assigned_to`),
  ADD KEY `tasks_created_by_idx` (`created_by`);

--
-- Índices de tabela `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_attachments_task_id_idx` (`task_id`),
  ADD KEY `task_attachments_user_fk` (`uploaded_by`);

--
-- Índices de tabela `task_checklists`
--
ALTER TABLE `task_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_checklists_task_id_idx` (`task_id`);

--
-- Índices de tabela `task_checklist_items`
--
ALTER TABLE `task_checklist_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_checklist_items_checklist_id_idx` (`checklist_id`);

--
-- Índices de tabela `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_comments_task_id_idx` (`task_id`),
  ADD KEY `task_comments_user_id_idx` (`user_id`);

--
-- Índices de tabela `task_dependencies`
--
ALTER TABLE `task_dependencies`
  ADD PRIMARY KEY (`task_id`,`depends_on_id`),
  ADD KEY `task_dep_blocker_fk` (`depends_on_id`);

--
-- Índices de tabela `task_history`
--
ALTER TABLE `task_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_history_task_id_idx` (`task_id`),
  ADD KEY `task_history_user_id_idx` (`user_id`);

--
-- Índices de tabela `task_labels`
--
ALTER TABLE `task_labels`
  ADD PRIMARY KEY (`task_id`,`label_id`),
  ADD KEY `task_labels_label_fk` (`label_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_company_email_unique` (`company_id`,`email`),
  ADD KEY `users_company_id_idx` (`company_id`);

--
-- Índices de tabela `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `user_roles_role_id_idx` (`role_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `boards`
--
ALTER TABLE `boards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `columns`
--
ALTER TABLE `columns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `labels`
--
ALTER TABLE `labels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `project_members`
--
ALTER TABLE `project_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `task_checklists`
--
ALTER TABLE `task_checklists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `task_checklist_items`
--
ALTER TABLE `task_checklist_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `task_history`
--
ALTER TABLE `task_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  ADD CONSTRAINT `fk_audit_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`);

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
-- Restrições para tabelas `labels`
--
ALTER TABLE `labels`
  ADD CONSTRAINT `labels_company_id_fk` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `pm_project_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `pm_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `project_members_invited_by_fk` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`);

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
-- Restrições para tabelas `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `task_attachments_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_attachments_user_fk` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `task_checklists`
--
ALTER TABLE `task_checklists`
  ADD CONSTRAINT `task_checklists_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `task_checklist_items`
--
ALTER TABLE `task_checklist_items`
  ADD CONSTRAINT `task_checklist_items_checklist_fk` FOREIGN KEY (`checklist_id`) REFERENCES `task_checklists` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_task_id_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_comments_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `task_dependencies`
--
ALTER TABLE `task_dependencies`
  ADD CONSTRAINT `task_dep_blocker_fk` FOREIGN KEY (`depends_on_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_dep_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `task_history`
--
ALTER TABLE `task_history`
  ADD CONSTRAINT `task_history_task_id_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_history_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `task_labels`
--
ALTER TABLE `task_labels`
  ADD CONSTRAINT `task_labels_label_fk` FOREIGN KEY (`label_id`) REFERENCES `labels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_labels_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

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
