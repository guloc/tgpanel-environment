SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `bot_context`;
CREATE TABLE `bot_context` (
  `id` int NOT NULL,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `chat_id` varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `role` enum('user','assistant') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `text` varchar(4096) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `channel`;
CREATE TABLE `channel` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `uid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `link` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `type` enum('source','target') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `platform` enum('telegram','vk','wordpress') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'telegram',
  `subs` int UNSIGNED NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `config` text COLLATE utf8mb4_bin,
  `stats` text COLLATE utf8mb4_bin,
  `last_check` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_post_id` varchar(128) COLLATE utf8mb4_bin DEFAULT NULL,
  `access_token` varchar(512) COLLATE utf8mb4_bin DEFAULT NULL,
  `access_name` varchar(256) COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int NOT NULL,
  `cfg_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `cfg_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;

INSERT INTO `config` (`id`, `cfg_name`, `cfg_value`) VALUES
(1, 'project_name', 'TG Panel'),
(2, 'tg_auth', ''),
(3, 'telegram_bot_token', ''),
(4, 'bot_users', '[]'),
(5, 'bot_paraphrase_prompt', 'Перефразируй этот текст: {UserText1}'),
(6, 'llm_provider', 'openai'),
(7, 'openai_api_key', ''),
(8, 'openai_ai_model', 'gpt-3.5-turbo'),
(9, 'openai_img_size', '1024x1024'),
(10, 'openrouter_api_key', ''),
(11, 'openrouter_ai_model', 'cohere/command-r-plus'),
(12, 'proxy_ip', ''),
(13, 'proxy_port', ''),
(14, 'proxy_login', ''),
(15, 'proxy_pass', ''),
(16, 'license_key', ''),
(17, 'unsafe_parsing', '0'),
(19, 'version', '1.4.2__1729946856'),
(20, 'posts_moderator', ''),
(21, 'vk_config', '{\"app_id\": \"\",\"private_key\": \"\",\"service_key\": \"\"}'),
(22, 'update_available', '');

DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `uid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `config` text COLLATE utf8mb4_bin,
  `stats` text COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `message_group`;
CREATE TABLE `message_group` (
  `id` int NOT NULL,
  `grouped_id` varchar(128) COLLATE utf8mb4_bin NOT NULL,
  `channel_id` int NOT NULL,
  `message` text COLLATE utf8mb4_bin NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `model`;
CREATE TABLE `model` (
  `id` int NOT NULL,
  `provider` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `code` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO `model` (`id`, `provider`, `name`, `code`) VALUES
(1, 'openai', 'GPT-3.5 Turbo', 'gpt-3.5-turbo'),
(2, 'openai', 'GPT-3.5 Turbo 16k', 'gpt-3.5-turbo-16k'),
(3, 'openai', 'GPT-4 Turbo (preview)', 'gpt-4-1106-preview'),
(4, 'openai', 'gpt-4o-mini', 'gpt-4o-mini'),
(5, 'openai', 'gpt-4o-2024-08-06', 'gpt-4o-2024-08-06'),
(6, 'openrouter', 'Anthropic: Claude v2.1', 'anthropic/claude-2.1'),
(7, 'openrouter', 'Perplexity: PPLX 7B Online', 'perplexity/pplx-7b-online'),
(8, 'openrouter', 'Nous: Capybara 7B (free)', 'nousresearch/nous-capybara-7b:free'),
(9, 'openrouter', 'GPT-4 Turbo (preview)', 'openai/gpt-4-turbo-preview'),
(10, 'openrouter', 'GPT-4 32k', 'openai/gpt-4-32k'),
(11, 'openrouter', 'Mistral 7B Instruct (free)', 'mistralai/mistral-7b-instruct:free'),
(12, 'openrouter', 'Toppy M 7B (free)', 'undi95/toppy-m-7b:free'),
(13, 'openrouter', 'Cohere: Command R+', 'cohere/command-r-plus'),
(14, 'openrouter', 'Anthropic: Claude 3 Opus', 'anthropic/claude-3-opus'),
(15, 'openrouter', 'Anthropic: Claude 3 Sonnet', 'anthropic/claude-3-sonnet'),
(16, 'openrouter', 'Anthropic: Claude 3 Haiku', 'anthropic/claude-3-haiku'),
(17, 'openrouter', 'Perplexity: PPLX 70B Online', 'perplexity/pplx-70b-online'),
(18, 'openrouter', 'Google: Gemini Pro 1.5 (preview)', 'google/gemini-pro-1.5'),
(19, 'openrouter', 'Google: PaLM 2 Code Chat', 'google/palm-2-codechat-bison'),
(20, 'openrouter', 'OpenChat 3.5', 'openchat/openchat-7b'),
(21, 'openrouter', 'openai/gpt-4o-2024-08-06', 'openai/gpt-4o-2024-08-06'),
(22, 'openrouter', 'openai/o1-mini-2024-09-12', 'openai/o1-mini-2024-09-12'),
(23, 'openrouter', 'openai/o1-preview', 'openai/o1-preview'),
(24, 'openrouter', 'nousresearch/hermes-3-llama-3.1-405b:free', 'nousresearch/hermes-3-llama-3.1-405b:free'),
(25, 'openrouter', 'google/gemini-pro-1.5-exp', 'google/gemini-pro-1.5-exp'),
(26, 'openrouter', 'perplexity/llama-3.1-sonar-large-128k-online', 'perplexity/llama-3.1-sonar-large-128k-online'),
(27, 'openrouter', 'openai/gpt-4o-mini-2024-07-18', 'openai/gpt-4o-mini-2024-07-18'),
(28, 'openrouter', 'liquid/lfm-40b:free', 'liquid/lfm-40b:free');

DROP TABLE IF EXISTS `post`;
CREATE TABLE `post` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `channel_id` int DEFAULT NULL,
  `source_id` int DEFAULT NULL,
  `uid` varchar(128) COLLATE utf8mb4_bin DEFAULT NULL,
  `status` enum('draft','queued','posted','moderation') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'draft',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `content` text COLLATE utf8mb4_bin,
  `files` text COLLATE utf8mb4_bin,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pub_date` datetime DEFAULT NULL,
  `attempt` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `tg_user`;
CREATE TABLE `tg_user` (
  `id` int NOT NULL,
  `chat_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `uid` varchar(18) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `first_name` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `last_name` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `username` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `muted_for` datetime DEFAULT NULL,
  `state` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `parameters` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `registered_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int NOT NULL,
  `login` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `date_register` datetime DEFAULT NULL,
  `last_visit` datetime DEFAULT NULL,
  `ip` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `pswd` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `type` enum('user','admin','blocked') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'user',
  `visible_pages` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `token` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;


ALTER TABLE `bot_context`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`);

ALTER TABLE `channel`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cfg_name` (`cfg_name`);

ALTER TABLE `group`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `message_group`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `model`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tg_user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);


ALTER TABLE `bot_context`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `channel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `group`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `message_group`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `model`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

ALTER TABLE `post`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `tg_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
