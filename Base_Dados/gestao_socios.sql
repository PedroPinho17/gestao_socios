-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 23-Jun-2026 às 03:12
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gestao_socios`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `attribute_changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attribute_changes`)),
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `subject_id`, `event`, `causer_type`, `causer_id`, `attribute_changes`, `properties`, `created_at`, `updated_at`) VALUES
(1, 'default', 'updated', 'App\\Models\\QuotaPlan', 4, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"nome\":\"Anual - Guisande\"},\"old\":{\"nome\":\"sdad\"}}', '[]', '2026-06-22 23:32:47', '2026-06-22 23:32:47');

-- --------------------------------------------------------

--
-- Estrutura da tabela `app_settings`
--

CREATE TABLE `app_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chave` varchar(255) NOT NULL,
  `valor` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `app_settings`
--

INSERT INTO `app_settings` (`id`, `chave`, `valor`, `created_at`, `updated_at`) VALUES
(1, 'mfa_obrigatorio', '0', '2026-06-22 22:22:45', '2026-06-22 22:22:45'),
(2, 'dias_alerta_quota', '7', '2026-06-22 22:22:45', '2026-06-22 22:22:45');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `club_settings`
--

CREATE TABLE `club_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nome_clube` varchar(255) NOT NULL DEFAULT 'O meu clube',
  `logo_path` varchar(255) DEFAULT NULL,
  `card_gradient_from` varchar(7) NOT NULL DEFAULT '#0f766e',
  `card_gradient_to` varchar(7) NOT NULL DEFAULT '#0f172a',
  `card_accent_color` varchar(7) NOT NULL DEFAULT '#d1fae5',
  `card_titulo` varchar(255) NOT NULL DEFAULT 'Sócio',
  `card_campo_extra_label` varchar(255) NOT NULL DEFAULT 'Cargo',
  `show_proximo_vencimento` tinyint(1) NOT NULL DEFAULT 1,
  `show_cargo` tinyint(1) NOT NULL DEFAULT 1,
  `show_email` tinyint(1) NOT NULL DEFAULT 0,
  `show_telefone` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `club_settings`
--

INSERT INTO `club_settings` (`id`, `nome_clube`, `logo_path`, `card_gradient_from`, `card_gradient_to`, `card_accent_color`, `card_titulo`, `card_campo_extra_label`, `show_proximo_vencimento`, `show_cargo`, `show_email`, `show_telefone`, `created_at`, `updated_at`) VALUES
(1, 'CRC VALE', NULL, '#0f766e', '#e4e6eb', '#d1fae5', 'Sócio', 'Cargo', 1, 1, 1, 1, '2026-06-10 21:03:17', '2026-06-10 22:18:46');

-- --------------------------------------------------------

--
-- Estrutura da tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `members`
--

CREATE TABLE `members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `numero` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  `data_adesao` date NOT NULL,
  `quota_plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `foto_path` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `notas` text DEFAULT NULL,
  `cargo_cartao` varchar(255) DEFAULT NULL,
  `validade_manual` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `members`
--

INSERT INTO `members` (`id`, `numero`, `nome`, `email`, `telefone`, `data_adesao`, `quota_plan_id`, `foto_path`, `ativo`, `notas`, `cargo_cartao`, `validade_manual`, `created_at`, `updated_at`) VALUES
(1, '1', 'Pedro Marcelo Santos Pinho', 'pedro0409romariz@gmail.com', '913646563', '2026-06-10', 2, NULL, 1, NULL, 'Cargoo', NULL, '2026-06-10 22:19:20', '2026-06-10 22:19:20');

-- --------------------------------------------------------

--
-- Estrutura da tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_27_000001_create_gestao_socios_tables', 1),
(5, '2026_05_27_000002_add_security_fields_to_users_table', 2),
(6, '2026_06_22_010156_create_activity_log_table', 2),
(7, '2026_06_22_000003_create_permissoes_table', 3),
(8, '2026_06_23_000004_create_app_settings_table', 4);

-- --------------------------------------------------------

--
-- Estrutura da tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `data` date NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `referencia` varchar(255) NOT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `permissoes`
--

CREATE TABLE `permissoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permissao` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `permissoes`
--

INSERT INTO `permissoes` (`id`, `permissao`, `created_at`, `updated_at`) VALUES
(1, 'Imperador', '2026-06-22 22:05:34', '2026-06-22 22:05:34'),
(2, 'Administrador', '2026-06-22 22:05:34', '2026-06-22 22:05:34'),
(3, 'Tesoureiro', '2026-06-22 22:05:34', '2026-06-22 22:05:34');

-- --------------------------------------------------------

--
-- Estrutura da tabela `quota_plans`
--

CREATE TABLE `quota_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `periodicidade` varchar(20) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `tipo_vencimento` varchar(20) NOT NULL DEFAULT 'aniversario',
  `dia_vencimento_mes` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `quota_plans`
