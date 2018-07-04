--
-- Base de données: `saru`
--

-- --------------------------------------------------------

--
-- Structure de la table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `account_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_user_id` int(10) unsigned NOT NULL COMMENT 'FK users.user_id',
  `account_name` varchar(255) NOT NULL,
  `account_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 inactive / 1 active',
  PRIMARY KEY (`account_id`),
  KEY `compte_actif` (`account_active`),
  KEY `account_user_id` (`account_user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure de la table `account_metas`
--

CREATE TABLE IF NOT EXISTS `account_metas` (
  `meta_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meta_name` varchar(255) NOT NULL,
  `meta_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'order for display',
  `meta_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 inactive / 1 active',
  PRIMARY KEY (`meta_id`),
  KEY `meta_active` (`meta_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='metas infos for contacts' AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `account_meta_relationships`
--

CREATE TABLE IF NOT EXISTS `account_meta_relationships` (
  `rel_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rel_account_id` int(10) unsigned NOT NULL COMMENT 'FK accounts.id',
  `rel_meta_id` int(10) unsigned NOT NULL COMMENT 'FK accounts_metas.id',
  `rel_value` text,
  PRIMARY KEY (`rel_id`),
  KEY `rel_account_id` (`rel_account_id`,`rel_meta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='relationships between contacts and metas' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `alerts`
--

CREATE TABLE IF NOT EXISTS `alerts` (
  `alert_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alert_user_id` int(10) unsigned NOT NULL COMMENT 'FK users.user_id',
  `alert_contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK contacts.contact_id',
  `alert_priority` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `alert_date` date DEFAULT NULL,
  `alert_comments` text NOT NULL,
  `alert_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0-1 ; 1 = done',
  PRIMARY KEY (`alert_id`),
  KEY `alert_user_id` (`alert_user_id`,`alert_contact_id`,`alert_done`),
  KEY `alert_priority` (`alert_priority`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `attachments`
--

CREATE TABLE IF NOT EXISTS `attachments` (
  `attachment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attachment_account_id` int(10) unsigned NOT NULL COMMENT 'FK accounts.PK',
  `attachment_type_item` enum('ct','ci','me') DEFAULT NULL,
  `attachment_item_id` int(10) unsigned NOT NULL COMMENT 'FK contacts.PK ; companies.PK ; meetings.PK',
  `attachment_real_name` varchar(255) NOT NULL COMMENT 'file name as user entered it',
  `attachment_intern_name` varchar(255) NOT NULL COMMENT 'file name used to store it',
  PRIMARY KEY (`attachment_id`),
  KEY `attachment_account_id` (`attachment_account_id`,`attachment_type_item`,`attachment_item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Structure de la table `companies`
--

CREATE TABLE IF NOT EXISTS `companies` (
  `company_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_account_id` int(10) unsigned DEFAULT NULL,
  `company_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK contact_types.type_id',
  `company_name` varchar(255) NOT NULL,
  `company_comments` text,
  `company_date_add` date NOT NULL COMMENT 'date the company was added',
  `company_date_update` date NOT NULL COMMENT 'date the company was updated last',
  `company_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'tells if a company is active (visible) or inactive (invisible by default)',
  PRIMARY KEY (`company_id`),
  KEY `company_type_id` (`company_type_id`),
  KEY `company_active` (`company_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `company_metas`
--

CREATE TABLE IF NOT EXISTS `company_metas` (
  `meta_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meta_name` varchar(255) NOT NULL,
  `meta_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'order for display',
  `meta_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 inactive / 1 active',
  PRIMARY KEY (`meta_id`),
  KEY `meta_active` (`meta_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='metas infos for companies' AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Structure de la table `company_meta_relationships`
--

CREATE TABLE IF NOT EXISTS `company_meta_relationships` (
  `rel_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rel_company_id` int(10) unsigned NOT NULL COMMENT 'FK company.id',
  `rel_meta_id` int(10) unsigned NOT NULL COMMENT 'FK company_metas.id',
  `rel_value` text,
  PRIMARY KEY (`rel_id`),
  KEY `rel_contact_id` (`rel_company_id`,`rel_meta_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='relationships between companies and metas' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `components`
--

CREATE TABLE IF NOT EXISTS `components` (
  `component_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `component_name` varchar(255) NOT NULL,
  `component_description` varchar(255) DEFAULT NULL,
  `component_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`component_id`),
  UNIQUE KEY `component_name` (`component_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='the components are used to manage access rights' AUTO_INCREMENT=39 ;

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `contact_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_account_id` int(10) unsigned NOT NULL COMMENT 'FK accounts.id',
  `contact_company_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK company.id',
  `contact_type_id` int(10) unsigned NOT NULL COMMENT 'FK contact_type.id',
  `contact_lastname` varchar(255) DEFAULT NULL,
  `contact_firstname` varchar(255) DEFAULT NULL,
  `contact_comments` text,
  `contact_date_add` date NOT NULL COMMENT 'date the contact was added',
  `contact_date_update` date NOT NULL COMMENT 'date the contact was updated last',
  `contact_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'tells if a contact is active (visible) ou inactive (invisible by default)',
  PRIMARY KEY (`contact_id`),
  KEY `contact_compte_id` (`contact_account_id`,`contact_company_id`,`contact_type_id`),
  KEY `contact_active` (`contact_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `contact_metas`
--

CREATE TABLE IF NOT EXISTS `contact_metas` (
  `meta_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meta_name` varchar(255) NOT NULL,
  `meta_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'order for display',
  `meta_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 inactive / 1 active',
  PRIMARY KEY (`meta_id`),
  KEY `meta_active` (`meta_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='metas infos for contacts' AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Structure de la table `contact_meta_relationships`
--

CREATE TABLE IF NOT EXISTS `contact_meta_relationships` (
  `rel_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rel_contact_id` int(10) unsigned NOT NULL COMMENT 'FK contacts.id',
  `rel_meta_id` int(10) unsigned NOT NULL COMMENT 'FK contact_metas.id',
  `rel_value` text,
  PRIMARY KEY (`rel_id`),
  KEY `rel_contact_id` (`rel_contact_id`,`rel_meta_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='relationships between contacts and metas' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `contact_types`
--

CREATE TABLE IF NOT EXISTS `contact_types` (
  `contact_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_type_name` varchar(255) NOT NULL,
  `contact_type_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'one type is default type for new contacts',
  `contact_type_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 : inactive / 1 : active',
  PRIMARY KEY (`contact_type_id`),
  KEY `contact_type_default` (`contact_type_default`,`contact_type_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Structure de la table `meetings`
--

CREATE TABLE IF NOT EXISTS `meetings` (
  `meeting_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meeting_contact_id` int(10) unsigned NOT NULL COMMENT 'FK contacts.contact_id',
  `meeting_type_id` int(10) unsigned NOT NULL COMMENT 'FK meeting_types.id',
  `meeting_date` date NOT NULL,
  `meeting_comments` text,
  PRIMARY KEY (`meeting_id`),
  KEY `meeting_type_id` (`meeting_type_id`,`meeting_date`),
  KEY `meeting_contact_id` (`meeting_contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `meeting_types`
--

CREATE TABLE IF NOT EXISTS `meeting_types` (
  `meeting_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meeting_type_name` varchar(255) NOT NULL,
  `meeting_type_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 inactive / 1 active',
  PRIMARY KEY (`meeting_type_id`),
  KEY `meta_active` (`meeting_type_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='metas infos for contacts' AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` varchar(40) NOT NULL,
  `session_last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `session_data` text,
  PRIMARY KEY (`session_id`),
  KEY `session_last_activity` (`session_last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_lastname` varchar(255) DEFAULT NULL,
  `user_firstname` varchar(255) DEFAULT NULL,
  `user_login` varchar(255) DEFAULT NULL,
  `user_pwd` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `user_send_alerts` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `user_new_pwd` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `user_token` varchar(255) DEFAULT NULL,
  `user_isadmin` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='utilisateurs du crm' AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure de la table `user_account_relationships`
--

CREATE TABLE IF NOT EXISTS `user_account_relationships` (
  `rel_user_id` int(10) unsigned NOT NULL COMMENT 'FK users.user_id',
  `rel_account_id` int(10) unsigned NOT NULL COMMENT 'FK accounts.account_id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='table of relation between users and components';

-- --------------------------------------------------------

--
-- Structure de la table `user_component_relationships`
--

CREATE TABLE IF NOT EXISTS `user_component_relationships` (
  `rel_user_id` int(10) unsigned NOT NULL COMMENT 'FK users.user_id',
  `rel_component_id` int(10) unsigned NOT NULL COMMENT 'FK components.component_id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='table of relation between users and components';



-- --------------------------------------------------------

--
-- Contenu de la table `accounts`
--

INSERT INTO `accounts` (`account_id`, `account_user_id`, `account_name`, `account_active`) VALUES
(1, 1, 'My personal account', 1);

--
-- Contenu de la table `account_metas`
--

INSERT INTO `account_metas` (`meta_id`, `meta_name`, `meta_order`, `meta_active`) VALUES
(1, 'telephone', 10, 1),
(2, 'email', 20, 1),
(3, 'Adresse', 8, 1);

--
-- Contenu de la table `company_metas`
--

INSERT INTO `company_metas` (`meta_id`, `meta_name`, `meta_order`, `meta_active`) VALUES
(2, 'tÃ©lÃ©phone (standard)', 10, 1),
(4, 'email de contact', 20, 1),
(6, 'adresse', 30, 1),
(7, 'code postal', 31, 1),
(8, 'ville', 32, 1),
(9, 'Site internet', 35, 1),
(10, 'Fax', 15, 1);

--
-- Contenu de la table `components`
--

INSERT INTO `components` (`component_id`, `component_name`, `component_description`, `component_order`) VALUES
(1, 'account_list', 'liste des comptes', 50),
(2, 'account_form', 'ajout et modification de comptes', 51),
(3, 'account_del', 'suppression d''un compte', 52),
(5, 'alert_list', 'liste des alertes', 25),
(6, 'alert_form', 'ajout et modification d''alertes', 26),
(7, 'alert_del', 'suppression d''une alerte', 27),
(8, 'contact_list', 'liste de contacts', 10),
(9, 'contact_form', 'ajout / modification de contacts', 11),
(10, 'contact_del', 'suppression d''un contact', 13),
(11, 'contact_recap', 'detail d''un contact', 12),
(12, 'contact_import', 'importation de contacts', 14),
(13, 'meeting_list', 'liste des rendez-vous', 20),
(14, 'meeting_form', 'ajout et modification de rendez-vous', 21),
(15, 'meeting_del', 'suppression d''un rendez-vous', 22),
(16, 'admin', 'administration de l''outil', 100),
(17, 'note_list', 'liste des notes', 30),
(18, 'note_form', 'ajout et modification de notes', 31),
(19, 'note_del', 'suppression de notes', 32),
(23, 'user_list', 'liste des utilisateurs', 105),
(24, 'user_form', 'ajout et modification d''utilisateurs', 106),
(25, 'user_del', 'suppression d''utilisateurs', 107),
(32, 'company_list', 'Liste des entreprises', 16),
(33, 'company_form', 'Ajout / modification d''une entreprise', 17),
(34, 'company_del', 'Suppression d''une entreprise', 18),
(35, 'contact_export', 'Export des contacts', 15),
(36, 'company_recap', 'fiche entreprise', 19),
(37, 'meeting_export', 'exportation de l''historique', 23),
(38, 'account_choice', 'choix d''un compte', 53),
(39, 'export_template', 'Exportation du template', 150),
(40, 'export_search_contact', 'Exportation d''une recherche de contacts', 155),
(41, 'export_search_company', 'Export d''une recherche d''entreprises', 160),
(42, 'alert_assign', 'Attribuer une alerte a un autre utilisateur', 27);

--
-- Contenu de la table `contact_metas`
--

INSERT INTO `contact_metas` (`meta_id`, `meta_name`, `meta_order`, `meta_active`) VALUES
(1, 'fonction', 5, 1),
(2, 'tÃ©lÃ©phone principal', 10, 1),
(3, 'tÃ©lÃ©phone secondaire', 15, 1),
(4, 'email principal', 20, 1),
(5, 'email secondaire', 25, 1),
(6, 'adresse', 30, 1),
(7, 'code postal', 31, 1),
(8, 'ville', 32, 1);

--
-- Contenu de la table `contact_types`
--

INSERT INTO `contact_types` (`contact_type_id`, `contact_type_name`, `contact_type_default`, `contact_type_active`) VALUES
(1, 'non classÃ©', 1, 1),
(2, 'prospect', 0, 1),
(3, 'client', 0, 1),
(4, 'sous-traitant', 0, 1),
(5, 'fournisseur', 0, 1),
(6, 'partenaire', 0, 1),
(7, 'autre', 0, 1),
(8, 'prescripteur', 0, 1);

--
-- Contenu de la table `meeting_types`
--

INSERT INTO `meeting_types` (`meeting_type_id`, `meeting_type_name`, `meeting_type_active`) VALUES
(1, 'physique', 1),
(2, 'mail', 1),
(3, 'telephone', 1),
(4, 'rÃ©seaux sociaux', 1),
(6, 'courrier', 1),
(7, 'messagerie instantanÃ©e', 1),
(8, 'autre', 1),
(9, 'Ã©vÃ©nement', 1);

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`user_id`, `user_lastname`, `user_firstname`, `user_login`, `user_pwd`, `user_email`, `user_send_alerts`, `user_new_pwd`, `user_token`, `user_isadmin`) VALUES
(1, 'Doe', 'John', 'saru', 'ff250145ce01e9f53848dbd155d4e0fa317bec04b04bfcae1629638f22f28f7653a4fab3b921b6ac3d5c1f41fed1356f3ef4380294706d019e91ae925ada739b', 1, 'your.email@your-company.com', 1, NULL, 0);

--
-- Contenu de la table `user_account_relationships`
--

INSERT INTO `user_account_relationships` (`rel_user_id`, `rel_account_id`) VALUES
(1, 1);

--
-- Contenu de la table `user_component_relationships`
--

INSERT INTO `user_component_relationships` (`rel_user_id`, `rel_component_id`) VALUES
(1, 8),
(1, 9),
(1, 11),
(1, 10),
(1, 12),
(1, 35),
(1, 32),
(1, 33),
(1, 34),
(1, 36),
(1, 13),
(1, 14),
(1, 15),
(1, 37),
(1, 5),
(1, 6),
(1, 7),
(1, 1),
(1, 2),
(1, 3),
(1, 38),
(1, 16),
(1, 23),
(1, 24),
(1, 25),
(1, 39),
(1, 40),
(1, 41),
(1, 42);

