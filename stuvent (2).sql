-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Gegenereerd op: 02 feb 2023 om 16:06
-- Serverversie: 10.4.25-MariaDB
-- PHP-versie: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stuvent`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `about`
--

CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `text` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `images` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20230202084843', '2023-02-02 09:48:46', 36);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `event`
--

CREATE TABLE `event` (
  `id` int(11) NOT NULL,
  `opleiding_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` datetime NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aantal_uur` int(11) NOT NULL,
  `niveau` int(11) NOT NULL,
  `attendees` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enddate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `event`
--

INSERT INTO `event` (`id`, `opleiding_id`, `title`, `description`, `company`, `date`, `image`, `aantal_uur`, `niveau`, `attendees`, `enddate`) VALUES
(12, 5, 'Vakantiebeurs', 'Kom helpen op de vakantiebeurs.', 'ROC Mondriaan', '2023-01-12 09:30:00', 'img/event-img/afbeelding2-63c8f66838e3a-63db7caf320ec.jpg', 8, 1, '10', '2023-01-12 17:30:00'),
(13, 5, 'Vakantiebeurs', 'Kom helpen op de vakantiebeurs.', 'ROC Mondriaan', '2023-01-13 09:30:00', 'img/event-img/afbeelding2-63c8f66838e3a-63db7cc0217e1.jpg', 8, 1, '10', '2023-01-13 17:30:00'),
(14, 5, 'Vakantiebeurs', 'Kom helpen op de vakantiebeurs.', 'ROC Mondriaan', '2023-01-14 09:30:00', 'img/event-img/afbeelding2-63c8f66838e3a-63db7cc7d849d.jpg', 8, 1, '10', '2023-01-14 17:30:00'),
(15, 5, 'Vakantiebeurs', 'Kom helpen op de vakantiebeurs.', 'ROC Mondriaan', '2023-01-15 09:30:00', 'img/event-img/afbeelding2-63c8f66838e3a-63db7cce52d99.jpg', 8, 1, '10', '2023-01-15 17:30:00'),
(16, 5, 'ADO - NAC Breda', 'Host bij voetbalwedstrijd: ontvang de gasten en wijs hen de weg. Kijk mee tijdens de wedstrijd op de tribune!!!!', 'ADO', '2023-02-03 17:45:00', 'img/event-img/ado-6385f12b22719-63db7d471a162.png', 5, 1, '10', '2023-02-03 22:45:00'),
(19, 5, 'Open Dag school voor Toerisme', 'Open dag school voor toerisme, recreatie en evenementen.', 'ROC Mondriaan', '2023-02-07 13:00:00', 'img/event-img/afbeelding4-63c8fdf7c15b9-63db7db30f650-63dbb97c70d75.jpg', 7, 1, '25', '2023-02-07 20:00:00'),
(20, 10, 'Ocean Race The Hague', 'Eventmakers is het landelijke platform voor vrijwilligers in sport evenemten. Wij zijn ervan overtuigd dat vrijwilligers van onmisbare waarde zijn in de organisatie van sportevenemten en daarom zijn wij op zoek naar vrijwilligers voor The Ocean race The Hague van 11 t/m 15 Juni! De haven van Schevening wordt de plek waar zeilen, watersport en duurzaamheid samenkomen. Zonder vrijwiller is er geen Ocean Race! Daarom zijn wij op zoek naar enthousiaste vrijwilligers die een steentje willen bijdragen aan dit evenement en jij kan deel uitmaken van dit team! Alle vrijwilligers meldden zich aan via eventmakers: Het landelijke platform voor vrijwilligers in sportevenement. \r\n\r\nVrijwilligersfuncties:\r\n\r\nOperations duurzaamheid\r\n\r\n*uitgebreide omschrijvingen van de bovenstaande vrijwilligers zijn te vinden op de evenementenpagina van The Ocean Race.\r\n\r\nWat Vragen we van jou? \r\n- Een beschikbaarheid van minimaal 3 dagen;\r\n- Minimale leeftijd van 16 jaar of ouder op 10 juni 2023;\r\n- Het beheersen van in elk geval Nederlands of Engels;\r\n- Een akkoord op onze vrijwilligers overeenkomst;\r\n- Een enthousiasme, Gastvrijheid, inzet met passie voor het evenement;\r\n- Reis en verblijfkosten zijn voor eigen rekening;\r\n\r\nWat krijg je van ons? \r\nHet ontmoeten  van veel nieuwe mensen en een uniek kijkje achter de schermen van een groot internationaal sportevenemt;\r\n- Een gaaf en uniek kledingpakket in de stijl van het evenement;\r\n- Goed eten en drinken tijdens het evenement;\r\n- Een persoonlijke kennismaking en het benutten van jouw talenten door te kijken waar we jou het beste kunnen inzetten;\r\n- En wij verzorgen een inspirerende kick-off voor alle eventmakers van de Ocean Race;', 'EventMakers', '2023-06-11 09:00:00', 'img/event-img/ocean-race-63dbd12ce2418.jpg', 20, 1, '20', '2023-06-15 17:00:00');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `klas`
--