--

INSERT INTO `quota_plans` (`id`, `nome`, `periodicidade`, `valor`, `tipo_vencimento`, `dia_vencimento_mes`, `created_at`, `updated_at`) VALUES
(1, 'Quota social — mensal', 'mensal', 15.00, 'aniversario', 1, '2026-06-10 21:03:17', '2026-06-10 21:03:17'),
(2, 'Quota social — anual', 'anual', 180.00, 'aniversario', 1, '2026-06-10 21:35:35', '2026-06-10 21:35:35'),
(3, 'Mensal - Guisande', 'mensal', 1.00, 'aniversario', 1, '2026-06-22 00:22:35', '2026-06-22 00:22:35'),
(4, 'Anual - Guisande', 'mensal', 12.00, 'aniversario', 1, '2026-06-22 00:23:11', '2026-06-22 23:32:47');

-- --------------------------------------------------------

--
-- Estrutura da tabela `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('5Q7ZQc005hHgGrSq8XiBveaozCupkqF2i4GUifOK', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'eyJfdG9rZW4iOiJkN2FPVjVhaE1lMktMa01hTFZlTEVKOEMwczJONUhOcnZmT2lIa3pLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pbiIsInJvdXRlIjoiZmlsYW1lbnQuYWRtaW4ucGFnZXMuZGFzaGJvYXJkIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwidXJsIjpbXSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInBhc3N3b3JkX2hhc2hfd2ViIjoiZDVjOTNhNzNhNjE1YmI5Y2Q5MDU0NGI0ZGRkNTkxYjQ4MTZiOGViMGU0ZDE1ZDUzZjkyM2FhOTI4MjRkMTA1OCJ9', 1782091290),
('lOvNpByz9FuELDPO5zQQ3ojElbMXw7dHPjGnZ8er', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.7.27 Chrome/142.0.7444.265 Electron/39.8.1 Safari/537.36', 'eyJfdG9rZW4iOiJRQ203WjlxSkFWZnNUUFZySEJwRU9HcTJDZVo4bFl5elh1NHBBSDhtIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvY2hhbmdlLXJlcXVpcmVkLXBhc3N3b3JkIiwicm91dGUiOiJmaWxhbWVudC5hZG1pbi5wYWdlcy5jaGFuZ2UtcmVxdWlyZWQtcGFzc3dvcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF93ZWIiOiJkNWM5M2E3M2E2MTViYjljZDkwNTQ0YjRkZGQ1OTFiNDgxNmI4ZWIwZTRkMTVkNTNmOTIzYWE5MjgyNGQxMDU4In0=', 1782090441),
('Ni8K3LlSjnpJ6gc5BVSF8y8DPlqaF7WobYRBmmRi', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJWMjJ3RndaOXZIcHE0dkRoZVdLM0xIVHBuc1FIYXBMM0ZpOHJrR0hXIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvYWN0aXZpdHktbG9ncyIsInJvdXRlIjoiZmlsYW1lbnQuYWRtaW4ucmVzb3VyY2VzLmFjdGl2aXR5LWxvZ3MuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF93ZWIiOiJkNWM5M2E3M2E2MTViYjljZDkwNTQ0YjRkZGQ1OTFiNDgxNmI4ZWIwZTRkMTVkNTNmOTIzYWE5MjgyNGQxMDU4IiwidGFibGVzIjp7ImJjOWZlZmJhMTc5MGFiMmQxMjY0OGI4ZTM5YmVmMzJiX2NvbHVtbnMiOlt7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoibnVtZXJvIiwibGFiZWwiOiJOLlx1MDBiYSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJub21lIiwibGFiZWwiOiJOb21lIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InF1b3RhUGxhbi5ub21lIiwibGFiZWwiOiJQbGFubyIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJxdW90YV9zdGF0dXMiLCJsYWJlbCI6IlBhZ2FtZW50byIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJhdGl2byIsImxhYmVsIjoiQXRpdm8iLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfV0sIjJiNDdmYWU0NzBkN2JjYTQ0MTk5ZTA5YWQzYTQ4MzI0X2NvbHVtbnMiOlt7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoibm9tZSIsImxhYmVsIjoiTm9tZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJwZXJpb2RpY2lkYWRlIiwibGFiZWwiOiJQZXJpb2RpY2lkYWRlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InZhbG9yIiwibGFiZWwiOiJWYWxvciIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJ0aXBvX3ZlbmNpbWVudG8iLCJsYWJlbCI6IlZlbmNpbWVudG8iLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfV0sImZjOGM2YzNkNjA4OTIxMGRlOGI1OGNlMjRjODI0NDBjX2NvbHVtbnMiOlt7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiY3JlYXRlZF9hdCIsImxhYmVsIjoiRGF0YSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjYXVzZXIubmFtZSIsImxhYmVsIjoiVXRpbGl6YWRvciIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJkZXNjcmlwdGlvbiIsImxhYmVsIjoiQWNcdTAwZTdcdTAwZTNvIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InN1YmplY3RfdHlwZSIsImxhYmVsIjoiTW9kZWxvIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InByb3BlcnRpZXMuYXR0cmlidXRlcyIsImxhYmVsIjoiQWx0ZXJhXHUwMGU3XHUwMGY1ZXMiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfV19LCJmaWxhbWVudCI6W119', 1782091431),
('rZJrxLFUygBjWPNRqFLBjOioEtH3HkWeJWbQUfS0', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJ1cmwiOltdLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluIiwicm91dGUiOiJmaWxhbWVudC5hZG1pbi5wYWdlcy5kYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfdG9rZW4iOiI2bzVsOEdzMzFIUFlCZ3lqdmtjRWFWUnE0bU5TWTNQVWlHTzlaeWEwIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjMsInBhc3N3b3JkX2hhc2hfd2ViIjoiNmYxZjM4NWM4MDUyYTY0MTM5NTU5ZDYzYzI4YjBiYjA1Zjk1NmEzM2FjYmUyNDA5ZjQwYjNkMjI4NGE1MmNjMiJ9', 1782172488);

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `permissao_id` bigint(20) UNSIGNED DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `app_authentication_secret` text DEFAULT NULL,
  `app_authentication_recovery_codes` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `permissao_id`, `must_change_password`, `password_changed_at`, `app_authentication_secret`, `app_authentication_recovery_codes`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'admin@clube.pt', NULL, '$2y$12$Mz8FhTt/OAkvtn7OOpLkreXxWfxGqE0diMictFmxMMntH1Ns2zBQi', 2, 1, NULL, NULL, NULL, 'sIvzaYRqb8LL9JRT89uttr78QXljTPHUaNpcmYLdsfSwZjpD1wzyljnvAxA6', '2026-06-10 21:03:17', '2026-06-22 22:42:35'),
(2, 'Imperador', 'imperador@dev.local', NULL, '$2y$12$/AHz1YL49WNM/wT9CJtPxejOssNOkNyUYAivE2TQ4uf9.iuz5huPa', 1, 1, NULL, NULL, NULL, NULL, '2026-06-22 22:42:35', '2026-06-22 22:42:35'),
(3, 'Pedro Pinho', 'pedropinho364@gmail.com', NULL, '$2y$04$xMMsZn10HxqU8edADtmR/eu8kGtI3QkgnY7F29xuibjikwizlo.Xy', 1, 0, '2026-06-22 22:48:49', NULL, NULL, 'HKqDIvGZDub6kZTm0IKYf9uZjUHkvhYBi45YLLJxliHYerLW9kO47yt5Nb5X', '2026-06-22 22:48:49', '2026-06-22 23:06:04'),
(4, 'Diogo Resende', 'dresende882@gmail.com', NULL, '$2y$04$RfFURju.Dvk5NXtWL1tFnug12DC96U0oMjmwmaOs9owvB9a7FU9eS', 1, 1, NULL, NULL, NULL, NULL, '2026-06-22 23:34:45', '2026-06-22 23:34:45');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Índices para tabela `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_settings_chave_unique` (`chave`);

--
-- Índices para tabela `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Índices para tabela `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Índices para tabela `club_settings`
--
ALTER TABLE `club_settings`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Índices para tabela `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Índices para tabela `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `members_numero_unique` (`numero`),
  ADD KEY `members_quota_plan_id_foreign` (`quota_plan_id`);

--
-- Índices para tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices para tabela `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_member_id_foreign` (`member_id`);

--
-- Índices para tabela `permissoes`
--
ALTER TABLE `permissoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissoes_permissao_unique` (`permissao`);

--
-- Índices para tabela `quota_plans`
--
ALTER TABLE `quota_plans`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_permissao_id_foreign` (`permissao_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `app_settings`
--
ALTER TABLE `app_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `club_settings`
--
ALTER TABLE `club_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `members`
--
ALTER TABLE `members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `permissoes`
--
ALTER TABLE `permissoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `quota_plans`
--
ALTER TABLE `quota_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_quota_plan_id_foreign` FOREIGN KEY (`quota_plan_id`) REFERENCES `quota_plans` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_permissao_id_foreign` FOREIGN KEY (`permissao_id`) REFERENCES `permissoes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