CREATE TABLE `klas` (
  `id` int(11) NOT NULL,
  `naam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `klas`
--

INSERT INTO `klas` (`id`, `naam`) VALUES
(7, 'E1A '),
(8, 'E1B '),
(9, 'T1A '),
(10, 'T1D '),
(11, 'T2A '),
(12, 'T2D '),
(13, 'T2E'),
(14, 'N.V.T.');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `opleiding`
--

CREATE TABLE `opleiding` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `opleiding`
--

INSERT INTO `opleiding` (`id`, `name`) VALUES
(5, 'Leidinggevende Travel & Hospitality'),
(6, 'Leidinggevende  Leisure & Hospitality '),
(7, 'Junior Event Manager'),
(8, 'Zelfstandig Medewerker Travel en Hospitality'),
(9, 'Zelfstandig Medewerker Leisure & Hospitality'),
(10, 'Staf en Overig');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `opleiding_id` int(11) DEFAULT NULL,
  `klas_id` int(11) DEFAULT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:json)',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_number` int(11) NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefoonnummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `user`
--

INSERT INTO `user` (`id`, `opleiding_id`, `klas_id`, `email`, `roles`, `password`, `student_number`, `first_name`, `last_name`, `image`, `telefoonnummer`) VALUES
(10, 10, 14, 'koen@email.com', '[\"ROLE_beheerder\"]', '$2y$13$Wwr049KdKRP3gNYfU8D.Z.j1aR4wvf6eKazWd5YlW/oCwyRifHurK', 302261150, 'Koen', 'Green', 'img/profile-img/bran-castle-romania-525975996-emicristea-63db75aacb610.jpg', '0626075765'),
(11, 10, 14, 'p.vd.weide@rocmondriaan.nl', '[\"ROLE_ADMIN\"]', '$2y$13$lBm3qr.R3ZkB0ta6ZCppFe4/vYNxTcV1q1Oum.w4YJJOS89F9byDm', 2147483647, 'Mariska', 'van der Weide', 'img/profile-img/logo-rocm-rgb-63db7731642f0.jpg', '0611754108'),
(12, 10, 14, 'Manish@email.com', '[\"ROLE_beheerder\"]', '$2y$13$3LB0uEYQxCThIk9sNPi5aut45NO4HpsX8RhQbgrrXPlkIzIWQcsPa', 302261278, 'Manish', 'Mahadew', 'img/profile-img/profile-63c8f9da83989-63cfa21170565-63db7e3b54dab.jpg', '06484754857'),
(13, 5, 7, 'Aegon@email.com', '[\"ROLE_USER\"]', '$2y$13$ouRkFSMQON903c4tnwvZy.rjCOXhR6.5RWV4aPS4Qu//bvPct/w4i', 304563546, 'Aegon', 'Vijfwinkel', 'img/profile-img/aegon-vijfwinkel-63db8058d8e30.jpg', '06353563356'),
(14, 5, 7, 'Anna@email.com', '[\"ROLE_USER\"]', '$2y$13$E.tyvshk6MvIZXhwdlso6O.TIrb67BjdyU9gQQXh8dTYz97RNttkO', 304563547, 'Anna', 'Frankenburg', 'img/profile-img/anna-frankenbrug-63db80798c882.jpg', '06353943949'),
(15, 6, 8, 'Arthur@email.com', '[\"ROLE_USER\"]', '$2y$13$DPYQXJXdUSAazQ1H8EZGK.mrnM8BoagZFlizSlLeKQDnK81TJ6/2K', 304563550, 'Arthur', 'de Bas', 'img/profile-img/arthur-de-bas-63db80a39af6d.jpg', '06353943949'),
(16, 6, 8, 'Cas@email.com', '[\"ROLE_USER\"]', '$2y$13$js.gc7Vsy4ILz7XsdOKvfuyZxe7eAkdOy7XWDAISmec4mbeW3VF/y', 304563555, 'Cas', 'Caspasius', 'img/profile-img/cas-caspasius-63db80cc90689.jpg', '06353943949'),
(17, 7, 9, 'Crystal@email.com', '[\"ROLE_USER\"]', '$2y$13$jJ16OmeRjN6lYXuEo4P9nuM9/MDQEEGHM.dYF.UPq75S/xxsyHk.K', 304563521, 'Crystal', 'McQueen', 'img/profile-img/crystal-mcqueen-63db80f80a62a.jpg', '06353943912'),
(18, 8, 10, 'Evelien@email.com', '[\"ROLE_USER\"]', '$2y$13$yIu4rApz5FUudoM35TLiYufxvTUSo01JSTBJMAfvW8O3IAj5ulx.u', 304563541, 'Evelien', 'de Wit', 'img/profile-img/evelien-de-wit-63db81210ee0b.jpg', '063539439145'),
(19, 8, 10, 'Fleur@email.com', '[\"ROLE_USER\"]', '$2y$13$Zt4upS6Lbg8Yh/zG8mxceOWyGmJWml3UgAglRDIjiZH9eKmtr2H9i', 304564541, 'Fleur', 'Naaktgeboren', 'img/profile-img/fleur-naaktgeboren-63db8145e9367.jpg', '063539439145'),
(20, 9, 11, 'Hikaru@email.com', '[\"ROLE_USER\"]', '$2y$13$uGk/f11/AwIH2feXZXb5FOWfzhc7Vu5/CLR5p4l.ZGneYQ.DK15cy', 2147483647, 'Hikaru', 'Nakamura', 'img/profile-img/hikaru-nakamura-63db81744ccf8.jpg', '063539439145'),
(21, 9, 12, 'Mark@email.com', '[\"ROLE_USER\"]', '$2y$13$ReXnHwgtQbzjUwGKWLrZZuy4ViB2lXDyDypEs8I.KYoBDnWda.vHC', 2147483647, 'Mark', 'van Geest', 'img/profile-img/mark-van-geest-63db8197ba78c.jpg', '063539439145'),
(22, 9, 13, 'Sam', '[\"ROLE_USER\"]', '$2y$13$v6PmF/4tVRuUWgYt6reZAuZ9iqaweS4kixoSm3UAufs1GOV2FEY2u', 2147483647, 'Sam', 'Hert', 'img/profile-img/sam-hert-63db81c9599d3.jpg', '063539439145'),
(23, 8, 13, 'Sem', '[\"ROLE_USER\"]', '$2y$13$YEi7BpgVMTnlgIZzKHldWe85enc1ry0Ps6.uOFVz8.kiMh1RtgwdW', 2147483647, 'Sem', 'van Harte', 'img/profile-img/sem-van-harte-63db81ed12955.jpg', '063539439145'),
(24, 8, 13, 'Zamara', '[\"ROLE_USER\"]', '$2y$13$eifOGEouqZqtTyd7SK/C6uPJGLfl/rxeiXAGFk.rdE46Sgb42Sy4.', 2147483647, 'Zamara', 'de Jong', 'img/profile-img/zamara-de-jong-63db820796b28.jpg', '0635394391452'),
(25, 8, 12, 'Michiel@email.com', '[\"ROLE_USER\"]', '$2y$13$Ijo.Yc7Z3z4t453IzPPVsuG/.XPLCe604wz9BKOsTYTDXXdpJNh6a', 2147483647, 'Michiel', 'Auerbach', 'img/profile-img/michiel-auerbach-63dbade2c9456.jpg', '0674363746');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user_events`
--

CREATE TABLE `user_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `accepted` tinyint(1) NOT NULL,
  `presence` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `user_events`
--

INSERT INTO `user_events` (`id`, `user_id`, `event_id`, `accepted`, `presence`) VALUES
(11, 10, 19, 1, NULL),
(12, 10, 16, 1, NULL);

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexen voor tabel `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3BAE0AA7844BD0B0` (`opleiding_id`);

--
-- Indexen voor tabel `klas`
--
ALTER TABLE `klas`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Indexen voor tabel `opleiding`
--
ALTER TABLE `opleiding`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  ADD KEY `IDX_8D93D649844BD0B0` (`opleiding_id`),
  ADD KEY `IDX_8D93D6492F3345ED` (`klas_id`);

--
-- Indexen voor tabel `user_events`
--
ALTER TABLE `user_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_36D54C77A76ED395` (`user_id`),
  ADD KEY `IDX_36D54C7771F7E88B` (`event_id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `about`
--
ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `event`
--
ALTER TABLE `event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT voor een tabel `klas`
--
ALTER TABLE `klas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT voor een tabel `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `opleiding`
--
ALTER TABLE `opleiding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT voor een tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT voor een tabel `user_events`
--
ALTER TABLE `user_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `FK_3BAE0AA7844BD0B0` FOREIGN KEY (`opleiding_id`) REFERENCES `opleiding` (`id`);

--
-- Beperkingen voor tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D6492F3345ED` FOREIGN KEY (`klas_id`) REFERENCES `klas` (`id`),
  ADD CONSTRAINT `FK_8D93D649844BD0B0` FOREIGN KEY (`opleiding_id`) REFERENCES `opleiding` (`id`);

--
-- Beperkingen voor tabel `user_events`
--
ALTER TABLE `user_events`
  ADD CONSTRAINT `FK_36D54C7771F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `FK_36D54C77A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
