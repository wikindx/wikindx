-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 05, 2020 at 06:42 AM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wikindx`
--

-- --------------------------------------------------------

--
-- Table structure for table `wkx_bibtex_string`
--

CREATE TABLE `wkx_bibtex_string` (
  `bibtexstringId` int(11) NOT NULL,
  `bibtexstringText` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_cache`
--

CREATE TABLE `wkx_cache` (
  `cacheResourceCreators` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheMetadataCreators` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheResourceKeywords` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheMetadataKeywords` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheQuoteKeywords` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheParaphraseKeywords` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheMusingKeywords` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheResourcePublishers` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheMetadataPublishers` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheConferenceOrganisers` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheResourceCollections` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheMetadataCollections` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheResourceCollectionTitles` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheResourceCollectionShorts` longtext COLLATE utf8mb4_unicode_520_ci,
  `cacheKeywords` longtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_cache`
--

INSERT INTO `wkx_cache` (`cacheResourceCreators`, `cacheMetadataCreators`, `cacheResourceKeywords`, `cacheMetadataKeywords`, `cacheQuoteKeywords`, `cacheParaphraseKeywords`, `cacheMusingKeywords`, `cacheResourcePublishers`, `cacheMetadataPublishers`, `cacheConferenceOrganisers`, `cacheResourceCollections`, `cacheMetadataCollections`, `cacheResourceCollectionTitles`, `cacheResourceCollectionShorts`, `cacheKeywords`) VALUES
('YTo0Mzp7aToyOTtzOjIzOiJ2b24gQXJ0aXN0ZSwgRmlyc3RuYW1lICI7aTozMDtzOjIwOiJBcnRpc3RlMiwgRmlyc3RuYW1lICI7aTo0NTtzOjI1OiJkZSBBdHRvcm5leSAxLCBGaXJzdG5hbWUgIjtpOjUxO3M6MjI6IkF0dHJpYnV0ZWUsIEZpcnN0bmFtZSAiO2k6MTtzOjM1OiJkZSBBdXRob3JMYXN0MSwgRmlyc3RuYW1lICBJLk4uSS5ULiI7aToyO3M6MjY6IkRlIEF1dGhvckxhc3QyLCBGaXJzdG5hbWUgIjtpOjExO3M6MjQ6IkF1dGhvckxhc3QzLCBGaXJzdC1OYW1lICI7aToxMjtzOjIzOiJBdXRob3JMYXN0NCwgRmlyc3RuYW1lICI7aTo1MjtzOjI0OiJDYXJ0b2dyYXBoZXIsIEZpcnN0bmFtZSAiO2k6MjY7czoyNToiZGEgQ29tcG9zZXIgMSwgRmlyc3RuYW1lICI7aToyODtzOjIyOiJDb21wb3NlciAyLCBGaXJzdG5hbWUgIjtpOjI3O3M6MjM6IkNvbmR1Y3RvciAxLCBGaXJzdG5hbWUgIjtpOjQxO3M6MjQ6IkNvdW5zZWwgMSwgRmlyc3RuYW1lICBILiI7aTo0MjtzOjI0OiJkZSBDb3Vuc2VsIDIsIEZpcnN0bmFtZSAiO2k6NTM7czoyMToiQ3JlYXRvciAxLCBGaXJzdG5hbWUgIjtpOjU0O3M6MjQ6ImRlIENyZWF0b3IgMiwgRmlyc3RuYW1lICI7aToxNTtzOjIyOiJEaXJlY3RvciAxLCBGaXJzdG5hbWUgIjtpOjE3O3M6MjI6IkRpcmVjdG9yIDIsIEZpcnN0bmFtZSAiO2k6MjE7czoyMzoiRGlyZWN0b3IgMiwgRmlyc3QtTmFtZSAiO2k6MztzOjIzOiJFZGl0b3JMYXN0MSwgRmlyc3RuYW1lICI7aTo0O3M6MjU6InZvbiBFZGl0b3JMYXN0MiwgSS5OLkkuVC4iO2k6MTQ7czoyMzoiRWRpdG9yTGFzdDMsIEZpcnN0bmFtZSAiO2k6NDY7czoyMzoiSW50QXV0aG9yIDEsIEZpcnN0bmFtZSAiO2k6NDg7czoyMzoiSW50QXV0aG9yIDIsIEZpcnN0bmFtZSAiO2k6NDM7czoyMjoiSW52ZW50b3IgMSwgRmlyc3RuYW1lICI7aTo0NztzOjIyOiJJbnZlbnRvciAyLCBGaXJzdG5hbWUgIjtpOjQ0O3M6MjE6Iklzc3VpbmdPcmdhbml6YXRpb24gMSI7aToxMztzOjIxOiJMYXN0TmFtZSwgRmlyc3QtTmFtZSAiO2k6MjM7czoyMToidm9uIFBlcmZvcm1lciAxLCBJLk0uIjtpOjI0O3M6MjM6IlBlcmZvcm1lciAyLCBGaXJzdG5hbWUgIjtpOjI1O3M6MjM6IlBlcmZvcm1lciAzLCBGaXJzdG5hbWUgIjtpOjE2O3M6MjE6IlByb2R1Y2VyIDEsIEZpcnNuYW1lICI7aToyMjtzOjI1OiJEZSBQcm9kdWNlciAxLCBGaXJzdG5hbWUgIjtpOjE4O3M6MjE6IlByb2R1Y2VyIDIsIEZpcnNuYW1lICI7aToyMDtzOjM0OiJkZSBQcm9kdWNlciAyLCBGaXJzdG5hbWUgIEkuTi5JLlQuIjtpOjQ5O3M6MjA6IlJlY2lwaWVudCwgRmlyc25hbWUgIjtpOjUwO3M6MjU6InZhbiBSZWNpcGllbnQsIEZpcnN0bmFtZSAiO2k6NztzOjI0OiJSZXZpc2VyTGFzdDEsIEZpcnN0bmFtZSAiO2k6ODtzOjMzOiJSZXZpc2VyTGFzdDIsIEZpcnN0bmFtZSAgSS5OLkkuVC4iO2k6OTtzOjM4OiJTZXJpZXNFZGl0b3JMYXN0MSwgRmlyc3RuYW1lICBJLk4uSS5ULiI7aToxMDtzOjI5OiJTZXJpZXNFZGl0b3JMYXN0MiwgRmlyc3RuYW1lICI7aTo1O3M6MzY6IlRyYW5zbGF0b3JMYXN0MSwgRmlyc3RuYW1lICBJLk4uSS5ULiI7aTo2O3M6MTU6IlRyYW5zbGF0b3JMYXN0MSI7fQ==', 'YToxMjp7aToxO3M6MzU6ImRlIEF1dGhvckxhc3QxLCBGaXJzdG5hbWUgIEkuTi5JLlQuIjtpOjI7czoyNjoiRGUgQXV0aG9yTGFzdDIsIEZpcnN0bmFtZSAiO2k6MTE7czoyNDoiQXV0aG9yTGFzdDMsIEZpcnN0LU5hbWUgIjtpOjEyO3M6MjM6IkF1dGhvckxhc3Q0LCBGaXJzdG5hbWUgIjtpOjM7czoyMzoiRWRpdG9yTGFzdDEsIEZpcnN0bmFtZSAiO2k6NDtzOjI1OiJ2b24gRWRpdG9yTGFzdDIsIEkuTi5JLlQuIjtpOjc7czoyNDoiUmV2aXNlckxhc3QxLCBGaXJzdG5hbWUgIjtpOjg7czozMzoiUmV2aXNlckxhc3QyLCBGaXJzdG5hbWUgIEkuTi5JLlQuIjtpOjk7czozODoiU2VyaWVzRWRpdG9yTGFzdDEsIEZpcnN0bmFtZSAgSS5OLkkuVC4iO2k6MTA7czoyOToiU2VyaWVzRWRpdG9yTGFzdDIsIEZpcnN0bmFtZSAiO2k6NTtzOjM2OiJUcmFuc2xhdG9yTGFzdDEsIEZpcnN0bmFtZSAgSS5OLkkuVC4iO2k6NjtzOjE1OiJUcmFuc2xhdG9yTGFzdDEiO30=', 'YTo1OntpOjE7czozOiJrdzEiO2k6MjtzOjM6Imt3MiI7aTozO3M6Mzoia3czIjtpOjQ7czozOiJrdzQiO2k6NTtzOjM6Imt3NSI7fQ==', NULL, 'YTo0OntpOjI7czozOiJrdzIiO2k6MztzOjM6Imt3MyI7aTo0O3M6Mzoia3c0IjtpOjU7czozOiJrdzUiO30=', 'YTo0OntpOjE7czozOiJrdzEiO2k6MjtzOjM6Imt3MiI7aTozO3M6Mzoia3czIjtpOjQ7czozOiJrdzQiO30=', 'YToyOntpOjE7czozOiJrdzEiO2k6MztzOjM6Imt3MyI7fQ==', 'YTozMDp7aToyODtzOjI3OiJBc3NpZ25lZTogQXNzaWduZWUgTG9jYXRpb24iO2k6Mjk7czozMToiQXNzaWduZWUgMjogQXNzaWduZWUgTG9jYXRpb24gMiI7aToxODtzOjU0OiJCcm9hZGNhc3QgQ2hhbm5lbCBOYW1lIDE6IEJyb2FkY2FzdCBDaGFubmVsIExvY2F0aW9uIDEiO2k6MTk7czo1NDoiQnJvYWRjYXN0IENoYW5uZWwgTmFtZSAyOiBCcm9hZGNhc3QgQ2hhbm5lbCBMb2NhdGlvbiAyIjtpOjU7czo0MToiQ29uZmVyZW5jZSBPcmdhbml6ZXI6IENvbmZlcmVuY2UgTG9jYXRpb24iO2k6NztzOjQ1OiJDb25mZXJlbmNlIE9yZ2FuaXplciAyOiBDb25mZXJlbmNlIExvY2F0aW9uIDIiO2k6OTtzOjQ1OiJDb25mZXJlbmNlIE9yZ2FuaXplciAzOiBDb25mZXJlbmNlIExvY2F0aW9uIDMiO2k6MTA7czo1NToiQ29uZmVyZW5jZSBQdWJsaXNoZXIgMzogQ29uZmVyZW5jZSBQdWJsaXNoZXIgTG9jYXRpb24gMyI7aTo2O3M6NTY6IkNvbmZlcmVuY2UgUHVibGlzaGVyIE5hbWU6IENvbmZlcmVuY2UgUHVibGlzaGVyIExvY2F0aW9uIjtpOjg7czo2MDoiQ29uZmVyZW5jZSBQdWJsaXNoZXIgTmFtZSAyOiBDb25mZXJlbmNlIFB1Ymxpc2hlciBMb2NhdGlvbiAyIjtpOjI0O3M6NzoiQ291cnQgMSI7aToyNTtzOjc6IkNvdXJ0IDIiO2k6MTY7czoxMzoiRGlzdHJpYnV0b3IgMSI7aToxNztzOjEzOiJEaXN0cmlidXRvciAyIjtpOjIyO3M6MjU6IkhlYXJpbmc6IEhlYXJpbmcgTG9jYXRpb24iO2k6MjM7czoyNzoiSGVhcmluZzogSGVhcmluZyBMb2NhdGlvbiAyIjtpOjExO3M6MzI6Ikluc3RpdHV0aW9uOiBJbnN0aXR1dGlvbkxvY2F0aW9uIjtpOjEyO3M6MzQ6Ikluc3RpdHV0aW9uMjogSW5zdGl0dXRpb25Mb2NhdGlvbjIiO2k6MjY7czozODoiTGVnaXNsYXRpdmUgQm9keSAzOiBMZWdCb2R5IExvY2F0aW9uIDMiO2k6Mjc7czozODoiTGVnaXNsYXRpdmUgQm9keSA0OiBMZWdCb2R5IExvY2F0aW9uIDQiO2k6MTM7czozMzoiUHVibGlzaGVyIDY6IFB1Ymxpc2hlciBMb2NhdGlvbiA2IjtpOjMwO3M6MzE6IlB1Ymxpc2hlciA5OiBQdWJsaXNoZXJMb2NhdGlvbjkiO2k6MTtzOjM0OiJQdWJsaXNoZXJOYW1lMTogUHVibGlzaGVyTG9jYXRpb24xIjtpOjM7czozNDoiUHVibGlzaGVyTmFtZTI6IFB1Ymxpc2hlckxvY2F0aW9uMiI7aToxNDtzOjM0OiJQdWJsaXNoZXJOYW1lNzogUHVibGlzaGVyTG9jYXRpb243IjtpOjE1O3M6MzQ6IlB1Ymxpc2hlck5hbWU4OiBQdWJsaXNoZXJMb2NhdGlvbjgiO2k6MjA7czoxNzoiUmVjb3JkIExhYmVsIE5hbWUiO2k6MjE7czoxOToiUmVjb3JkIExhYmVsIE5hbWUgMiI7aToyO3M6NDQ6IlRyYW5zUHVibGlzaGVyTmFtZTE6IFRyYW5zUHVibGlzaGVyTG9jYXRpb24xIjtpOjQ7czo0NDoiVHJhbnNQdWJsaXNoZXJOYW1lMjogVHJhbnNQdWJsaXNoZXJMb2NhdGlvbjIiO30=', 'YTozOntpOjU7czo0MToiQ29uZmVyZW5jZSBPcmdhbml6ZXI6IENvbmZlcmVuY2UgTG9jYXRpb24iO2k6MTtzOjM0OiJQdWJsaXNoZXJOYW1lMTogUHVibGlzaGVyTG9jYXRpb24xIjtpOjE1O3M6MzQ6IlB1Ymxpc2hlck5hbWU4OiBQdWJsaXNoZXJMb2NhdGlvbjgiO30=', NULL, 'YToxNjp7aToxNjtzOjI1OiJBbGJ1bSBUaXRsZSAxIFtBbGJTaGl0bGVdIjtpOjE3O3M6MTM6IkFsYnVtIFRpdGxlIDIiO2k6MjA7czoxNjoiQ29sbGVjdGlvbiBUaXRsZSI7aToyMTtzOjM2OiJDb2xsZWN0aW9uIFRpdGx0ZSAyIFtDb2xsU2hvcnRUaXRsZV0iO2k6MTA7czozMzoiQ29uZmVyZW5jZSBUaXRsZSBbQ29uZlNob3J0VGl0bGVdIjtpOjExO3M6MTg6IkNvbmZlcmVuY2UgVGl0bGUgMiI7aToxMjtzOjM3OiJDb25mZXJlbmNlIFRpdGxlIDMgW0NvbmZTaG9ydFRpdGxlIDNdIjtpOjE0O3M6MzU6IkVuY3ljbG9wYWVkaWEgdGl0bGUgW0VuY1Nob3J0VGl0bGVdIjtpOjE1O3M6MTk6IkVuY3ljbG9wYWVkaWEgdGl0bGUiO2k6NDtzOjE1OiJKb3VybmFsIFRpdGxlIDEiO2k6NTtzOjI0OiJKb3VybmFsIFRpdGxlIDIgW0pTSE9SVF0iO2k6ODtzOjE0OiJNYWdhemluZSBUaXRsZSI7aTo5O3M6MTY6Ik1hZ2F6aW5lIFRpdGxlIDIiO2k6NjtzOjE1OiJOZXdzcGFwZXIgVGl0bGUiO2k6NztzOjE3OiJOZXdzcGFwZXIgVGl0bGUgMiI7aToxMztzOjI2OiJUaGVzaXNBYnN0cmFjdEpvdXJuYWxUaXRsZSI7fQ==', 'YToyOntpOjIwO3M6MTY6IkNvbGxlY3Rpb24gVGl0bGUiO2k6MTA7czozMzoiQ29uZmVyZW5jZSBUaXRsZSBbQ29uZlNob3J0VGl0bGVdIjt9', NULL, NULL, 'YTo1OntpOjE7czozOiJrdzEiO2k6MjtzOjM6Imt3MiI7aTozO3M6Mzoia3czIjtpOjQ7czozOiJrdzQiO2k6NTtzOjM6Imt3NSI7fQ==');

-- --------------------------------------------------------

--
-- Table structure for table `wkx_category`
--

CREATE TABLE `wkx_category` (
  `categoryId` int(11) NOT NULL,
  `categoryCategory` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_category`
--

INSERT INTO `wkx_category` (`categoryId`, `categoryCategory`) VALUES
(1, 'General'),
(2, 'Cat1'),
(3, 'Cat2'),
(4, 'Cat3');

-- --------------------------------------------------------

--
-- Table structure for table `wkx_collection`
--

CREATE TABLE `wkx_collection` (
  `collectionId` int(11) NOT NULL,
  `collectionTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `collectionTitleShort` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `collectionType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `collectionDefault` longtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_collection`
--

INSERT INTO `wkx_collection` (`collectionId`, `collectionTitle`, `collectionTitleShort`, `collectionType`, `collectionDefault`) VALUES
(4, 'Journal Title 1', NULL, 'journal', 'YToxOntzOjI0OiJyZXNvdXJjZW1pc2NQZWVyUmV2aWV3ZWQiO3M6MToiWSI7fQ=='),
(5, 'Journal Title 2', 'JSHORT', 'journal', 'YToxOntzOjI0OiJyZXNvdXJjZW1pc2NQZWVyUmV2aWV3ZWQiO3M6MToiTiI7fQ=='),
(6, 'Newspaper Title', NULL, 'newspaper', 'YToxOntzOjE0OiJyZXNvdXJjZUZpZWxkMiI7czo0OiJDaXR5Ijt9'),
(7, 'Newspaper Title 2', NULL, 'newspaper', 'YToyOntzOjE0OiJyZXNvdXJjZUZpZWxkMiI7czo0OiJDaXR5IjtzOjEyOiJyZXNvdXJjZUlzYm4iO3M6MzoiSUQxIjt9'),
(8, 'Magazine Title', NULL, 'magazine', NULL),
(9, 'Magazine Title 2', NULL, 'magazine', NULL),
(10, 'Conference Title', 'ConfShortTitle', 'proceedings', 'YToxMTp7czoxMjoicmVzb3VyY2VJc2JuIjtzOjM6IklEMSI7czoxODoicmVzb3VyY2VtaXNjRmllbGQyIjtzOjE6IjEiO3M6MTg6InJlc291cmNlbWlzY0ZpZWxkNSI7czoxOiIyIjtzOjE4OiJyZXNvdXJjZW1pc2NGaWVsZDMiO3M6MToiMiI7czoxODoicmVzb3VyY2VtaXNjRmllbGQ2IjtzOjE6IjIiO3M6MjQ6InJlc291cmNlbWlzY1BlZXJSZXZpZXdlZCI7czoxOiJZIjtzOjIxOiJyZXNvdXJjZW1pc2NQdWJsaXNoZXIiO3M6MToiNSI7czoxNzoicmVzb3VyY2V5ZWFyWWVhcjIiO3M6OToiQ29uZlllYXIxIjtzOjE3OiJyZXNvdXJjZXllYXJZZWFyMyI7czo5OiJDb25mWWVhcjEiO3M6MTg6InJlc291cmNlbWlzY0ZpZWxkMSI7czoxOiI2IjtzOjE3OiJyZXNvdXJjZXllYXJZZWFyMSI7czo4OiJQdWJZZWFyMyI7fQ=='),
(11, 'Conference Title 2', NULL, 'proceedings', 'YTo4OntzOjE4OiJyZXNvdXJjZW1pc2NGaWVsZDIiO3M6MToiMiI7czoxODoicmVzb3VyY2VtaXNjRmllbGQzIjtzOjE6IjIiO3M6MjQ6InJlc291cmNlbWlzY1BlZXJSZXZpZXdlZCI7czoxOiJZIjtzOjIxOiJyZXNvdXJjZW1pc2NQdWJsaXNoZXIiO3M6MToiOSI7czoxNzoicmVzb3VyY2V5ZWFyWWVhcjIiO3M6OToiQ29uZlllYXIxIjtzOjE4OiJyZXNvdXJjZW1pc2NGaWVsZDUiO3M6MToiNiI7czoxODoicmVzb3VyY2VtaXNjRmllbGQ2IjtzOjE6IjMiO3M6MTc6InJlc291cmNleWVhclllYXIzIjtzOjQ6IjIwMDAiO30='),
(12, 'Conference Title 3', 'ConfShortTitle 3', 'proceedings', 'YToxNTp7czoxNDoicmVzb3VyY2VGaWVsZDEiO3M6MTU6IkNvbmZTZXJpZXNUaXRsZSI7czoxNDoicmVzb3VyY2VGaWVsZDMiO3M6MTM6IkNvbmZTZXJpZXNOdW0iO3M6MTQ6InJlc291cmNlRmllbGQ0IjtzOjEwOiJQcm9jVm9sTnVtIjtzOjEyOiJyZXNvdXJjZUlzYm4iO3M6MzoiaWQ0IjtzOjE4OiJyZXNvdXJjZW1pc2NGaWVsZDIiO3M6MToiMSI7czoxODoicmVzb3VyY2VtaXNjRmllbGQ1IjtzOjE6IjUiO3M6MTg6InJlc291cmNlbWlzY0ZpZWxkMyI7czoxOiIxIjtzOjE4OiJyZXNvdXJjZW1pc2NGaWVsZDYiO3M6MToiMSI7czoyNDoicmVzb3VyY2VtaXNjUGVlclJldmlld2VkIjtzOjE6IlkiO3M6MjE6InJlc291cmNlbWlzY1B1Ymxpc2hlciI7czoxOiI5IjtzOjE4OiJyZXNvdXJjZW1pc2NGaWVsZDEiO3M6MjoiMTAiO3M6MTc6InJlc291cmNleWVhclllYXIxIjtzOjg6IlB1YlllYXI0IjtzOjE3OiJyZXNvdXJjZXllYXJZZWFyMiI7czo5OiJDb25mWWVhcjQiO3M6MTc6InJlc291cmNleWVhclllYXIzIjtzOjk6IkNvbmZZZWFyNCI7czo4OiJjcmVhdG9ycyI7YToyOntzOjE3OiJDcmVhdG9yMl8wX3NlbGVjdCI7czoxOiIzIjtzOjE3OiJDcmVhdG9yMl8xX3NlbGVjdCI7czoyOiIxNCI7fX0='),
(13, 'ThesisAbstractJournalTitle', NULL, 'thesis', 'YToxOntzOjEyOiJyZXNvdXJjZUlzYm4iO3M6MzoiaWQyIjt9'),
(14, 'Encyclopaedia title', 'EncShortTitle', 'web', 'YTo0OntzOjEyOiJyZXNvdXJjZUlzYm4iO3M6MzoiaWQ0IjtzOjIxOiJyZXNvdXJjZW1pc2NQdWJsaXNoZXIiO3M6MToiMyI7czoyNDoicmVzb3VyY2VtaXNjUGVlclJldmlld2VkIjtzOjE6Ik4iO3M6ODoiY3JlYXRvcnMiO2E6MTp7czoxNzoiQ3JlYXRvcjJfMF9zZWxlY3QiO3M6MToiMyI7fX0='),
(15, 'Encyclopaedia title', NULL, 'web', 'YToyOntzOjIxOiJyZXNvdXJjZW1pc2NQdWJsaXNoZXIiO3M6MjoiMTMiO3M6MjQ6InJlc291cmNlbWlzY1BlZXJSZXZpZXdlZCI7czoxOiJZIjt9'),
(16, 'Album Title 1', 'AlbShitle', 'music', 'YTozOntzOjE0OiJyZXNvdXJjZUZpZWxkMiI7czo2OiJNZWRpdW0iO3M6MjE6InJlc291cmNlbWlzY1B1Ymxpc2hlciI7czoyOiIyMSI7czoxNzoicmVzb3VyY2V5ZWFyWWVhcjEiO3M6ODoiUHViWWVhcjIiO30='),
(17, 'Album Title 2', NULL, 'music', 'YTozOntzOjE0OiJyZXNvdXJjZUZpZWxkMiI7czo4OiJNZWRpdW0gMiI7czoyMToicmVzb3VyY2VtaXNjUHVibGlzaGVyIjtzOjI6IjIwIjtzOjE3OiJyZXNvdXJjZXllYXJZZWFyMSI7czo4OiJQdWJZZWFyMSI7fQ=='),
(20, 'Collection Title', NULL, 'manuscript', 'YToxOntzOjEyOiJyZXNvdXJjZUlzYm4iO3M6MzoiaWQ1Ijt9'),
(21, 'Collection Titlte 2', 'CollShortTitle', 'manuscript', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_config`
--

CREATE TABLE `wkx_config` (
  `configId` int(11) NOT NULL,
  `configName` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `configInt` int(11) DEFAULT NULL,
  `configVarchar` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `configText` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `configBoolean` tinyint(1) DEFAULT NULL,
  `configDatetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_config`
--

INSERT INTO `wkx_config` (`configId`, `configName`, `configInt`, `configVarchar`, `configText`, `configBoolean`, `configDatetime`) VALUES
(1, 'configTitle', NULL, 'WIKINDX', NULL, NULL, NULL),
(2, 'configContactEmail', NULL, NULL, NULL, NULL, NULL),
(3, 'configDescription', NULL, NULL, NULL, NULL, NULL),
(4, 'configFileDeleteSeconds', 3600, NULL, NULL, NULL, NULL),
(5, 'configPaging', 10, NULL, NULL, NULL, NULL),
(6, 'configPagingMaxLinks', 11, NULL, NULL, NULL, NULL),
(7, 'configStringLimit', 40, NULL, NULL, NULL, NULL),
(8, 'configLanguage', NULL, 'en_GB', NULL, NULL, NULL),
(9, 'configStyle', NULL, 'apa', NULL, NULL, NULL),
(10, 'configTemplate', NULL, 'default', NULL, NULL, NULL),
(11, 'configMultiUser', NULL, NULL, NULL, 1, NULL),
(12, 'configUserRegistration', NULL, NULL, NULL, NULL, NULL),
(13, 'configRegistrationModerate', NULL, NULL, NULL, NULL, NULL),
(14, 'configNotify', NULL, NULL, NULL, 1, NULL),
(15, 'configImgWidthLimit', 400, NULL, NULL, NULL, NULL),
(16, 'configImgHeightLimit', 400, NULL, NULL, NULL, NULL),
(17, 'configFileAttach', NULL, NULL, NULL, 1, NULL),
(18, 'configFileViewLoggedOnOnly', NULL, NULL, NULL, NULL, NULL),
(19, 'configMaxPaste', 10, NULL, NULL, NULL, NULL),
(20, 'configLastChanges', 10, NULL, NULL, NULL, NULL),
(21, 'configLastChangesType', NULL, '1', NULL, NULL, NULL),
(22, 'configLastChangesDayLimit', 100, NULL, NULL, NULL, NULL),
(23, 'configPagingTagCloud', 100, NULL, NULL, NULL, NULL),
(24, 'configImportBib', NULL, NULL, NULL, NULL, NULL),
(25, 'configEmailNews', NULL, NULL, NULL, NULL, NULL),
(26, 'configEmailNewRegistrations', NULL, NULL, NULL, NULL, NULL),
(27, 'configQuarantine', NULL, NULL, NULL, NULL, NULL),
(28, 'configNoSort', NULL, NULL, 'YTozMDp7aTowO3M6MjoiYW4iO2k6MTtzOjE6ImEiO2k6MjtzOjM6InRoZSI7aTozO3M6MzoiZGVyIjtpOjQ7czozOiJkaWUiO2k6NTtzOjM6ImRhcyI7aTo2O3M6MzoiZWluIjtpOjc7czo0OiJlaW5lIjtpOjg7czo1OiJlaW5lciI7aTo5O3M6NToiZWluZXMiO2k6MTA7czoyOiJsZSI7aToxMTtzOjI6ImxhIjtpOjEyO3M6MzoibGFzIjtpOjEzO3M6MjoiaWwiO2k6MTQ7czozOiJsZXMiO2k6MTU7czozOiJ1bmUiO2k6MTY7czoyOiJ1biI7aToxNztzOjM6InVuYSI7aToxODtzOjM6InVubyI7aToxOTtzOjI6ImxvIjtpOjIwO3M6MzoibG9zIjtpOjIxO3M6MToiaSI7aToyMjtzOjM6ImdsaSI7aToyMztzOjI6ImRlIjtpOjI0O3M6MzoiaGV0IjtpOjI1O3M6MjoidW0iO2k6MjY7czozOiJ1bWEiO2k6Mjc7czoxOiJvIjtpOjI4O3M6Mjoib3MiO2k6Mjk7czoyOiJhcyI7fQ==', NULL, NULL),
(29, 'configSearchFilter', NULL, NULL, 'YTo1OntpOjA7czoyOiJhbiI7aToxO3M6MToiYSI7aToyO3M6MzoidGhlIjtpOjM7czozOiJhbmQiO2k6NDtzOjI6InRvIjt9', NULL, NULL),
(30, 'configListlink', NULL, NULL, NULL, NULL, NULL),
(31, 'configEmailStatistics', NULL, NULL, NULL, NULL, NULL),
(32, 'configStatisticsCompiled', NULL, NULL, NULL, NULL, '2020-02-01 00:00:00'),
(33, 'configMetadataAllow', NULL, NULL, NULL, 1, NULL),
(34, 'configMetadataUserOnly', NULL, NULL, NULL, NULL, NULL),
(35, 'configDenyReadOnly', NULL, NULL, NULL, NULL, NULL),
(36, 'configReadOnlyAccess', NULL, NULL, NULL, 1, NULL),
(37, 'configOriginatorEditOnly', NULL, NULL, NULL, NULL, NULL),
(38, 'configGlobalEdit', NULL, NULL, NULL, NULL, NULL),
(39, 'configTimezone', NULL, NULL, 'UTC', NULL, NULL),
(40, 'configRestrictUserId', NULL, NULL, NULL, NULL, NULL),
(41, 'configDeactivateResourceTypes', NULL, NULL, 'YTowOnt9', NULL, NULL),
(42, 'configRssAllow', NULL, NULL, NULL, NULL, NULL),
(43, 'configRssBibstyle', NULL, 'apa', NULL, NULL, NULL),
(44, 'configRssLimit', 10, NULL, NULL, NULL, NULL),
(45, 'configRssDisplay', NULL, NULL, NULL, 1, NULL),
(46, 'configRssTitle', NULL, 'WIKINDX', NULL, NULL, NULL),
(47, 'configRssDescription', NULL, 'My Wikindx', NULL, NULL, NULL),
(49, 'configMailServer', NULL, NULL, NULL, NULL, NULL),
(50, 'configMailFrom', NULL, 'WIKINDX', NULL, NULL, NULL),
(51, 'configMailReplyTo', NULL, 'noreply@noreply.org', NULL, NULL, NULL),
(52, 'configMailReturnPath', NULL, NULL, NULL, NULL, NULL),
(53, 'configMailBackend', NULL, 'smtp', NULL, NULL, NULL),
(54, 'configMailSmPath', NULL, '/usr/sbin/sendmail', NULL, NULL, NULL),
(55, 'configMailSmtpServer', NULL, 'localhost', NULL, NULL, NULL),
(56, 'configMailSmtpPort', 25, NULL, NULL, NULL, NULL),
(57, 'configMailSmtpEncrypt', NULL, NULL, NULL, NULL, NULL),
(58, 'configMailSmtpPersist', NULL, NULL, NULL, NULL, NULL),
(59, 'configMailSmtpAuth', NULL, NULL, NULL, NULL, NULL),
(60, 'configMailSmtpUsername', NULL, NULL, NULL, NULL, NULL),
(61, 'configMailSmtpPassword', NULL, NULL, NULL, NULL, NULL),
(62, 'configGsAllow', NULL, NULL, NULL, NULL, NULL),
(63, 'configGsAttachment', NULL, NULL, NULL, 1, NULL),
(64, 'configCmsAllow', NULL, NULL, NULL, 1, NULL),
(65, 'configCmsBibstyle', NULL, 'apa', NULL, NULL, NULL),
(67, 'configCmsSql', NULL, NULL, NULL, NULL, NULL),
(68, 'configCmsDbUser', NULL, NULL, NULL, NULL, NULL),
(69, 'configCmsDbPassword', NULL, NULL, NULL, NULL, NULL),
(70, 'configTagLowColour', NULL, 'a0a0a0', NULL, NULL, NULL),
(71, 'configTagHighColour', NULL, 'ff0000', NULL, NULL, NULL),
(72, 'configTagLowFactor', 100, NULL, NULL, NULL, NULL),
(73, 'configTagHighFactor', 200, NULL, NULL, NULL, NULL),
(74, 'configImagesAllow', NULL, NULL, NULL, NULL, NULL),
(75, 'configImagesMaxSize', 5, NULL, NULL, NULL, NULL),
(76, 'configErrorReport', NULL, NULL, NULL, NULL, NULL),
(77, 'configSqlEmail', NULL, NULL, NULL, NULL, NULL),
(78, 'configPrintSql', NULL, NULL, NULL, NULL, NULL),
(79, 'configSqlErrorOutput', NULL, 'printSql', NULL, NULL, NULL),
(80, 'configBypassSmartyCompile', NULL, NULL, NULL, NULL, NULL),
(81, 'configDisplayStatistics', NULL, NULL, NULL, NULL, NULL),
(82, 'configDisplayUserStatistics', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_creator`
--

CREATE TABLE `wkx_creator` (
  `creatorId` int(11) NOT NULL,
  `creatorSurname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorFirstname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorInitials` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorPrefix` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorSameAs` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_creator`
--

INSERT INTO `wkx_creator` (`creatorId`, `creatorSurname`, `creatorFirstname`, `creatorInitials`, `creatorPrefix`, `creatorSameAs`) VALUES
(1, 'AuthorLast1', 'Firstname', 'I N I T', 'de', NULL),
(2, 'AuthorLast2', 'Firstname', NULL, 'De', NULL),
(3, 'EditorLast1', 'Firstname', NULL, NULL, NULL),
(4, 'EditorLast2', NULL, 'I N I T', 'von', NULL),
(5, 'TranslatorLast1', 'Firstname', 'I N I T', NULL, NULL),
(6, 'TranslatorLast1', NULL, NULL, NULL, NULL),
(7, 'ReviserLast1', 'Firstname', NULL, NULL, NULL),
(8, 'ReviserLast2', 'Firstname', 'I N I T', NULL, NULL),
(9, 'SeriesEditorLast1', 'Firstname', 'I N I T', NULL, NULL),
(10, 'SeriesEditorLast2', 'Firstname', NULL, NULL, NULL),
(11, 'AuthorLast3', 'First-Name', NULL, NULL, NULL),
(12, 'AuthorLast4', 'Firstname', NULL, NULL, NULL),
(13, 'LastName', 'First-Name', NULL, NULL, NULL),
(14, 'EditorLast3', 'Firstname', NULL, NULL, NULL),
(15, 'Director 1', 'Firstname', NULL, NULL, NULL),
(16, 'Producer 1', 'Firsname', NULL, NULL, NULL),
(17, 'Director 2', 'Firstname', NULL, NULL, NULL),
(18, 'Producer 2', 'Firsname', NULL, NULL, NULL),
(20, 'Producer 2', 'Firstname', 'I N I T', 'de', NULL),
(21, 'Director 2', 'First-Name', NULL, NULL, NULL),
(22, 'Producer 1', 'Firstname', NULL, 'De', NULL),
(23, 'Performer 1', NULL, 'I M', 'von', NULL),
(24, 'Performer 2', 'Firstname', NULL, NULL, NULL),
(25, 'Performer 3', 'Firstname', NULL, NULL, NULL),
(26, 'Composer 1', 'Firstname', NULL, 'da', NULL),
(27, 'Conductor 1', 'Firstname', NULL, NULL, NULL),
(28, 'Composer 2', 'Firstname', NULL, NULL, NULL),
(29, 'Artiste', 'Firstname', NULL, 'von', NULL),
(30, 'Artiste2', 'Firstname', NULL, NULL, NULL),
(41, 'Counsel 1', 'Firstname', 'H', NULL, NULL),
(42, 'Counsel 2', 'Firstname', NULL, 'de', NULL),
(43, 'Inventor 1', 'Firstname', NULL, NULL, NULL),
(44, 'IssuingOrganization 1', NULL, NULL, NULL, NULL),
(45, 'Attorney 1', 'Firstname', NULL, 'de', NULL),
(46, 'IntAuthor 1', 'Firstname', NULL, NULL, NULL),
(47, 'Inventor 2', 'Firstname', NULL, NULL, NULL),
(48, 'IntAuthor 2', 'Firstname', NULL, NULL, NULL),
(49, 'Recipient', 'Firsname', NULL, NULL, NULL),
(50, 'Recipient', 'Firstname', NULL, 'van', NULL),
(51, 'Attributee', 'Firstname', NULL, NULL, NULL),
(52, 'Cartographer', 'Firstname', NULL, NULL, NULL),
(53, 'Creator 1', 'Firstname', NULL, NULL, NULL),
(54, 'Creator 2', 'Firstname', NULL, 'de', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_custom`
--

CREATE TABLE `wkx_custom` (
  `customId` int(11) NOT NULL,
  `customLabel` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `customSize` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'S'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_database_summary`
--

CREATE TABLE `wkx_database_summary` (
  `databasesummaryTotalResources` int(11) NOT NULL,
  `databasesummaryTotalQuotes` int(11) DEFAULT NULL,
  `databasesummaryTotalParaphrases` int(11) DEFAULT NULL,
  `databasesummaryTotalMusings` int(11) DEFAULT NULL,
  `databasesummarySoftwareVersion` varchar(16) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_database_summary`
--

INSERT INTO `wkx_database_summary` (`databasesummaryTotalResources`, `databasesummaryTotalQuotes`, `databasesummaryTotalParaphrases`, `databasesummaryTotalMusings`, `databasesummarySoftwareVersion`) VALUES
(83, 8, 4, 4, '10');

-- --------------------------------------------------------

--
-- Table structure for table `wkx_import_raw`
--

CREATE TABLE `wkx_import_raw` (
  `importrawId` int(11) NOT NULL,
  `importrawStringId` int(11) DEFAULT NULL,
  `importrawText` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `importrawImportType` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_keyword`
--

CREATE TABLE `wkx_keyword` (
  `keywordId` int(11) NOT NULL,
  `keywordKeyword` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `keywordGlossary` mediumtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_keyword`
--

INSERT INTO `wkx_keyword` (`keywordId`, `keywordKeyword`, `keywordGlossary`) VALUES
(1, 'kw1', NULL),
(2, 'kw2', NULL),
(3, 'kw3', NULL),
(4, 'kw4', NULL),
(5, 'kw5', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_language`
--

CREATE TABLE `wkx_language` (
  `languageId` int(11) NOT NULL,
  `languageLanguage` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_news`
--

CREATE TABLE `wkx_news` (
  `newsId` int(11) NOT NULL,
  `newsTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `newsNews` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `newsTimestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `newsEmailSent` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_plugin_soundexplorer`
--

CREATE TABLE `wkx_plugin_soundexplorer` (
  `pluginsoundexplorerId` int(11) NOT NULL,
  `pluginsoundexplorerUserId` int(11) NOT NULL,
  `pluginsoundexplorerLabel` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pluginsoundexplorerArray` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_plugin_wordprocessor`
--

CREATE TABLE `wkx_plugin_wordprocessor` (
  `pluginwordprocessorId` int(11) NOT NULL,
  `pluginwordprocessorHashFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `pluginwordprocessorFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `pluginwordprocessorUserId` int(11) NOT NULL,
  `pluginwordprocessorTimestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_publisher`
--

CREATE TABLE `wkx_publisher` (
  `publisherId` int(11) NOT NULL,
  `publisherName` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `publisherLocation` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `publisherType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_publisher`
--

INSERT INTO `wkx_publisher` (`publisherId`, `publisherName`, `publisherLocation`, `publisherType`) VALUES
(1, 'PublisherName1', 'PublisherLocation1', NULL),
(2, 'TransPublisherName1', 'TransPublisherLocation1', 'book'),
(3, 'PublisherName2', 'PublisherLocation2', NULL),
(4, 'TransPublisherName2', 'TransPublisherLocation2', 'book'),
(5, 'Conference Organizer', 'Conference Location', 'conference'),
(6, 'Conference Publisher Name', 'Conference Publisher Location', 'conference'),
(7, 'Conference Organizer 2', 'Conference Location 2', 'conference'),
(8, 'Conference Publisher Name 2', 'Conference Publisher Location 2', 'conference'),
(9, 'Conference Organizer 3', 'Conference Location 3', 'conference'),
(10, 'Conference Publisher 3', 'Conference Publisher Location 3', 'conference'),
(11, 'Institution', 'InstitutionLocation', NULL),
(12, 'Institution2', 'InstitutionLocation2', NULL),
(13, 'Publisher 6', 'Publisher Location 6', NULL),
(14, 'PublisherName7', 'PublisherLocation7', NULL),
(15, 'PublisherName8', 'PublisherLocation8', NULL),
(16, 'Distributor 1', NULL, 'distributor'),
(17, 'Distributor 2', NULL, 'distributor'),
(18, 'Broadcast Channel Name 1', 'Broadcast Channel Location 1', 'distributor'),
(19, 'Broadcast Channel Name 2', 'Broadcast Channel Location 2', 'distributor'),
(20, 'Record Label Name', NULL, 'music'),
(21, 'Record Label Name 2', NULL, 'music'),
(22, 'Hearing', 'Hearing Location', 'legal'),
(23, 'Hearing', 'Hearing Location 2', 'legal'),
(24, 'Court 1', NULL, 'legal'),
(25, 'Court 2', NULL, 'legal'),
(26, 'Legislative Body 3', 'LegBody Location 3', 'legal'),
(27, 'Legislative Body 4', 'LegBody Location 4', 'legal'),
(28, 'Assignee', 'Assignee Location', 'legal'),
(29, 'Assignee 2', 'Assignee Location 2', 'legal'),
(30, 'Publisher 9', 'PublisherLocation9', 'chart');

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource`
--

CREATE TABLE `wkx_resource` (
  `resourceId` int(11) NOT NULL,
  `resourceType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTitle` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourceSubtitle` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourceShortTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTransTitle` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourceTransSubtitle` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourceTransShortTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField4` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField5` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField6` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField7` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField8` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField9` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceNoSort` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourceTransNoSort` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourceIsbn` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceBibtexKey` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceDoi` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTitleSort` mediumtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource`
--

INSERT INTO `wkx_resource` (`resourceId`, `resourceType`, `resourceTitle`, `resourceSubtitle`, `resourceShortTitle`, `resourceTransTitle`, `resourceTransSubtitle`, `resourceTransShortTitle`, `resourceField1`, `resourceField2`, `resourceField3`, `resourceField4`, `resourceField5`, `resourceField6`, `resourceField7`, `resourceField8`, `resourceField9`, `resourceNoSort`, `resourceTransNoSort`, `resourceIsbn`, `resourceBibtexKey`, `resourceDoi`, `resourceTitleSort`) VALUES
(1, 'book', 'Book Title 1', 'Subtitle', 'Short title', 'TransTitle1', 'TransSubTitle', NULL, 'Series Title 1', 'Ed1', 'SeriesNum', '2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ID1', 'deAuthorLast1PubYear1', 'doi1', 'Book Title 1 Subtitle'),
(2, 'book', 'Book Title 2', 'Subtitle', 'Short title', NULL, NULL, NULL, 'Series Title 2', 'Ed2', 'SeriesNum', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast3PubYear2', NULL, 'Book Title 2 Subtitle'),
(3, 'book_article', 'Book Article Title 1', 'Subtitle', 'Short title', 'TransTitle2', 'TransSubTitle2', NULL, 'Series Title 2', 'Ed1', 'SeriesNum', '2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id2', 'AuthorLast3PubYear1', 'doi2', 'Book Article Title 1 Subtitle'),
(4, 'book_article', 'Book Article Title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast4', NULL, 'Book Article Title 2'),
(5, 'book_chapter', '1', 'Subtitle', 'Short title', 'TransTitle2', 'TransSubTitle2', NULL, 'Series Title 2', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2PubYear1', NULL, '1 Subtitle'),
(6, 'book_chapter', '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ID1', 'deAuthorLast1PubYear1a', 'doi2', '5'),
(7, 'journal_article', 'Journal Article Title', NULL, NULL, NULL, NULL, NULL, 'Vol1', 'Iss1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast4PubYear1', 'doi1', 'Journal Article Title'),
(8, 'journal_article', 'Journal Article Title 2', 'Subtitle', NULL, NULL, NULL, NULL, 'Vol2', 'Iss2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'LastNamePubYear3', NULL, 'Journal Article Title 2 Subtitle'),
(9, 'newspaper_article', 'Newspaper Article Title 1', NULL, NULL, NULL, NULL, NULL, 'Section', 'City', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'deAuthorLast1PubYear1b', NULL, 'Newspaper Article Title 1'),
(10, 'newspaper_article', 'Newspaper Article Title 2', NULL, NULL, NULL, NULL, NULL, 'Section', 'City', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ID1', 'AuthorLast3PubYear3', NULL, 'Newspaper Article Title 2'),
(11, 'magazine_article', 'Magazine Article Title 1', NULL, NULL, NULL, NULL, NULL, 'Ed1', 'ArticleType', 'Iss4', 'Vol10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2PubYear1a', NULL, 'Magazine Article Title 1'),
(12, 'magazine_article', 'Magazine Article Title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast4PubYear1b', NULL, 'Magazine Article Title 2'),
(13, 'proceedings', 'Proceedings Title', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'EditorLast1PubYear1', NULL, 'Proceedings Title'),
(14, 'proceedings', 'Proceedings Title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'vonEditorLast2PubYear4', NULL, 'Proceedings Title 2'),
(15, 'conference_paper', 'Conference Paper Title 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ID1', 'deAuthorLast1', 'doi2', 'Conference Paper Title 1'),
(16, 'conference_paper', 'Conference Paper Title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast3', NULL, 'Conference Paper Title 2'),
(17, 'proceedings_article', 'Proceedings article Title', NULL, NULL, NULL, NULL, NULL, 'ConfSeriesTitle', NULL, 'ConfSeriesNum', 'ProcVolNum', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id4', 'deAuthorLast1PubYear4', 'doi4', 'Proceedings article Title'),
(18, 'proceedings_article', 'Proceedings article Title 2', NULL, 'ProcShortTitle2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2PubYear3', NULL, 'Proceedings article Title 2'),
(19, 'thesis', 'Thesis Title 1', NULL, NULL, NULL, NULL, NULL, 'PhD', 'thesis', 'JourVolNum2', 'JournIssNum2', 'Department', NULL, NULL, NULL, NULL, NULL, NULL, 'id2', 'AuthorLast4ThesisYear1', NULL, 'Thesis Title 1'),
(20, 'thesis', 'Thesis Title 2', NULL, NULL, NULL, NULL, NULL, 'Masters', 'Dissertation', NULL, NULL, 'Department2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2ThesisYear2', NULL, 'Thesis Title 2'),
(21, 'web_site', 'Web Site Title 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id3', 'deAuthorLast1PubYear3', 'doi3', 'Web Site Title 1'),
(22, 'web_site', 'Web Site Title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2PubYear2', NULL, 'Web Site Title 2'),
(23, 'web_article', 'Web Article Title 1', NULL, NULL, NULL, NULL, NULL, 'JouVolNum', 'JourIssNum', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id2', 'deAuthorLast1PubYear3a', 'doi3', 'Web Article Title 1'),
(24, 'web_article', 'Web Article Title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'EditorLast3', NULL, 'Web Article Title 2'),
(25, 'web_encyclopedia', 'Web Encyclopaedia Title 1', NULL, NULL, NULL, NULL, NULL, NULL, 'Ed2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast3PubYear2a', NULL, 'Web Encyclopaedia Title 1'),
(26, 'web_encyclopedia', 'Web Encyclopaedia Title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast4PubYear1a', NULL, 'Web Encyclopaedia Title 2'),
(27, 'web_encyclopedia_article', 'Web Encyclopaedia Article Title 1', NULL, NULL, NULL, NULL, NULL, NULL, 'Ed2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id4', 'DeAuthorLast2PubYear2a', 'doi3', 'Web Encyclopaedia Article Title 1'),
(28, 'web_encyclopedia_article', 'Web Encyclopaedia Article Title 2', 'WebEncArtSubTitle', 'WebEncArtShortTitle', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast3PubYear1a', NULL, 'Web Encyclopaedia Article Title 2 WebEncArtSubTitle'),
(29, 'database', 'Online Database Title 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast4PubYear7', NULL, 'Online Database Title 1'),
(30, 'database', 'Online Database Title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'anon', NULL, 'Online Database Title 2'),
(31, 'film', 'Film Title 1', NULL, NULL, NULL, NULL, NULL, 'Country 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Director2PubYear3', NULL, 'Film Title 1'),
(32, 'film', 'Film Title 2', NULL, NULL, NULL, NULL, NULL, 'Country 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Director1PubYear7', NULL, 'Film Title 2'),
(33, 'broadcast', 'Broadcast title 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Director1Broadcastyear1', NULL, 'Broadcast title 1'),
(34, 'broadcast', 'Broadcast title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Director2Broadcastyear2', NULL, 'Broadcast title 2'),
(35, 'music_album', 'Music Album 1', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'vonPerformer1PubYear3', NULL, 'Music Album 1'),
(36, 'music_album', 'Music Album 2', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'vonPerformer1PubYear1', NULL, 'Music Album 2'),
(37, 'music_track', 'Music Track', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'vonPerformer1PubYear2', NULL, 'Music Track'),
(38, 'music_track', 'Music Track 2', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Performer2PubYear1', NULL, 'Music Track 2'),
(39, 'music_score', 'Music Score Title 1', 'Subtitle', 'ScoreShortTitle', NULL, NULL, NULL, NULL, NULL, 'Ed2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Composer2PubYear1', NULL, 'Music Score Title 1 Subtitle'),
(40, 'music_score', 'Music Score Title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Composer2PubYear4', NULL, 'Music Score Title 2'),
(41, 'artwork', 'Artwork Title', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'vonArtistePubYear4', NULL, 'Artwork Title'),
(42, 'artwork', 'Artwork Title 2', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Artiste2PubYear1', NULL, 'Artwork Title 2'),
(43, 'software', 'Software title 1', NULL, NULL, NULL, NULL, NULL, NULL, 'TypeOfSoftware 1', NULL, 'Version 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'deAuthorLast1PubYear1c', NULL, 'Software title 1'),
(44, 'software', 'Software title 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2PubYear3a', NULL, 'Software title 2'),
(45, 'audiovisual', 'Audiovisual title 1', NULL, NULL, NULL, NULL, NULL, 'AudioVisualSeriesTitle', 'Medium 2', 'Ed3', 'SeriesNum', '1', NULL, NULL, NULL, NULL, NULL, NULL, 'id4', 'AuthorLast3PubYear3a', 'doi3', 'Audiovisual title 1'),
(46, 'audiovisual', 'Audiovisual title 2', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2PubYear7', NULL, 'Audiovisual title 2'),
(62, 'government_report', 'Government Report Title 1', NULL, NULL, NULL, NULL, NULL, 'Secton 1', 'Department 1', 'Edition', NULL, 'Issue Number', NULL, NULL, NULL, NULL, NULL, NULL, 'id2', 'deAuthorLast1PubYear1d', 'doi3', 'Government Report Title 1'),
(63, 'government_report', 'Government Report Title 2', NULL, NULL, NULL, NULL, NULL, NULL, 'Department 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast4PubYear7a', NULL, 'Government Report Title 2'),
(64, 'report', 'Report 1', NULL, NULL, NULL, NULL, NULL, 'ReportSeriesTitle 1', 'TypeOfReport', NULL, NULL, 'Issue Number 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'deAuthorLast1PubYear1e', NULL, 'Report 1'),
(65, 'report', 'Report 2', NULL, NULL, NULL, NULL, NULL, 'ReportSeriesTitle 3', 'TypeOfReport 3', NULL, NULL, 'Issue Number 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast3PubYear3b', NULL, 'Report 2'),
(66, 'hearing', 'Hearing 1', 'Hearing Subtitle', 'HearShTit', NULL, NULL, NULL, 'Committee', 'Legislative Body', 'Session', 'DocNo. 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id4', 'anon2001', NULL, 'Hearing 1 Hearing Subtitle'),
(67, 'hearing', 'Hearing 2', NULL, NULL, NULL, NULL, NULL, 'Committee 2', 'Legislative Body 2', 'Session 2', 'DocNo. 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'anon2011', 'doi3', 'Hearing 2'),
(68, 'statute', 'Statute 1', NULL, NULL, NULL, NULL, NULL, 'Public Law No.', 'Code 1', 'Session 3', 'Section 2', 'Code No. 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'anon2000', NULL, 'Statute 1'),
(69, 'statute', 'Statute 2', NULL, NULL, NULL, NULL, NULL, 'Public Law No. 2', 'Code 2', 'Session 2', 'Section 1', 'Code No. 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'anon1896', NULL, 'Statute 2'),
(70, 'legal_ruling', 'Legal Rule 1', NULL, NULL, NULL, NULL, NULL, 'Secton 1', 'TypeOfRuling 1', 'Edition', 'RuleNumber 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast42000', NULL, 'Legal Rule 1'),
(71, 'legal_ruling', 'Legal Rule 2', NULL, NULL, NULL, NULL, NULL, 'Secton 2', 'TypeOfRuling 2', 'Edition 2', 'RuleNumber 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id3', 'AuthorLast31896', 'doi1', 'Legal Rule 2'),
(72, 'case', 'Legal Case 1', NULL, NULL, NULL, NULL, NULL, 'Reporter 1', NULL, NULL, 'Reporter Volume 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'anon1769', NULL, 'Legal Case 1'),
(73, 'case', 'Legal Case 2', NULL, NULL, NULL, NULL, NULL, 'Reporter 2', NULL, NULL, 'Reporter Volume 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'anon2010', 'doi3', 'Legal Case 2'),
(74, 'bill', 'Bill 1', NULL, NULL, NULL, NULL, NULL, 'Section 2', 'Code 1', 'CodeVol 2', 'Session 3', 'BillNo. 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'anonSessYear', NULL, 'Bill 1'),
(75, 'bill', 'Bill 2', NULL, NULL, NULL, NULL, NULL, 'Section 1', 'Code 1', 'CodeVol 1', 'Session 4', 'BillNo. 6', NULL, NULL, NULL, NULL, NULL, NULL, 'id4', 'anonSessYeara', 'doi4', 'Bill 2'),
(76, 'patent', 'Patent 1', NULL, NULL, NULL, NULL, NULL, 'PublishedSource 1', 'PatentVersionNo. 1', 'ApplicationNo. 1', 'PatentType 1', 'IntPatentNo. 1', 'IntPatentTitle 1', 'IntPatentClass. 1', 'PatentNo. 1', 'LegalStatus', NULL, NULL, NULL, 'Inventor11999', 'doi3', 'Patent 1'),
(77, 'patent', 'Patent 2', NULL, NULL, NULL, NULL, NULL, 'PublishedSource 2', 'PatentVersionNo. 2', 'ApplicationNo. 2', 'PatentType 2', 'IntPatentNo. 2', 'IntPatentTitle 2', 'IntPatentClass. 2', 'PatentNo. 2', 'LegalStatus 2', NULL, NULL, 'id5', 'Inventor11885', 'doi5', 'Patent 2'),
(78, 'personal', 'Personal Communication 1', NULL, NULL, NULL, NULL, NULL, NULL, 'Letter', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'deAuthorLast11876', NULL, 'Personal Communication 1'),
(79, 'personal', 'Personal Communication 2', NULL, NULL, NULL, NULL, NULL, NULL, 'Email', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast42005', NULL, 'Personal Communication 2'),
(80, 'unpublished', 'Unpublished Work 1', NULL, NULL, NULL, NULL, NULL, NULL, 'TypeOfWork 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast22009', NULL, 'Unpublished Work 1'),
(81, 'unpublished', 'Unpublished Work 2', NULL, NULL, NULL, NULL, NULL, NULL, 'TypeOfWork 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id5', 'anon1788', NULL, 'Unpublished Work 2'),
(82, 'classical', 'Classical Work 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'IX', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id2', 'AttributeeBC 201', NULL, 'Classical Work 1'),
(83, 'classical', 'Classical Work 2', 'Subtitle', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XIX', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Attributee313 AD', 'doi3', 'Classical Work 2 Subtitle'),
(84, 'manuscript', 'Manuscript 1', NULL, NULL, NULL, NULL, NULL, NULL, 'ManuscriptType', 'ManuscriptNo.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AuthorLast31301', 'doi1', 'Manuscript 1'),
(85, 'manuscript', 'Manuscript 2', NULL, NULL, NULL, NULL, NULL, NULL, 'ManuscriptType 2', 'ManuscriptNo. 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast21239', NULL, 'Manuscript 2'),
(86, 'map', 'Map 1', NULL, NULL, NULL, NULL, NULL, 'Series Title 1', 'MapType 1', 'Edition 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CartographerPubYear', NULL, 'Map 1'),
(87, 'map', 'Map 2', NULL, NULL, NULL, NULL, NULL, 'Series Title 2', 'MapType 2', 'Edition 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id4', 'CartographerPubYear3', NULL, 'Map 2'),
(88, 'chart', 'Chart 1', NULL, NULL, NULL, NULL, NULL, 'FileName', 'ImageProgram', 'Image Size', 'Image Type', 'Version', 'Number', NULL, NULL, NULL, NULL, NULL, NULL, 'Creator1PubYear2', 'doi1', 'Chart 1'),
(89, 'chart', 'Chart 2', NULL, NULL, NULL, NULL, NULL, 'FileName2', 'ImageProgram2', 'Image Size2', 'Image Type2', 'Version2', 'Number2', NULL, NULL, NULL, NULL, NULL, NULL, 'deCreator2PubYear1', NULL, 'Chart 2'),
(90, 'miscellaneous', 'Miscellaneous 1', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2PubYear7a', NULL, 'Miscellaneous 1'),
(91, 'miscellaneous', 'Miscellaneous 2', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2PubYear3b', NULL, 'Miscellaneous 2'),
(92, 'miscellaneous_section', 'Miscellaneous Section 1', NULL, NULL, NULL, NULL, NULL, NULL, 'Medium 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'deAuthorLast1PubYear7', NULL, 'Miscellaneous Section 1'),
(93, 'miscellaneous_section', 'Miscellaneous Section 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id5', 'anonPubYear2', 'doi5', 'Miscellaneous Section 2'),
(94, 'book', 'Book Title 4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'deAuthorLast1PubYear1f', NULL, 'Book Title 4'),
(95, 'conference_paper', 'Conference Paper 4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2', NULL, 'Conference Paper 4'),
(96, 'web_site', 'Web Site Title 4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id4', 'AuthorLast4PubYear1c', 'doi5', 'Web Site Title 4'),
(97, 'music_score', 'Music Score Title 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Edition 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'id5', 'Composer2PubYear', NULL, 'Music Score Title 3'),
(98, 'classical', 'Classical Work 4', 'Subtitle', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XII', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DeAuthorLast2PubYear3c', NULL, 'Classical Work 4 Subtitle');

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_attachments`
--

CREATE TABLE `wkx_resource_attachments` (
  `resourceattachmentsId` int(11) NOT NULL,
  `resourceattachmentsHashFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsResourceId` int(11) DEFAULT NULL,
  `resourceattachmentsFileName` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileSize` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsDownloads` int(11) DEFAULT '0',
  `resourceattachmentsDownloadsPeriod` int(11) DEFAULT '0',
  `resourceattachmentsPrimary` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourceattachmentsTimestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `resourceattachmentsEmbargo` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourceattachmentsEmbargoUntil` datetime DEFAULT CURRENT_TIMESTAMP,
  `resourceattachmentsDescription` mediumtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_category`
--

CREATE TABLE `wkx_resource_category` (
  `resourcecategoryId` int(11) NOT NULL,
  `resourcecategoryResourceId` int(11) DEFAULT NULL,
  `resourcecategoryCategoryId` int(11) DEFAULT NULL,
  `resourcecategorySubcategoryId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_category`
--

INSERT INTO `wkx_resource_category` (`resourcecategoryId`, `resourcecategoryResourceId`, `resourcecategoryCategoryId`, `resourcecategorySubcategoryId`) VALUES
(1, 1, 1, NULL),
(2, 2, 2, NULL),
(3, 3, 1, NULL),
(4, 4, 2, NULL),
(5, 5, 2, NULL),
(6, 6, 1, NULL),
(7, 7, 1, NULL),
(8, 8, 1, NULL),
(9, 9, 1, NULL),
(10, 9, 2, NULL),
(11, 10, 1, NULL),
(12, 11, 1, NULL),
(13, 12, 2, NULL),
(14, 13, 1, NULL),
(15, 14, 1, NULL),
(16, 15, 2, NULL),
(17, 16, 1, NULL),
(18, 17, 1, NULL),
(19, 18, 3, NULL),
(20, 19, 1, NULL),
(21, 20, 2, NULL),
(22, 21, 2, NULL),
(23, 21, 3, NULL),
(24, 22, 1, NULL),
(25, 23, 3, NULL),
(26, 24, 1, NULL),
(27, 25, 1, NULL),
(28, 26, 2, NULL),
(29, 26, 3, NULL),
(30, 27, 2, NULL),
(31, 28, 1, NULL),
(32, 29, 1, NULL),
(33, 30, 2, NULL),
(34, 30, 3, NULL),
(35, 31, 3, NULL),
(36, 32, 2, NULL),
(37, 33, 1, NULL),
(38, 34, 1, NULL),
(39, 35, 1, NULL),
(40, 36, 1, NULL),
(41, 37, 1, NULL),
(42, 38, 1, NULL),
(43, 38, 2, NULL),
(44, 38, 3, NULL),
(45, 39, 1, NULL),
(46, 40, 1, NULL),
(47, 41, 1, NULL),
(48, 42, 3, NULL),
(49, 43, 1, NULL),
(50, 44, 1, NULL),
(51, 45, 3, NULL),
(52, 46, 1, NULL),
(53, 62, 3, NULL),
(54, 63, 1, NULL),
(55, 64, 1, NULL),
(56, 65, 1, NULL),
(57, 66, 2, NULL),
(58, 67, 1, NULL),
(59, 68, 3, NULL),
(60, 69, 4, NULL),
(61, 70, 1, NULL),
(62, 71, 3, NULL),
(63, 71, 4, NULL),
(64, 72, 1, NULL),
(65, 73, 2, NULL),
(66, 74, 3, NULL),
(67, 74, 4, NULL),
(68, 75, 1, NULL),
(69, 76, 1, NULL),
(70, 77, 1, NULL),
(71, 77, 4, NULL),
(72, 78, 1, NULL),
(73, 79, 1, NULL),
(74, 80, 1, NULL),
(75, 81, 3, NULL),
(76, 82, 1, NULL),
(77, 83, 1, NULL),
(78, 84, 1, NULL),
(79, 85, 3, NULL),
(80, 85, 4, NULL),
(81, 86, 1, NULL),
(82, 87, 3, NULL),
(83, 88, 1, NULL),
(84, 89, 4, NULL),
(85, 90, 1, NULL),
(86, 91, 1, NULL),
(87, 92, 1, NULL),
(88, 93, 1, NULL),
(89, 93, 4, NULL),
(90, 94, 1, NULL),
(91, 95, 1, NULL),
(92, 96, 4, NULL),
(93, 97, 1, NULL),
(94, 97, 2, NULL),
(95, 97, 4, NULL),
(96, 98, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_creator`
--

CREATE TABLE `wkx_resource_creator` (
  `resourcecreatorId` int(11) NOT NULL,
  `resourcecreatorResourceId` int(11) NOT NULL,
  `resourcecreatorCreatorId` int(11) DEFAULT NULL,
  `resourcecreatorOrder` int(11) DEFAULT NULL,
  `resourcecreatorRole` int(11) DEFAULT NULL,
  `resourcecreatorCreatorMain` int(11) DEFAULT NULL,
  `resourcecreatorCreatorSurname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_creator`
--

INSERT INTO `wkx_resource_creator` (`resourcecreatorId`, `resourcecreatorResourceId`, `resourcecreatorCreatorId`, `resourcecreatorOrder`, `resourcecreatorRole`, `resourcecreatorCreatorMain`, `resourcecreatorCreatorSurname`) VALUES
(1, 1, 1, 1, 1, 1, 'AuthorLast1'),
(2, 1, 2, 2, 1, 1, 'AuthorLast1'),
(3, 1, 3, 1, 2, 1, 'AuthorLast1'),
(4, 1, 4, 2, 2, 1, 'AuthorLast1'),
(5, 1, 5, 1, 3, 1, 'AuthorLast1'),
(6, 1, 6, 2, 3, 1, 'AuthorLast1'),
(7, 1, 7, 1, 4, 1, 'AuthorLast1'),
(8, 1, 8, 2, 4, 1, 'AuthorLast1'),
(9, 1, 9, 1, 5, 1, 'AuthorLast1'),
(10, 1, 10, 2, 5, 1, 'AuthorLast1'),
(11, 2, 11, 1, 1, 11, 'AuthorLast3'),
(12, 3, 11, 1, 1, 11, 'AuthorLast3'),
(13, 3, 10, 1, 5, 11, 'AuthorLast3'),
(14, 4, 12, 1, 1, 12, 'AuthorLast4'),
(15, 5, 2, 1, 1, 2, 'AuthorLast2'),
(16, 5, 3, 1, 2, 2, 'AuthorLast2'),
(17, 5, 4, 2, 2, 2, 'AuthorLast2'),
(18, 6, 1, 1, 1, 1, 'AuthorLast1'),
(19, 6, 12, 2, 1, 1, 'AuthorLast1'),
(20, 7, 12, 1, 1, 12, 'AuthorLast4'),
(21, 8, 13, 1, 1, 13, 'LastName'),
(22, 8, 2, 2, 1, 13, 'LastName'),
(23, 9, 1, 1, 1, 1, 'AuthorLast1'),
(24, 10, 11, 1, 1, 11, 'AuthorLast3'),
(25, 11, 2, 1, 1, 2, 'AuthorLast2'),
(26, 12, 12, 1, 1, 12, 'AuthorLast4'),
(27, 13, 3, 1, 2, 3, 'EditorLast1'),
(28, 14, 4, 1, 2, 4, 'EditorLast2'),
(29, 14, 14, 2, 2, 4, 'EditorLast2'),
(30, 15, 1, 1, 1, 1, 'AuthorLast1'),
(31, 16, 11, 1, 1, 11, 'AuthorLast3'),
(32, 17, 1, 1, 1, 1, 'AuthorLast1'),
(33, 17, 3, 1, 2, 1, 'AuthorLast1'),
(34, 17, 14, 2, 2, 1, 'AuthorLast1'),
(35, 18, 2, 1, 1, 2, 'AuthorLast2'),
(36, 18, 11, 2, 1, 2, 'AuthorLast2'),
(37, 19, 12, 1, 1, 12, 'AuthorLast4'),
(38, 20, 2, 1, 1, 2, 'AuthorLast2'),
(39, 21, 1, 1, 1, 1, 'AuthorLast1'),
(40, 21, 4, 1, 2, 1, 'AuthorLast1'),
(41, 22, 2, 1, 1, 2, 'AuthorLast2'),
(42, 22, 1, 2, 1, 2, 'AuthorLast2'),
(43, 23, 1, 1, 1, 1, 'AuthorLast1'),
(44, 23, 3, 1, 2, 1, 'AuthorLast1'),
(45, 24, 14, 1, 2, 14, 'EditorLast3'),
(46, 25, 11, 1, 1, 11, 'AuthorLast3'),
(47, 25, 2, 2, 1, 11, 'AuthorLast3'),
(48, 26, 12, 1, 1, 12, 'AuthorLast4'),
(49, 27, 2, 1, 1, 2, 'AuthorLast2'),
(50, 27, 3, 1, 2, 2, 'AuthorLast2'),
(51, 28, 11, 1, 1, 11, 'AuthorLast3'),
(52, 28, 1, 2, 1, 11, 'AuthorLast3'),
(53, 29, 12, 1, 1, 12, 'AuthorLast4'),
(54, 31, 21, 1, 1, 21, 'Director 2'),
(55, 31, 22, 1, 2, 21, 'Director 2'),
(56, 32, 15, 1, 1, 15, 'Director 1'),
(57, 32, 20, 1, 2, 15, 'Director 1'),
(58, 33, 15, 1, 1, 15, 'Director 1'),
(59, 33, 16, 1, 2, 15, 'Director 1'),
(60, 34, 17, 1, 1, 17, 'Director 2'),
(61, 34, 18, 1, 2, 17, 'Director 2'),
(62, 35, 23, 1, 1, 23, 'Performer 1'),
(63, 35, 24, 2, 1, 23, 'Performer 1'),
(64, 35, 25, 3, 1, 23, 'Performer 1'),
(65, 35, 26, 1, 2, 23, 'Performer 1'),
(66, 35, 27, 1, 3, 23, 'Performer 1'),
(67, 36, 23, 1, 1, 23, 'Performer 1'),
(68, 36, 25, 2, 1, 23, 'Performer 1'),
(69, 36, 26, 1, 2, 23, 'Performer 1'),
(70, 36, 28, 2, 2, 23, 'Performer 1'),
(71, 37, 23, 1, 1, 23, 'Performer 1'),
(72, 37, 25, 2, 1, 23, 'Performer 1'),
(73, 37, 28, 1, 2, 23, 'Performer 1'),
(74, 37, 27, 1, 3, 23, 'Performer 1'),
(75, 38, 24, 1, 1, 24, 'Performer 2'),
(76, 38, 26, 1, 2, 24, 'Performer 2'),
(77, 39, 28, 1, 1, 28, 'Composer 2'),
(78, 39, 3, 1, 2, 28, 'Composer 2'),
(79, 40, 28, 1, 1, 28, 'Composer 2'),
(80, 40, 26, 2, 1, 28, 'Composer 2'),
(81, 41, 29, 1, 1, 29, 'Artiste'),
(82, 42, 30, 1, 1, 30, 'Artiste2'),
(83, 43, 1, 1, 1, 1, 'AuthorLast1'),
(84, 44, 2, 1, 1, 2, 'AuthorLast2'),
(85, 44, 1, 2, 1, 2, 'AuthorLast2'),
(86, 45, 11, 1, 1, 11, 'AuthorLast3'),
(87, 45, 3, 1, 5, 11, 'AuthorLast3'),
(88, 45, 14, 2, 5, 11, 'AuthorLast3'),
(89, 46, 2, 1, 1, 2, 'AuthorLast2'),
(90, 46, 12, 2, 1, 2, 'AuthorLast2'),
(91, 46, 25, 1, 2, 2, 'AuthorLast2'),
(92, 46, 24, 2, 2, 2, 'AuthorLast2'),
(93, 46, 23, 3, 2, 2, 'AuthorLast2'),
(94, 62, 1, 1, 1, 1, 'AuthorLast1'),
(95, 62, 3, 1, 2, 1, 'AuthorLast1'),
(96, 63, 12, 1, 1, 12, 'AuthorLast4'),
(97, 63, 11, 2, 1, 12, 'AuthorLast4'),
(98, 63, 2, 3, 1, 12, 'AuthorLast4'),
(99, 64, 1, 1, 1, 1, 'AuthorLast1'),
(100, 64, 11, 2, 1, 1, 'AuthorLast1'),
(101, 64, 4, 1, 2, 1, 'AuthorLast1'),
(102, 65, 11, 1, 1, 11, 'AuthorLast3'),
(103, 65, 14, 1, 2, 11, 'AuthorLast3'),
(104, 65, 3, 2, 2, 11, 'AuthorLast3'),
(105, 70, 12, 1, 1, 12, 'AuthorLast4'),
(106, 70, 1, 2, 1, 12, 'AuthorLast4'),
(107, 71, 11, 1, 1, 11, 'AuthorLast3'),
(108, 72, 41, 1, 3, 41, 'Counsel 1'),
(109, 73, 42, 1, 3, 42, 'Counsel 2'),
(110, 76, 43, 1, 1, 43, 'Inventor 1'),
(111, 76, 44, 1, 2, 43, 'Inventor 1'),
(112, 76, 45, 1, 3, 43, 'Inventor 1'),
(113, 76, 46, 1, 4, 43, 'Inventor 1'),
(114, 77, 43, 1, 1, 43, 'Inventor 1'),
(115, 77, 47, 2, 1, 43, 'Inventor 1'),
(116, 77, 44, 1, 2, 43, 'Inventor 1'),
(117, 77, 45, 1, 3, 43, 'Inventor 1'),
(118, 77, 48, 1, 4, 43, 'Inventor 1'),
(119, 78, 1, 1, 1, 1, 'AuthorLast1'),
(120, 78, 49, 1, 2, 1, 'AuthorLast1'),
(121, 79, 12, 1, 1, 12, 'AuthorLast4'),
(122, 79, 50, 1, 2, 12, 'AuthorLast4'),
(123, 80, 2, 1, 1, 2, 'AuthorLast2'),
(124, 80, 11, 2, 1, 2, 'AuthorLast2'),
(125, 82, 51, 1, 1, 51, 'Attributee'),
(126, 83, 51, 1, 1, 51, 'Attributee'),
(127, 84, 11, 1, 1, 11, 'AuthorLast3'),
(128, 85, 2, 1, 1, 2, 'AuthorLast2'),
(129, 86, 52, 1, 1, 52, 'Cartographer'),
(130, 86, 9, 1, 5, 52, 'Cartographer'),
(131, 87, 52, 1, 1, 52, 'Cartographer'),
(132, 87, 10, 1, 5, 52, 'Cartographer'),
(133, 88, 53, 1, 1, 53, 'Creator 1'),
(134, 89, 54, 1, 1, 54, 'Creator 2'),
(135, 90, 2, 1, 1, 2, 'AuthorLast2'),
(136, 91, 2, 1, 1, 2, 'AuthorLast2'),
(137, 91, 11, 2, 1, 2, 'AuthorLast2'),
(138, 91, 12, 3, 1, 2, 'AuthorLast2'),
(139, 92, 1, 1, 1, 1, 'AuthorLast1'),
(140, 94, 1, 1, 1, 1, 'AuthorLast1'),
(141, 94, 8, 1, 4, 1, 'AuthorLast1'),
(142, 95, 2, 1, 1, 2, 'AuthorLast2'),
(143, 96, 12, 1, 1, 12, 'AuthorLast4'),
(144, 96, 14, 1, 2, 12, 'AuthorLast4'),
(145, 97, 28, 1, 1, 28, 'Composer 2'),
(146, 98, 2, 1, 1, 2, 'AuthorLast2'),
(147, 30, NULL, NULL, NULL, NULL, NULL),
(148, 66, NULL, NULL, NULL, NULL, NULL),
(149, 67, NULL, NULL, NULL, NULL, NULL),
(150, 68, NULL, NULL, NULL, NULL, NULL),
(151, 69, NULL, NULL, NULL, NULL, NULL),
(152, 74, NULL, NULL, NULL, NULL, NULL),
(153, 75, NULL, NULL, NULL, NULL, NULL),
(154, 81, NULL, NULL, NULL, NULL, NULL),
(155, 93, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_custom`
--

CREATE TABLE `wkx_resource_custom` (
  `resourcecustomId` int(11) NOT NULL,
  `resourcecustomCustomId` int(11) NOT NULL,
  `resourcecustomResourceId` int(11) NOT NULL,
  `resourcecustomShort` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcecustomLong` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourcecustomAddUserIdCustom` int(11) DEFAULT NULL,
  `resourcecustomEditUserIdCustom` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_keyword`
--

CREATE TABLE `wkx_resource_keyword` (
  `resourcekeywordId` int(11) NOT NULL,
  `resourcekeywordResourceId` int(11) DEFAULT NULL,
  `resourcekeywordKeywordId` int(11) DEFAULT NULL,
  `resourcekeywordMetadataId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_keyword`
--

INSERT INTO `wkx_resource_keyword` (`resourcekeywordId`, `resourcekeywordResourceId`, `resourcekeywordKeywordId`, `resourcekeywordMetadataId`) VALUES
(1, 1, 1, NULL),
(2, 2, 2, NULL),
(3, 2, 1, NULL),
(4, 3, 2, NULL),
(5, 5, 3, NULL),
(6, 6, 1, NULL),
(7, 6, 3, NULL),
(8, 9, 2, NULL),
(9, 10, 2, NULL),
(10, 12, 1, NULL),
(11, 12, 3, NULL),
(12, 15, 2, NULL),
(13, 17, 4, NULL),
(14, 18, 2, NULL),
(15, 18, 4, NULL),
(16, 21, 1, NULL),
(17, 25, 2, NULL),
(18, 25, 4, NULL),
(19, 27, 2, NULL),
(20, 27, 4, NULL),
(21, 29, 2, NULL),
(22, 30, 3, NULL),
(23, 32, 1, NULL),
(24, 32, 3, NULL),
(25, 32, 4, NULL),
(26, 34, 3, NULL),
(27, 31, 2, NULL),
(28, 31, 3, NULL),
(29, 38, 4, NULL),
(30, 42, 1, NULL),
(31, 42, 2, NULL),
(32, 43, 1, NULL),
(33, 62, 1, NULL),
(34, 66, 5, NULL),
(35, 66, 2, NULL),
(36, 66, 3, NULL),
(37, 67, 5, NULL),
(38, 68, 2, NULL),
(39, 71, 2, NULL),
(40, 71, 5, NULL),
(41, 75, 2, NULL),
(42, 75, 4, NULL),
(43, 75, 5, NULL),
(44, 76, 3, NULL),
(45, 77, 1, NULL),
(46, 79, 1, NULL),
(47, 84, 3, NULL),
(48, 85, NULL, NULL),
(49, 85, 4, NULL),
(50, 85, 5, NULL),
(51, 87, 3, NULL),
(52, 93, 1, NULL),
(53, 93, 3, NULL),
(54, 93, 5, NULL),
(55, 96, 1, NULL),
(56, 96, 3, NULL),
(57, 97, 2, NULL),
(58, 97, 3, NULL),
(59, 97, 4, NULL),
(60, 98, 3, NULL),
(61, NULL, 2, 1),
(62, NULL, 5, 2),
(63, NULL, 2, 4),
(64, NULL, 3, 4),
(65, NULL, 2, 7),
(66, NULL, 4, 7),
(67, NULL, 5, 7),
(68, NULL, 3, 8),
(69, NULL, 3, 15),
(70, NULL, 4, 15),
(71, NULL, 3, 16),
(72, NULL, 2, 17),
(73, NULL, 3, 17),
(74, NULL, 1, 18),
(75, NULL, 3, 18),
(76, NULL, 1, 23),
(77, NULL, 3, 23);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_language`
--

CREATE TABLE `wkx_resource_language` (
  `resourcelanguageId` int(11) NOT NULL,
  `resourcelanguageResourceId` int(11) DEFAULT NULL,
  `resourcelanguageLanguageId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_metadata`
--

CREATE TABLE `wkx_resource_metadata` (
  `resourcemetadataId` int(11) NOT NULL,
  `resourcemetadataResourceId` int(11) DEFAULT NULL,
  `resourcemetadataMetadataId` int(11) DEFAULT NULL,
  `resourcemetadataAddUserId` int(11) DEFAULT NULL,
  `resourcemetadataPageStart` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataPageEnd` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataParagraph` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataSection` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataChapter` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataType` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `resourcemetadataPrivate` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourcemetadataText` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `resourcemetadataTimestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `resourcemetadataTimestampEdited` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_metadata`
--

INSERT INTO `wkx_resource_metadata` (`resourcemetadataId`, `resourcemetadataResourceId`, `resourcemetadataMetadataId`, `resourcemetadataAddUserId`, `resourcemetadataPageStart`, `resourcemetadataPageEnd`, `resourcemetadataParagraph`, `resourcemetadataSection`, `resourcemetadataChapter`, `resourcemetadataType`, `resourcemetadataPrivate`, `resourcemetadataText`, `resourcemetadataTimestamp`, `resourcemetadataTimestampEdited`) VALUES
(1, 98, NULL, 2, '4', NULL, NULL, NULL, NULL, 'q', 'N', '\"The OSBib package has two sections which share some common PHP files. Files in the directory format/ will format the bibliography output as described above. Files in the directory create/ will create or edit the XML style files. As supplied in the OSBib package, the create interface is stand-alone and runs via index.php. Users wishing to integrate the creation/editing interface within their bibliographic management system will need to modify or extract various portions of index.php for use in their own PHP code.\"', '2010-12-27 08:15:37', NULL),
(2, 15, NULL, 2, '10', '11', NULL, NULL, NULL, 'q', 'N', 'Return a portion of a UTF-8 string. Where PHP has been compiled with mb_string, mb_substr() will be used.', '2010-12-27 08:15:37', NULL),
(3, 15, NULL, 2, '34', NULL, NULL, NULL, NULL, 'q', 'N', '\"loadStyle() and getStyle() need be called only once so can be outside your process loop.\"', '2010-12-27 08:15:37', NULL),
(4, 68, NULL, 2, '543', NULL, NULL, NULL, NULL, 'q', 'N', '\"With the advent of intellectual and scholarly projects that span the continents, it can be advantageous to have an effective program that allows multiple users access to shared bibliographies. WIKINDX ... is one such program...\"', '2010-12-27 08:15:37', NULL),
(5, 4, NULL, 3, '3', NULL, NULL, NULL, NULL, 'q', 'N', '\"Don\'t do anything on the next page except click on the link at the top called \'localhost\' then click on the privileges link and select \'add a new user\'. \'localhost\' is the name of the local (i.e. not remote) host that WAMP runs with the Apache web server.\"', '2010-12-27 08:15:37', NULL),
(6, 4, NULL, 3, '3', NULL, NULL, NULL, NULL, 'q', 'N', '\"In the field \'User Name\', type in \'wikindx\' and type \'wikindx\' into the two password fields and select \'local\' for host. Don\'t change anything else then click on the \'Go\' button.\"', '2010-12-27 08:15:37', NULL),
(7, 1, NULL, 1, '45', NULL, NULL, NULL, NULL, 'q', 'N', '\"STRING templateEndnote This is the template definition string such as citation|: pages. If \'citation\' exists in the template, then the full bibliographic citation as defined in the bibliography section of OSBib shyould be used and all other fields except \'pages\' should be discarded.\"', '2010-12-27 08:15:37', NULL),
(8, 84, NULL, 1, '76', NULL, NULL, NULL, NULL, 'q', 'N', '\"STRING opCit Replace previously cited resources with this template. If no template is given, the behaviour should follow that of templateEndnote.\"', '2010-12-27 08:15:37', NULL),
(9, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 'qc', 'N', 'This is a comment upon OSBiB.', '2010-12-27 08:15:37', NULL),
(10, NULL, 2, 2, NULL, NULL, NULL, NULL, NULL, 'qc', 'N', 'Another public comment.', '2010-12-27 08:19:28', NULL),
(11, NULL, 3, 2, NULL, NULL, NULL, NULL, NULL, 'qc', 'Y', 'A private comment only for the eyes of user1', '2010-12-27 08:20:06', NULL),
(12, NULL, 5, 3, NULL, NULL, NULL, NULL, NULL, 'qc', 'N', 'Public comment goes here (user2).', '2010-12-27 08:26:20', NULL),
(13, NULL, 7, 1, NULL, NULL, NULL, NULL, NULL, 'qc', 'Y', 'This is a private comment from super.', '2010-12-27 08:39:50', NULL),
(14, NULL, 8, 1, NULL, NULL, NULL, NULL, NULL, 'qc', 'N', 'Super\'s public comment.', '2010-12-27 08:42:46', NULL),
(15, 98, NULL, 2, '5', NULL, NULL, NULL, NULL, 'p', 'N', 'This is not part of the distribution package but is here as an example of how WIKINDX uses OSBib-Format. BIBSTYLE::process() is the loop that parses each bibliographic entry one by one. You are likely to need a similar process loop. Further comments are found in CITESTYLE.php.', '2010-12-27 08:15:37', NULL),
(16, 3, NULL, 3, NULL, NULL, NULL, NULL, NULL, 'p', 'N', 'STRING useInitials If \'Last name only\' is selected above, use initials to differentiate between creators with the same surname The value will be \'on\' for yes otherwise the array element does not exist.', '2010-12-27 08:15:37', NULL),
(17, 3, NULL, 3, '4', NULL, NULL, NULL, NULL, 'p', 'N', 'STRING creatorSepFirstBetween Separator between the first two primary creators in the case where there are more than two.', '2010-12-27 08:15:37', NULL),
(18, 92, NULL, 1, '32', NULL, NULL, NULL, NULL, 'p', 'N', 'Bon voyage!', '2010-12-27 08:15:37', NULL),
(19, NULL, 18, 1, NULL, NULL, NULL, NULL, NULL, 'pc', 'G', 'What does he mean?  This comment is only available to user groups of which super is a member.', '2010-12-27 08:37:17', NULL),
(20, 98, NULL, 2, NULL, NULL, NULL, NULL, NULL, 'm', 'Y', 'What is important here is that the key names of the above array match the key names of the resource type arrays in STYLEMAP. This is how the data from your particular database is mapped to a format that OSBib understands and this is why you must edit the key names of the resource type array in STYLEMAP. The one exception to this is the handling of creator elements (author, editor, composer, inventor etc.) which OSBib expects to be listed as \'creator1\', \'creator2\', \'creator3\', \'creator4\' and \'creator5\' where \'creator1\' is always the primary creator (usually the author). Do not edit these key names.', '2010-12-27 08:16:44', NULL),
(21, 98, NULL, 2, NULL, NULL, NULL, NULL, NULL, 'm', 'N', 'BIBFORMAT expects its data to be in UTF-8 format and will return its formatted data in UTF-8 format. If you need to encode or decode your data prior to or after using OSBib, do not use PHP\'s utf8_encode() and utf8_decode() functions. Use the OSBib functions UTF8::encodeUtf8() and UTF8::decodeUtf8() instead. Additionally, if you need to manipulate UTF-8-encoded strings with functions such as strtolower(), strlen() etc., you should strongly consider using the appropriate methods in the OSBib UTF8 class.', '2010-12-27 08:17:00', NULL),
(22, 15, NULL, 2, NULL, NULL, NULL, '2', NULL, 'm', 'N', 'Bibliographic styles may require the book edition number to be a cardinal or an ordinal number. If your edition number is stored in the database as a cardinal number, then it will be formatted as an ordinal number if required by the bibliographic style. If your edition number is stored as anything other than a cardinal number it will be used unchanged. The conversion is English - i.e. \'3\' => \'3rd\'. This works all the way up to infinity-1 ;-)', '2010-12-27 08:20:40', NULL),
(23, 3, NULL, 3, '4', NULL, '3', NULL, NULL, 'm', 'N', 'Use CITEFORMAT::loadStyle() to load and parse the XML file into usable arrays. The XML file is logically divided into four areas, info (see bibliography_xml), citation (see below), styleCommon (see bibliography_xml) and styleTypes (see bibliography_xml).', '2010-12-27 08:28:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_misc`
--

CREATE TABLE `wkx_resource_misc` (
  `resourcemiscId` int(11) NOT NULL,
  `resourcemiscCollection` int(11) DEFAULT NULL,
  `resourcemiscPublisher` int(11) DEFAULT NULL,
  `resourcemiscField1` int(11) DEFAULT NULL,
  `resourcemiscField2` int(11) DEFAULT NULL,
  `resourcemiscField3` int(11) DEFAULT NULL,
  `resourcemiscField4` int(11) DEFAULT NULL,
  `resourcemiscField5` int(11) DEFAULT NULL,
  `resourcemiscField6` int(11) DEFAULT NULL,
  `resourcemiscTag` int(11) DEFAULT NULL,
  `resourcemiscAddUserIdResource` int(11) DEFAULT NULL,
  `resourcemiscEditUserIdResource` int(11) DEFAULT NULL,
  `resourcemiscAccesses` int(11) DEFAULT '1',
  `resourcemiscMaturityIndex` double DEFAULT '0',
  `resourcemiscPeerReviewed` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourcemiscQuarantine` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourcemiscAccessesPeriod` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_misc`
--

INSERT INTO `wkx_resource_misc` (`resourcemiscId`, `resourcemiscCollection`, `resourcemiscPublisher`, `resourcemiscField1`, `resourcemiscField2`, `resourcemiscField3`, `resourcemiscField4`, `resourcemiscField5`, `resourcemiscField6`, `resourcemiscTag`, `resourcemiscAddUserIdResource`, `resourcemiscEditUserIdResource`, `resourcemiscAccesses`, `resourcemiscMaturityIndex`, `resourcemiscPeerReviewed`, `resourcemiscQuarantine`, `resourcemiscAccessesPeriod`) VALUES
(1, NULL, 1, 2, NULL, NULL, 1, NULL, 100, NULL, 1, NULL, 3, 4.5, 'N', 'N', 0),
(2, NULL, 3, NULL, NULL, NULL, 2, NULL, 100, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(3, 2, 1, 4, NULL, NULL, 1, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(4, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(5, 2, 3, 4, NULL, NULL, 2, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(6, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(7, 4, NULL, NULL, NULL, 1, NULL, NULL, 3, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(8, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(9, 6, NULL, NULL, 4, 3, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(10, 7, NULL, NULL, 6, 6, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(11, 8, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(12, 9, NULL, NULL, 1, 1, NULL, 8, 1, NULL, 1, 1, 1, NULL, 'N', 'N', 0),
(13, NULL, 5, 6, 1, 1, NULL, 2, 1, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(14, NULL, 7, 8, 1, 1, NULL, 2, 1, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(15, 10, 5, NULL, 1, 2, NULL, 2, 2, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(16, 11, 9, NULL, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, 7, NULL, 'Y', 'N', 0),
(17, 12, 9, 10, 1, 1, NULL, 5, 1, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(18, 10, 5, 6, 10, 3, NULL, 13, 3, NULL, 1, NULL, 4, NULL, 'N', 'N', 0),
(19, 13, 11, NULL, NULL, NULL, NULL, NULL, 400, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(20, NULL, 12, NULL, NULL, NULL, NULL, NULL, 200, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(21, NULL, NULL, NULL, 22, 10, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(22, NULL, NULL, NULL, 5, 2, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(23, NULL, 1, NULL, 22, 10, NULL, 1, 2, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(24, NULL, NULL, NULL, 22, 10, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(25, NULL, 3, NULL, 22, 10, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(26, NULL, 13, NULL, 22, 10, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(27, 14, 3, NULL, 3, 11, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(28, 15, 13, NULL, 3, 4, NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 'Y', 'N', 0),
(29, NULL, 14, NULL, 3, 11, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(30, NULL, 15, NULL, 3, 11, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(31, NULL, 16, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 1, 1, NULL, 'N', 'N', 0),
(32, NULL, 17, NULL, NULL, NULL, 2, NULL, NULL, NULL, 1, 1, 1, NULL, 'N', 'N', 0),
(33, NULL, 18, NULL, 3, 6, 1, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(34, NULL, 19, NULL, 11, 12, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(35, NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(36, NULL, 21, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(37, 16, 21, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(38, 17, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(39, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(40, NULL, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(41, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(42, NULL, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 'N', 'N', 0),
(43, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(44, NULL, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(45, NULL, 1, NULL, NULL, NULL, 2, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(46, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(62, NULL, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(63, NULL, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(64, NULL, 12, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(65, NULL, 11, NULL, 7, 9, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(66, NULL, 22, NULL, 2, 6, 3, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(67, NULL, 23, NULL, 15, 12, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(68, NULL, NULL, NULL, 1, 4, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(69, NULL, NULL, NULL, 9, 1, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(70, NULL, 13, NULL, 3, 7, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(71, NULL, 15, NULL, 6, 2, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(72, NULL, 24, NULL, 17, 11, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(73, NULL, 25, NULL, 2, 3, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(74, NULL, 26, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(75, NULL, 27, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(76, NULL, 28, NULL, 2, 4, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(77, NULL, 29, NULL, 8, 10, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(78, NULL, NULL, NULL, 3, 6, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(79, NULL, NULL, NULL, 5, 8, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(80, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(81, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(82, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(84, 20, NULL, NULL, NULL, 11, NULL, NULL, NULL, NULL, 1, NULL, 1, 6, 'N', 'N', 0),
(85, 21, NULL, NULL, 4, 8, NULL, NULL, NULL, NULL, 1, NULL, 1, 7, 'N', 'N', 0),
(86, NULL, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(87, NULL, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(88, NULL, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 3.2, 'N', 'N', 0),
(89, NULL, 30, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(90, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 9, 'N', 'N', 0),
(91, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 2, 9, 'Y', 'N', 0),
(92, 20, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'Y', 'N', 0),
(93, 20, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 'N', 'N', 0),
(94, NULL, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, 'N', 'N', 0),
(95, 11, 7, NULL, 3, 3, NULL, 6, 3, NULL, 2, NULL, 1, 6.9, 'Y', 'N', 0),
(96, NULL, NULL, NULL, 27, 12, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, 'N', 'N', 0),
(97, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, 'N', 'N', 0),
(98, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, 'N', 'N', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_page`
--

CREATE TABLE `wkx_resource_page` (
  `resourcepageId` int(11) NOT NULL,
  `resourcepagePageStart` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcepagePageEnd` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_page`
--

INSERT INTO `wkx_resource_page` (`resourcepageId`, `resourcepagePageStart`, `resourcepagePageEnd`) VALUES
(4, 'sPage', 'ePage'),
(7, 'sPage', 'ePage'),
(9, 'sPage', 'ePage'),
(10, 'sPage', NULL),
(11, 'sPage', 'ePage'),
(17, '34', '45'),
(19, '3', NULL),
(23, '2', '4'),
(27, 'PageStart', 'PageEnd'),
(62, 'PageStart', 'PageEnd'),
(64, 'PageStart', 'PageEnd'),
(66, 'PageStart', 'PageEnd'),
(68, 'PageStart', 'PageEnd'),
(70, 'PageStart', 'PageEnd'),
(74, 'PageStart', 'PageEnd'),
(80, 'PageStart', 'PageEnd'),
(84, 'PageStart', 'PageEnd');

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_summary`
--

CREATE TABLE `wkx_resource_summary` (
  `resourcesummaryId` int(11) NOT NULL,
  `resourcesummaryQuotes` int(11) DEFAULT NULL,
  `resourcesummaryParaphrases` int(11) DEFAULT NULL,
  `resourcesummaryMusings` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_summary`
--

INSERT INTO `wkx_resource_summary` (`resourcesummaryId`, `resourcesummaryQuotes`, `resourcesummaryParaphrases`, `resourcesummaryMusings`) VALUES
(1, 1, NULL, NULL),
(3, NULL, 2, 1),
(4, 2, NULL, NULL),
(15, 2, NULL, 1),
(68, 1, NULL, NULL),
(84, 1, NULL, NULL),
(92, NULL, 1, NULL),
(98, 1, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_text`
--

CREATE TABLE `wkx_resource_text` (
  `resourcetextId` int(11) NOT NULL,
  `resourcetextNote` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourcetextAbstract` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourcetextUrls` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourcetextUrlText` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `resourcetextEditUserIdNote` int(11) DEFAULT NULL,
  `resourcetextAddUserIdNote` int(11) DEFAULT NULL,
  `resourcetextEditUserIdAbstract` int(11) DEFAULT NULL,
  `resourcetextAddUserIdAbstract` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_text`
--

INSERT INTO `wkx_resource_text` (`resourcetextId`, `resourcetextNote`, `resourcetextAbstract`, `resourcetextUrls`, `resourcetextUrlText`, `resourcetextEditUserIdNote`, `resourcetextAddUserIdNote`, `resourcetextEditUserIdAbstract`, `resourcetextAddUserIdAbstract`) VALUES
(1, 'STRING sameIdOrderBib If the value is \'on\' and the same id numbers are being used (as above), the ordering of the id numbers in the text will follow the ordering of the appended bibliography rather than incrementing from 1. Otherwise, if the array element does not exist, id numbers will increment and the appended bibliography will follow the order of the id numbers in the text. NB. When using this option, endnotes for RTF exporting are faked (they will simply be plain text) because RTF cannot handle endnotes that do not increment in numerical order. In all other cases, RTF endnotes will be real endnotes as recognised by Word and OpenOffice.org.', NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDEiO30=', NULL, NULL, 1, NULL, NULL),
(3, 'OSBib-Format v3.0<br>XML structure ~ Citation (In-text and Footnote style)<br><br>A collection of PHP classes to manage bibliographic formatting for OS bibliography software using the OSBib standard. Taken from and originally developed in WIKINDX (https://wikindx.sourceforge.io).<br><br>Released through http://bibliophile.sourceforge.net under the GPL licence.<br><br>If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net so that your improvements can be added to the release package.<br><br>October 2005<br>Mark Grimshaw-Aagaard (WIKINDX)<br>Andrea Rossato (Uniwakka)<br>Guillaume Gardey (BibOrb)<br>Christian Boulanger (Bibliograph)', 'Caution: In this restricted \"SfR Fresh\" environment the current HTML page may not be correctly presentated and may have some non-functional links. Alternatively you can here view or download the uninterpreted source code. That can be also achieved for any archive member file by clicking within an archive contents listing on the first character of the file(path) respectively on the according byte size field.', 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDIiO30=', NULL, NULL, 3, NULL, 3),
(4, NULL, 'Since pointing out that WIKINDX can be run on a Windows desktop using WAMP, I\'ve been asked many times for instructions on how to set up WAMP to use WIKINDX. So, here they are (the details may be slightly different depending on the version of phpMyAdmin that comes with the WAMP version you have but the principles are the same).', 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDMiO30=', NULL, NULL, NULL, NULL, 3),
(6, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDMiO30=', NULL, NULL, NULL, NULL, NULL),
(7, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDMiO30=', NULL, NULL, NULL, NULL, NULL),
(10, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDIiO30=', NULL, NULL, NULL, NULL, NULL),
(15, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDMiO30=', NULL, NULL, NULL, NULL, NULL),
(17, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDQiO30=', NULL, NULL, NULL, NULL, NULL),
(21, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDUiO30=', NULL, NULL, NULL, NULL, NULL),
(22, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDEiO30=', NULL, NULL, NULL, NULL, NULL),
(23, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDUiO30=', NULL, NULL, NULL, NULL, NULL),
(24, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDEiO30=', NULL, NULL, NULL, NULL, NULL),
(25, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDUiO30=', NULL, NULL, NULL, NULL, NULL),
(26, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDYiO30=', NULL, NULL, NULL, NULL, NULL),
(27, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDUiO30=', NULL, NULL, NULL, NULL, NULL),
(28, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDEiO30=', NULL, NULL, NULL, NULL, NULL),
(29, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDciO30=', NULL, NULL, NULL, NULL, NULL),
(30, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDYiO30=', NULL, NULL, NULL, NULL, NULL),
(32, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDEiO30=', NULL, NULL, NULL, NULL, NULL),
(35, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDEiO30=', NULL, NULL, NULL, NULL, NULL),
(43, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDUiO30=', NULL, NULL, NULL, NULL, NULL),
(45, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDciO30=', NULL, NULL, NULL, NULL, NULL),
(65, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDEiO30=', NULL, NULL, NULL, NULL, NULL),
(67, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDUiO30=', NULL, NULL, NULL, NULL, NULL),
(68, 'Windows users wanting to run WIKINDX as a single user on their desktop may be interested in the very handy WAMP5 Server for Windows. Installing and configuring Apache/PHP/MySQL on Windows can be a bit of a chore - WAMP5 is an all-in-one install for those wishing to run WIKINDX from their Windows desktop. WIKINDX uses PHP\'s mysql_connect() rather than mysqli_connect() so make sure you grab the appropriate download if indicated on the download site.', 'WIKINDX is a free bibliographic and quotations/notes management and article authoring system designed either for single use (on a variety of operating sytems) or multi-user collaborative use across the internet.<br><br>Current version is 3.8.2<br><br>Developed under the GNU GPL license, the project homepage can be found at sourceforge and the required files/updates are available for download there. A FreeBSD port by Babak Farrokhi may be downloaded from http://www.freshports.org/www/wikindx.<br><br>The sourceforge site has all the appropriate contact details, forums for you to report bugs, request features etc.<br><br>Since v3.7, WIKINDX provides the possibility to interface and integrate with Content Management Systems which use \'replacement tags\' or similar. There are known to be a Moodle filter, a MediaWiki filter, a dokuwiki plug-in (a dokuwiki site using the WIKINDX plug-in is here), a WordPress plug-in and a phpWCMS filter and similar filters for other CMSs are easily written.  Tips on using jsMath with WIKINDX can be found here.', NULL, NULL, NULL, 2, NULL, 2),
(71, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDYiO30=', NULL, NULL, NULL, NULL, NULL),
(74, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDYiO30=', NULL, NULL, NULL, NULL, NULL),
(77, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDgiO30=', NULL, NULL, NULL, NULL, NULL),
(87, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDgiO30=', NULL, NULL, NULL, NULL, NULL),
(91, NULL, 'Caution: In this restricted \"SfR Fresh\" environment the current HTML page may not be correctly presentated and may have some non-functional links. Alternatively you can here view or download the uninterpreted source code. That can be also achieved for any archive member file by clicking within an archive contents listing on the first character of the file(path) respectively on the according byte size field.', NULL, NULL, NULL, NULL, NULL, 1),
(96, NULL, NULL, 'YToxOntpOjA7czoxMToiaHR0cDovL3VybDciO30=', NULL, NULL, NULL, NULL, NULL),
(98, 'OSBib is an Open Source bibliographic formatting engine written in PHP that uses XML style files to store formatting data for in-text or endnote-style (including footnote) citations and bibliographic lists. Released through Bibliophile, OSBib is designed to work with bibliographic data stored in any format via mapping arrays as defined in the class STYLEMAP. For those bibliographic systems whose data are stored in or that can be accessed as bibtex-type arrays, STYLEMAPBIBTEX is a set of pre-defined mapping arrays designed to get you up and running within a matter of minutes. Data stored in other formats require that STYLEMAP be edited.', 'Wikindx is a free bibliographic and quotations/notes management and article authoring system (Virtual Research Environment) designed either for single use (on a variety of operating systems) or multi-user collaborative use across the internet. Wikindx falls within the category of reference management software, but also provides functionality to write notes and entire papers. Developed under the GNU GPL license, the project homepage can be found at sourceforge.net and the required files/updates are available for download there.', NULL, NULL, NULL, 2, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_timestamp`
--

CREATE TABLE `wkx_resource_timestamp` (
  `resourcetimestampId` int(11) NOT NULL,
  `resourcetimestampTimestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `resourcetimestampTimestampAdd` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_timestamp`
--

INSERT INTO `wkx_resource_timestamp` (`resourcetimestampId`, `resourcetimestampTimestamp`, `resourcetimestampTimestampAdd`) VALUES
(1, '2010-12-27 08:39:50', '2010-10-10 09:39:45'),
(2, '2010-10-10 09:42:47', '2010-10-10 09:42:47'),
(3, '2010-12-27 08:29:38', '2010-10-10 09:44:48'),
(4, '2010-12-27 08:26:40', '2010-10-10 09:46:54'),
(5, '2010-10-10 16:12:38', '2010-10-10 16:12:38'),
(6, '2010-10-10 16:13:41', '2010-10-10 16:13:41'),
(7, '2010-10-12 05:48:11', '2010-10-12 05:48:11'),
(8, '2010-10-12 05:49:37', '2010-10-12 05:49:37'),
(9, '2010-10-12 06:37:17', '2010-10-12 06:37:17'),
(10, '2010-10-12 06:38:26', '2010-10-12 06:38:26'),
(11, '2010-10-12 06:39:34', '2010-10-12 06:39:34'),
(12, '2010-10-12 06:41:12', '2010-10-12 06:40:28'),
(13, '2010-10-17 06:41:13', '2010-10-17 06:41:13'),
(14, '2010-10-17 06:42:39', '2010-10-17 06:42:39'),
(15, '2010-12-27 08:20:40', '2010-10-17 06:43:58'),
(16, '2010-10-17 06:44:49', '2010-10-17 06:44:49'),
(17, '2010-10-22 07:20:26', '2010-10-22 07:20:26'),
(18, '2010-10-22 07:22:11', '2010-10-22 07:22:11'),
(19, '2010-10-22 07:24:18', '2010-10-22 07:24:18'),
(20, '2010-10-22 07:25:36', '2010-10-22 07:25:36'),
(21, '2010-10-22 07:29:22', '2010-10-22 07:29:22'),
(22, '2010-10-22 07:30:05', '2010-10-22 07:30:05'),
(23, '2010-10-22 15:54:40', '2010-10-22 15:54:40'),
(24, '2010-10-22 15:55:18', '2010-10-22 15:55:18'),
(25, '2010-10-22 15:56:40', '2010-10-22 15:56:40'),
(26, '2010-10-22 15:57:54', '2010-10-22 15:57:54'),
(27, '2010-11-03 06:24:09', '2010-11-03 06:24:09'),
(28, '2010-11-03 06:26:37', '2010-11-03 06:26:37'),
(29, '2010-11-03 06:27:45', '2010-11-03 06:27:45'),
(30, '2010-11-03 06:28:29', '2010-11-03 06:28:29'),
(31, '2010-11-06 08:51:58', '2010-11-03 06:29:26'),
(32, '2010-11-06 08:51:10', '2010-11-03 06:30:22'),
(33, '2010-11-06 08:49:08', '2010-11-06 08:49:08'),
(34, '2010-11-06 08:50:05', '2010-11-06 08:50:05'),
(35, '2010-11-06 08:54:42', '2010-11-06 08:54:42'),
(36, '2010-11-06 08:55:42', '2010-11-06 08:55:42'),
(37, '2010-11-07 08:21:56', '2010-11-07 08:21:57'),
(38, '2010-11-07 08:23:10', '2010-11-07 08:23:10'),
(39, '2010-11-07 08:24:21', '2010-11-07 08:24:21'),
(40, '2010-11-07 08:24:59', '2010-11-07 08:24:59'),
(41, '2010-11-07 08:25:54', '2010-11-07 08:25:54'),
(42, '2010-11-07 08:31:09', '2010-11-07 08:26:33'),
(43, '2010-11-07 08:27:35', '2010-11-07 08:27:35'),
(44, '2010-11-07 08:28:10', '2010-11-07 08:28:10'),
(45, '2010-11-07 08:29:53', '2010-11-07 08:29:53'),
(46, '2010-11-07 08:30:38', '2010-11-07 08:30:38'),
(62, '2010-11-22 08:03:43', '2010-11-22 08:03:43'),
(63, '2010-11-22 08:04:24', '2010-11-22 08:04:24'),
(64, '2010-11-22 08:05:48', '2010-11-22 08:05:48'),
(65, '2010-11-22 08:06:59', '2010-11-22 08:06:59'),
(66, '2010-12-26 09:20:35', '2010-12-26 09:20:35'),
(67, '2010-12-26 09:21:42', '2010-12-26 09:21:42'),
(68, '2010-12-27 08:22:41', '2010-12-26 09:22:40'),
(69, '2010-12-26 09:23:39', '2010-12-26 09:23:39'),
(70, '2010-12-26 10:18:12', '2010-12-26 10:18:12'),
(71, '2010-12-26 10:19:28', '2010-12-26 10:19:28'),
(72, '2010-12-26 10:20:55', '2010-12-26 10:20:55'),
(73, '2010-12-26 10:22:00', '2010-12-26 10:22:00'),
(74, '2010-12-26 11:46:02', '2010-12-26 11:46:02'),
(75, '2010-12-26 11:47:17', '2010-12-26 11:47:17'),
(76, '2010-12-26 11:49:51', '2010-12-26 11:49:51'),
(77, '2010-12-26 11:52:14', '2010-12-26 11:52:14'),
(78, '2010-12-26 11:53:03', '2010-12-26 11:53:03'),
(79, '2010-12-26 11:53:50', '2010-12-26 11:53:50'),
(80, '2010-12-26 11:54:39', '2010-12-26 11:54:39'),
(81, '2010-12-26 11:55:11', '2010-12-26 11:55:11'),
(82, '2010-12-26 11:55:57', '2010-12-26 11:55:57'),
(83, '2010-12-26 11:56:51', '2010-12-26 11:56:51'),
(84, '2010-12-27 08:42:46', '2010-12-26 14:59:38'),
(85, '2010-12-26 15:00:38', '2010-12-26 15:00:38'),
(86, '2010-12-26 15:54:53', '2010-12-26 15:54:53'),
(87, '2010-12-26 15:55:59', '2010-12-26 15:55:59'),
(88, '2010-12-26 15:57:07', '2010-12-26 15:57:07'),
(89, '2010-12-26 15:58:15', '2010-12-26 15:58:15'),
(90, '2010-12-26 15:58:56', '2010-12-26 15:58:56'),
(91, '2010-12-27 08:37:52', '2010-12-26 15:59:48'),
(92, '2010-12-27 08:37:17', '2010-12-26 16:00:32'),
(93, '2010-12-26 16:01:16', '2010-12-26 16:01:16'),
(94, '2010-12-27 08:08:08', '2010-12-27 08:08:08'),
(95, '2010-12-27 08:09:03', '2010-12-27 08:09:03'),
(96, '2010-12-27 08:10:19', '2010-12-27 08:10:19'),
(97, '2010-12-27 08:11:08', '2010-12-27 08:11:08'),
(98, '2010-12-27 08:17:00', '2010-12-27 08:11:59');

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_user_tags`
--

CREATE TABLE `wkx_resource_user_tags` (
  `resourceusertagsId` int(11) NOT NULL,
  `resourceusertagsTagId` int(11) DEFAULT NULL,
  `resourceusertagsResourceId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_resource_year`
--

CREATE TABLE `wkx_resource_year` (
  `resourceyearId` int(11) NOT NULL,
  `resourceyearYear1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceyearYear2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceyearYear3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceyearYear4` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_resource_year`
--

INSERT INTO `wkx_resource_year` (`resourceyearId`, `resourceyearYear1`, `resourceyearYear2`, `resourceyearYear3`, `resourceyearYear4`) VALUES
(1, 'PubYear1', 'ReprintYear1', 'VolumeYear1', 'TransPubYear'),
(2, 'PubYear2', 'ReprintYear2', 'VolumeYear2', NULL),
(3, 'PubYear1', 'ReprintYear1', 'VolumeYear2', 'TransPubYear2'),
(4, NULL, NULL, NULL, NULL),
(5, 'PubYear1', NULL, 'VolumeYear1', 'TransPubYear2'),
(6, 'PubYear1', NULL, NULL, NULL),
(7, 'PubYear1', NULL, 'PubYear1', NULL),
(8, 'PubYear3', NULL, NULL, NULL),
(9, 'PubYear1', NULL, NULL, NULL),
(10, 'PubYear3', NULL, NULL, NULL),
(11, 'PubYear1', NULL, NULL, NULL),
(12, 'PubYear1', NULL, 'PubYear1', NULL),
(13, 'PubYear1', 'ConfYear1', 'ConfYear1', NULL),
(14, 'PubYear4', 'ConfYear2', 'ConfYear2', NULL),
(15, NULL, 'ConfYear1', 'ConfYear1', NULL),
(16, NULL, 'ConfYear1', NULL, NULL),
(17, 'PubYear4', 'ConfYear4', 'ConfYear4', NULL),
(18, 'PubYear3', 'ConfYear3', 'ConfYear3', NULL),
(19, 'ThesisYear1', 'PubYear1', NULL, NULL),
(20, 'ThesisYear2', NULL, NULL, NULL),
(21, 'PubYear3', '2010', NULL, NULL),
(22, 'PubYear2', '2009', NULL, NULL),
(23, 'PubYear3', '2010', NULL, NULL),
(24, NULL, '2010', NULL, NULL),
(25, 'PubYear2', '2010', 'Revyear1', NULL),
(26, 'PubYear1', '2010', NULL, NULL),
(27, 'PubYear2', '2010', 'Revyear2', NULL),
(28, 'PubYear1', '2009', NULL, NULL),
(29, 'PubYear7', '2010', NULL, NULL),
(30, NULL, '2001', NULL, NULL),
(31, 'PubYear3', NULL, NULL, NULL),
(32, 'PubYear7', NULL, NULL, NULL),
(33, 'Broadcastyear1', NULL, NULL, NULL),
(34, 'Broadcastyear2', NULL, NULL, NULL),
(35, 'PubYear3', NULL, NULL, NULL),
(36, 'PubYear1', NULL, NULL, NULL),
(37, 'PubYear2', NULL, NULL, NULL),
(38, 'PubYear1', NULL, NULL, NULL),
(39, 'PubYear1', NULL, NULL, NULL),
(40, 'PubYear4', NULL, NULL, NULL),
(41, 'PubYear4', NULL, NULL, NULL),
(42, 'PubYear1', NULL, NULL, NULL),
(43, 'PubYear1', NULL, NULL, NULL),
(44, 'PubYear3', NULL, NULL, NULL),
(45, 'PubYear3', NULL, 'volPubYear3', NULL),
(46, 'PubYear7', NULL, NULL, NULL),
(62, 'PubYear1', NULL, NULL, NULL),
(63, 'PubYear7', NULL, NULL, NULL),
(64, 'PubYear1', NULL, NULL, NULL),
(65, 'PubYear3', NULL, NULL, NULL),
(66, '2001', NULL, NULL, NULL),
(67, '2011', NULL, NULL, NULL),
(68, '2000', NULL, NULL, NULL),
(69, '1896', NULL, NULL, NULL),
(70, '2000', NULL, NULL, NULL),
(71, '1896', NULL, NULL, NULL),
(72, '1769', NULL, NULL, NULL),
(73, '2010', NULL, NULL, NULL),
(74, 'SessYear', NULL, NULL, NULL),
(75, 'SessYear', NULL, NULL, NULL),
(76, '1999', NULL, NULL, NULL),
(77, '1885', NULL, NULL, NULL),
(78, '1876', NULL, NULL, NULL),
(79, '2005', NULL, NULL, NULL),
(80, '2009', NULL, NULL, NULL),
(81, '1788', NULL, NULL, NULL),
(82, 'BC 201', NULL, NULL, NULL),
(83, '313 AD', NULL, NULL, NULL),
(84, '1301', NULL, NULL, NULL),
(85, '1239', NULL, NULL, NULL),
(86, 'PubYear', NULL, NULL, NULL),
(87, 'PubYear3', NULL, NULL, NULL),
(88, 'PubYear2', NULL, NULL, NULL),
(89, 'PubYear1', NULL, NULL, NULL),
(90, 'PubYear7', NULL, NULL, NULL),
(91, 'PubYear3', NULL, NULL, NULL),
(92, 'PubYear7', NULL, NULL, NULL),
(93, 'PubYear2', NULL, NULL, NULL),
(94, 'PubYear1', NULL, NULL, NULL),
(95, NULL, '2000', '2000', NULL),
(96, 'PubYear1', '2010', NULL, NULL),
(97, 'PubYear', NULL, NULL, NULL),
(98, 'PubYear3', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_statistics`
--

CREATE TABLE `wkx_statistics` (
  `statisticsId` int(11) NOT NULL,
  `statisticsResourceId` int(11) NOT NULL,
  `statisticsAttachmentId` int(11) DEFAULT NULL,
  `statisticsStatistics` mediumtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_statistics`
--

INSERT INTO `wkx_statistics` (`statisticsId`, `statisticsResourceId`, `statisticsAttachmentId`, `statisticsStatistics`) VALUES
(1, 1, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(2, 2, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(3, 3, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(4, 4, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(5, 5, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(6, 6, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(7, 7, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(8, 8, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(9, 9, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(10, 10, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(11, 11, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(12, 12, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(13, 13, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(14, 14, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(15, 15, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(16, 16, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(17, 17, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(18, 18, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(19, 19, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(20, 20, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(21, 21, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(22, 22, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(23, 23, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(24, 24, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(25, 25, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(26, 26, NULL, 'YTo3NDp7aToyMDEwMTA7ZDoxO2k6MjAxMDExO2Q6MTtpOjIwMTAxMjtkOjE7aToyMDExMDE7ZDoxO2k6MjAxMTAyO2Q6MTtpOjIwMTEwMztkOjE7aToyMDExMDQ7ZDoxO2k6MjAxMTA1O2Q6MTtpOjIwMTEwNjtkOjE7aToyMDExMDc7ZDoxO2k6MjAxMTA4O2Q6MTtpOjIwMTEwOTtkOjE7aToyMDExMTA7ZDoxO2k6MjAxMTExO2Q6MTtpOjIwMTExMjtkOjE7aToyMDEyMDE7ZDoxO2k6MjAxMjAyO2Q6MTtpOjIwMTIwMztkOjE7aToyMDEyMDQ7ZDoxO2k6MjAxMjA1O2Q6MTtpOjIwMTIwNjtkOjE7aToyMDEyMDc7ZDoxO2k6MjAxMjA4O2Q6MTtpOjIwMTIwOTtkOjE7aToyMDEyMTA7ZDoxO2k6MjAxMjExO2Q6MTtpOjIwMTIxMjtkOjE7aToyMDEzMDE7ZDoxO2k6MjAxMzAyO2Q6MTtpOjIwMTMwMztkOjE7aToyMDEzMDQ7ZDoxO2k6MjAxMzA1O2Q6MTtpOjIwMTMwNjtkOjE7aToyMDEzMDc7ZDoxO2k6MjAxMzA4O2Q6MTtpOjIwMTMwOTtkOjE7aToyMDEzMTA7ZDoxO2k6MjAxMzExO2Q6MTtpOjIwMTMxMjtkOjE7aToyMDE0MDE7ZDoxO2k6MjAxNDAyO2Q6MTtpOjIwMTQwMztkOjE7aToyMDE0MDQ7ZDoxO2k6MjAxNDA1O2Q6MTtpOjIwMTQwNjtkOjE7aToyMDE0MDc7ZDoxO2k6MjAxNDA4O2Q6MTtpOjIwMTQwOTtkOjE7aToyMDE0MTA7ZDoxO2k6MjAxNDExO2Q6MTtpOjIwMTQxMjtkOjE7aToyMDE1MDE7ZDoxO2k6MjAxNTAyO2Q6MTtpOjIwMTUwMztkOjE7aToyMDE1MDQ7ZDoxO2k6MjAxNTA1O2Q6MTtpOjIwMTUwNjtkOjE7aToyMDE1MDc7ZDoxO2k6MjAxNTA4O2Q6MTtpOjIwMTUwOTtkOjE7aToyMDE1MTA7ZDoxO2k6MjAxNTExO2Q6MTtpOjIwMTUxMjtkOjE7aToyMDE2MDE7ZDoxO2k6MjAxNjAyO2Q6MTtpOjIwMTYwMztkOjE7aToyMDE2MDQ7ZDoxO2k6MjAxNjA1O2Q6MTtpOjIwMTYwNjtkOjE7aToyMDE2MDc7ZDoxO2k6MjAxNjA4O2Q6MTtpOjIwMTgwNTtzOjE6IjEiO2k6MjAyMDAxO047aToyMDIwMDI7czoxOiIwIjt9'),
(27, 27, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(28, 28, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(29, 29, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(30, 30, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(31, 31, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(32, 32, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(33, 33, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(34, 34, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(35, 35, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(36, 36, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(37, 37, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30=');
INSERT INTO `wkx_statistics` (`statisticsId`, `statisticsResourceId`, `statisticsAttachmentId`, `statisticsStatistics`) VALUES
(38, 38, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(39, 39, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(40, 40, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(41, 41, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(42, 42, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(43, 43, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(44, 44, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(45, 45, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(46, 46, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(47, 62, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(48, 63, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(49, 64, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(50, 65, NULL, 'YTo3Mzp7aToyMDEwMTE7ZDoxO2k6MjAxMDEyO2Q6MTtpOjIwMTEwMTtkOjE7aToyMDExMDI7ZDoxO2k6MjAxMTAzO2Q6MTtpOjIwMTEwNDtkOjE7aToyMDExMDU7ZDoxO2k6MjAxMTA2O2Q6MTtpOjIwMTEwNztkOjE7aToyMDExMDg7ZDoxO2k6MjAxMTA5O2Q6MTtpOjIwMTExMDtkOjE7aToyMDExMTE7ZDoxO2k6MjAxMTEyO2Q6MTtpOjIwMTIwMTtkOjE7aToyMDEyMDI7ZDoxO2k6MjAxMjAzO2Q6MTtpOjIwMTIwNDtkOjE7aToyMDEyMDU7ZDoxO2k6MjAxMjA2O2Q6MTtpOjIwMTIwNztkOjE7aToyMDEyMDg7ZDoxO2k6MjAxMjA5O2Q6MTtpOjIwMTIxMDtkOjE7aToyMDEyMTE7ZDoxO2k6MjAxMjEyO2Q6MTtpOjIwMTMwMTtkOjE7aToyMDEzMDI7ZDoxO2k6MjAxMzAzO2Q6MTtpOjIwMTMwNDtkOjE7aToyMDEzMDU7ZDoxO2k6MjAxMzA2O2Q6MTtpOjIwMTMwNztkOjE7aToyMDEzMDg7ZDoxO2k6MjAxMzA5O2Q6MTtpOjIwMTMxMDtkOjE7aToyMDEzMTE7ZDoxO2k6MjAxMzEyO2Q6MTtpOjIwMTQwMTtkOjE7aToyMDE0MDI7ZDoxO2k6MjAxNDAzO2Q6MTtpOjIwMTQwNDtkOjE7aToyMDE0MDU7ZDoxO2k6MjAxNDA2O2Q6MTtpOjIwMTQwNztkOjE7aToyMDE0MDg7ZDoxO2k6MjAxNDA5O2Q6MTtpOjIwMTQxMDtkOjE7aToyMDE0MTE7ZDoxO2k6MjAxNDEyO2Q6MTtpOjIwMTUwMTtkOjE7aToyMDE1MDI7ZDoxO2k6MjAxNTAzO2Q6MTtpOjIwMTUwNDtkOjE7aToyMDE1MDU7ZDoxO2k6MjAxNTA2O2Q6MTtpOjIwMTUwNztkOjE7aToyMDE1MDg7ZDoxO2k6MjAxNTA5O2Q6MTtpOjIwMTUxMDtkOjE7aToyMDE1MTE7ZDoxO2k6MjAxNTEyO2Q6MTtpOjIwMTYwMTtkOjE7aToyMDE2MDI7ZDoxO2k6MjAxNjAzO2Q6MTtpOjIwMTYwNDtkOjE7aToyMDE2MDU7ZDoxO2k6MjAxNjA2O2Q6MTtpOjIwMTYwNztkOjE7aToyMDE2MDg7ZDoxO2k6MjAxODA1O3M6MToiMSI7aToyMDIwMDE7TjtpOjIwMjAwMjtzOjE6IjAiO30='),
(51, 66, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(52, 67, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(53, 68, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(54, 69, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(55, 70, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(56, 71, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(57, 72, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(58, 73, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(59, 74, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(60, 75, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(61, 76, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(62, 77, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(63, 78, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(64, 79, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(65, 80, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(66, 81, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(67, 82, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(68, 83, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(69, 84, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(70, 85, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(71, 86, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(72, 87, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(73, 88, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(74, 89, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(75, 90, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ==');
INSERT INTO `wkx_statistics` (`statisticsId`, `statisticsResourceId`, `statisticsAttachmentId`, `statisticsStatistics`) VALUES
(76, 91, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(77, 92, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(78, 93, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(79, 94, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(80, 95, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(81, 96, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(82, 97, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ=='),
(83, 98, NULL, 'YTo3Mjp7aToyMDEwMTI7ZDoxO2k6MjAxMTAxO2Q6MTtpOjIwMTEwMjtkOjE7aToyMDExMDM7ZDoxO2k6MjAxMTA0O2Q6MTtpOjIwMTEwNTtkOjE7aToyMDExMDY7ZDoxO2k6MjAxMTA3O2Q6MTtpOjIwMTEwODtkOjE7aToyMDExMDk7ZDoxO2k6MjAxMTEwO2Q6MTtpOjIwMTExMTtkOjE7aToyMDExMTI7ZDoxO2k6MjAxMjAxO2Q6MTtpOjIwMTIwMjtkOjE7aToyMDEyMDM7ZDoxO2k6MjAxMjA0O2Q6MTtpOjIwMTIwNTtkOjE7aToyMDEyMDY7ZDoxO2k6MjAxMjA3O2Q6MTtpOjIwMTIwODtkOjE7aToyMDEyMDk7ZDoxO2k6MjAxMjEwO2Q6MTtpOjIwMTIxMTtkOjE7aToyMDEyMTI7ZDoxO2k6MjAxMzAxO2Q6MTtpOjIwMTMwMjtkOjE7aToyMDEzMDM7ZDoxO2k6MjAxMzA0O2Q6MTtpOjIwMTMwNTtkOjE7aToyMDEzMDY7ZDoxO2k6MjAxMzA3O2Q6MTtpOjIwMTMwODtkOjE7aToyMDEzMDk7ZDoxO2k6MjAxMzEwO2Q6MTtpOjIwMTMxMTtkOjE7aToyMDEzMTI7ZDoxO2k6MjAxNDAxO2Q6MTtpOjIwMTQwMjtkOjE7aToyMDE0MDM7ZDoxO2k6MjAxNDA0O2Q6MTtpOjIwMTQwNTtkOjE7aToyMDE0MDY7ZDoxO2k6MjAxNDA3O2Q6MTtpOjIwMTQwODtkOjE7aToyMDE0MDk7ZDoxO2k6MjAxNDEwO2Q6MTtpOjIwMTQxMTtkOjE7aToyMDE0MTI7ZDoxO2k6MjAxNTAxO2Q6MTtpOjIwMTUwMjtkOjE7aToyMDE1MDM7ZDoxO2k6MjAxNTA0O2Q6MTtpOjIwMTUwNTtkOjE7aToyMDE1MDY7ZDoxO2k6MjAxNTA3O2Q6MTtpOjIwMTUwODtkOjE7aToyMDE1MDk7ZDoxO2k6MjAxNTEwO2Q6MTtpOjIwMTUxMTtkOjE7aToyMDE1MTI7ZDoxO2k6MjAxNjAxO2Q6MTtpOjIwMTYwMjtkOjE7aToyMDE2MDM7ZDoxO2k6MjAxNjA0O2Q6MTtpOjIwMTYwNTtkOjE7aToyMDE2MDY7ZDoxO2k6MjAxNjA3O2Q6MTtpOjIwMTYwODtkOjE7aToyMDE4MDU7czoxOiIxIjtpOjIwMjAwMTtOO2k6MjAyMDAyO3M6MToiMCI7fQ==');

-- --------------------------------------------------------

--
-- Table structure for table `wkx_subcategory`
--

CREATE TABLE `wkx_subcategory` (
  `subcategoryId` int(11) NOT NULL,
  `subcategoryCategoryId` int(11) DEFAULT NULL,
  `subcategorySubcategory` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_tag`
--

CREATE TABLE `wkx_tag` (
  `tagId` int(11) NOT NULL,
  `tagTag` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_users`
--

CREATE TABLE `wkx_users` (
  `usersId` int(11) NOT NULL,
  `usersUsername` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `usersPassword` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `usersFullname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersEmail` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersTimestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `usersAdmin` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersCookie` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersPaging` int(11) DEFAULT '20',
  `usersPagingMaxLinks` int(11) DEFAULT '11',
  `usersPagingStyle` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersStringLimit` int(11) DEFAULT '40',
  `usersLanguage` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT 'auto',
  `usersStyle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT 'APA',
  `usersTemplate` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT 'default',
  `usersNotify` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersNotifyAddEdit` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'A',
  `usersNotifyThreshold` int(2) DEFAULT '0',
  `usersNotifyTimestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `usersPagingTagCloud` int(11) DEFAULT '100',
  `usersPasswordQuestion1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordAnswer1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordQuestion2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordAnswer2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordQuestion3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordAnswer3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersUserSession` longtext COLLATE utf8mb4_unicode_520_ci,
  `usersUseBibtexKey` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersUseWikindxKey` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersDisplayBibtexLink` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersDisplayCmsLink` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersCmsTag` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersIsCreator` int(11) DEFAULT NULL,
  `usersListlink` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersDepartment` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersTemplateMenu` int(11) DEFAULT NULL,
  `usersInstitution` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersNotifyDigestThreshold` int(11) DEFAULT '100',
  `usersChangePasswordTimestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `usersGDPR` varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N',
  `usersBlock` varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_users`
--

INSERT INTO `wkx_users` (`usersId`, `usersUsername`, `usersPassword`, `usersFullname`, `usersEmail`, `usersTimestamp`, `usersAdmin`, `usersCookie`, `usersPaging`, `usersPagingMaxLinks`, `usersPagingStyle`, `usersStringLimit`, `usersLanguage`, `usersStyle`, `usersTemplate`, `usersNotify`, `usersNotifyAddEdit`, `usersNotifyThreshold`, `usersNotifyTimestamp`, `usersPagingTagCloud`, `usersPasswordQuestion1`, `usersPasswordAnswer1`, `usersPasswordQuestion2`, `usersPasswordAnswer2`, `usersPasswordQuestion3`, `usersPasswordAnswer3`, `usersUserSession`, `usersUseBibtexKey`, `usersUseWikindxKey`, `usersDisplayBibtexLink`, `usersDisplayCmsLink`, `usersCmsTag`, `usersIsCreator`, `usersListlink`, `usersDepartment`, `usersTemplateMenu`, `usersInstitution`, `usersNotifyDigestThreshold`, `usersChangePasswordTimestamp`, `usersGDPR`, `usersBlock`) VALUES
(1, 'super', '36066C6rn7.oA', NULL, NULL, '2010-10-10 11:32:52', 'Y', 'N', 15, 11, 'N', 40, 'en_GB', 'apa', 'bryophyta', 'N', 'A', NULL, '2010-10-10 11:32:52', 100, NULL, NULL, NULL, NULL, NULL, NULL, 'YToxOntzOjEyOiJRdWVyeVN0cmluZ3MiO3M6MTExMjoiWVRveE1EcDdhVG93TzNNNk56azZJaTkzYVd0cGJtUjROUzkzYVd0cGJtUjRMM1J5ZFc1ckwybHVaR1Y0TG5Cb2NEOWhZM1JwYjI0OWRYTmxjbk5uY205MWNITmZUVmxYU1V0SlRrUllYME5QVWtVbWJXVjBhRzlrUFdsdWFYUWlPMms2TVR0ek9qWTNPaUl2ZDJscmFXNWtlRFV2ZDJscmFXNWtlQzkwY25WdWF5OXBibVJsZUM1d2FIQS9ZV04wYVc5dVBYVnpaWEp6WjNKdmRYQnpYMDFaVjBsTFNVNUVXRjlEVDFKRklqdHBPakk3Y3pvMk56b2lMM2RwYTJsdVpIZzFMM2RwYTJsdVpIZ3ZkSEoxYm1zdmFXNWtaWGd1Y0dod1AyRmpkR2x2YmoxMWMyVnljMmR5YjNWd2MxOU5XVmRKUzBsT1JGaGZRMDlTUlNJN2FUb3pPM002TmprNklpOTNhV3RwYm1SNE5TOTNhV3RwYm1SNEwzUnlkVzVyTDJsdVpHVjRMbkJvY0Q5aFkzUnBiMjQ5YVcxd2IzSjBaWGh3YjNKMFltbGlYMmx1YVhSQ2FXSjFkR2xzY3lJN2FUbzBPM002TmprNklpOTNhV3RwYm1SNE5TOTNhV3RwYm1SNEwzUnlkVzVyTDJsdVpHVjRMbkJvY0Q5aFkzUnBiMjQ5YVcxd2IzSjBaWGh3YjNKMFltbGlYMmx1YVhSQ2FXSjFkR2xzY3lJN2FUbzFPM002TmprNklpOTNhV3RwYm1SNE5TOTNhV3RwYm1SNEwzUnlkVzVyTDJsdVpHVjRMbkJvY0Q5aFkzUnBiMjQ5YVcxd2IzSjBaWGh3YjNKMFltbGlYMmx1YVhSQ2FXSjFkR2xzY3lJN2FUbzJPM002TmprNklpOTNhV3RwYm1SNE5TOTNhV3RwYm1SNEwzUnlkVzVyTDJsdVpHVjRMbkJvY0Q5aFkzUnBiMjQ5YVcxd2IzSjBaWGh3YjNKMFltbGlYMmx1YVhSQ2FXSjFkR2xzY3lJN2FUbzNPM002TmprNklpOTNhV3RwYm1SNE5TOTNhV3RwYm1SNEwzUnlkVzVyTDJsdVpHVjRMbkJvY0Q5aFkzUnBiMjQ5YVcxd2IzSjBaWGh3YjNKMFltbGlYMmx1YVhSQ2FXSjFkR2xzY3lJN2FUbzRPM002TnpRNklpOTNhV3RwYm1SNE5TOTNhV3RwYm1SNEwzUnlkVzVyTDJsdVpHVjRMbkJvY0Q5aFkzUnBiMjQ5WW1GemEyVjBYMEpCVTB0RlZGOURUMUpGSm5KbGMyOTFjbU5sU1dROU5UYzBJanRwT2prN2N6bzNORG9pTDNkcGEybHVaSGcxTDNkcGEybHVaSGd2ZEhKMWJtc3ZhVzVrWlhndWNHaHdQMkZqZEdsdmJqMXlaWE52ZFhKalpWOVNSVk5QVlZKRFJWWkpSVmRmUTA5U1JTWnBaRDAxTnpRaU8zMD0iO30=', 'N', 'N', '1', 'N', NULL, NULL, '', NULL, 0, NULL, 100, '2010-10-10 11:32:52', 'N', 'N'),
(2, 'user1', '76Rgfb2tSgYHA', NULL, 'blah@blah.com', '2010-12-27 07:51:07', 'N', 'N', 10, 11, 'N', 40, 'en_GB', 'apa', 'default', 'N', 'A', NULL, '2010-12-27 07:51:07', 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL, 'N', NULL, NULL, NULL, 100, '2010-12-27 07:51:07', 'N', 'N'),
(3, 'user2', '29pVL2tfNr34E', NULL, 'blah@blah.com', '2010-12-27 07:51:32', 'N', 'N', 10, 11, 'N', 40, 'en_GB', 'apa', 'default', 'N', 'A', NULL, '2010-12-27 07:51:32', 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL, 'N', NULL, NULL, NULL, 100, '2010-12-27 07:51:32', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `wkx_user_bibliography`
--

CREATE TABLE `wkx_user_bibliography` (
  `userbibliographyId` int(11) NOT NULL,
  `userbibliographyUserId` int(11) DEFAULT NULL,
  `userbibliographyUserGroupId` int(11) DEFAULT NULL,
  `userbibliographyTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `userbibliographyDescription` mediumtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_user_bibliography`
--

INSERT INTO `wkx_user_bibliography` (`userbibliographyId`, `userbibliographyUserId`, `userbibliographyUserGroupId`, `userbibliographyTitle`, `userbibliographyDescription`) VALUES
(1, 3, NULL, 'User2Bibliography', 'This is the private bibliography for User2'),
(2, 1, 1, 'superBibliography', 'Private bibliography for super\'s user group which includes user1.'),
(3, 1, NULL, 'User Bib', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_user_bibliography_resource`
--

CREATE TABLE `wkx_user_bibliography_resource` (
  `userbibliographyresourceId` int(11) NOT NULL,
  `userbibliographyresourceBibliographyId` int(11) DEFAULT NULL,
  `userbibliographyresourceResourceId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_user_bibliography_resource`
--

INSERT INTO `wkx_user_bibliography_resource` (`userbibliographyresourceId`, `userbibliographyresourceBibliographyId`, `userbibliographyresourceResourceId`) VALUES
(1, 1, 42),
(2, 1, 78),
(3, 1, 15),
(4, 1, 86),
(5, 1, 87),
(6, 1, 97),
(7, 1, 39),
(8, 1, 72),
(9, 1, 73),
(10, 1, 30),
(11, 1, 36),
(12, 1, 37),
(13, 1, 38),
(14, 1, 4),
(15, 2, 41),
(16, 2, 62),
(17, 2, 44),
(18, 2, 28),
(19, 2, 70),
(20, 2, 26),
(21, 2, 86),
(22, 2, 40),
(23, 2, 88),
(24, 2, 95),
(25, 2, 16),
(26, 2, 42),
(27, 2, 83),
(28, 2, 82);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_user_groups`
--

CREATE TABLE `wkx_user_groups` (
  `usergroupsId` int(11) NOT NULL,
  `usergroupsTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `usergroupsDescription` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `usergroupsAdminId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_user_groups`
--

INSERT INTO `wkx_user_groups` (`usergroupsId`, `usergroupsTitle`, `usergroupsDescription`, `usergroupsAdminId`) VALUES
(1, 'Super UserGroup', 'This is super\'s user group and includes user1.  It is user for the superBibliography.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_user_groups_users`
--

CREATE TABLE `wkx_user_groups_users` (
  `usergroupsusersId` int(11) NOT NULL,
  `usergroupsusersGroupId` int(11) DEFAULT NULL,
  `usergroupsusersUserId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wkx_user_groups_users`
--

INSERT INTO `wkx_user_groups_users` (`usergroupsusersId`, `usergroupsusersGroupId`, `usergroupsusersUserId`) VALUES
(1, 1, 1),
(2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `wkx_user_register`
--

CREATE TABLE `wkx_user_register` (
  `userregisterId` int(11) NOT NULL,
  `userregisterHashKey` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `userregisterEmail` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `userregisterTimestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `userregisterConfirmed` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `userregisterRequest` mediumtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wkx_user_tags`
--

CREATE TABLE `wkx_user_tags` (
  `usertagsId` int(11) NOT NULL,
  `usertagsUserId` int(11) DEFAULT NULL,
  `usertagsTag` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wkx_bibtex_string`
--
ALTER TABLE `wkx_bibtex_string`
  ADD PRIMARY KEY (`bibtexstringId`);

--
-- Indexes for table `wkx_category`
--
ALTER TABLE `wkx_category`
  ADD PRIMARY KEY (`categoryId`),
  ADD KEY `categoryCategory` (`categoryCategory`(100));

--
-- Indexes for table `wkx_collection`
--
ALTER TABLE `wkx_collection`
  ADD PRIMARY KEY (`collectionId`),
  ADD KEY `collectionTitle` (`collectionTitle`(100));

--
-- Indexes for table `wkx_config`
--
ALTER TABLE `wkx_config`
  ADD PRIMARY KEY (`configId`),
  ADD KEY `configName` (`configName`(100));

--
-- Indexes for table `wkx_creator`
--
ALTER TABLE `wkx_creator`
  ADD PRIMARY KEY (`creatorId`),
  ADD KEY `creatorSameAs` (`creatorSameAs`),
  ADD KEY `creatorSurname` (`creatorSurname`(100));

--
-- Indexes for table `wkx_custom`
--
ALTER TABLE `wkx_custom`
  ADD PRIMARY KEY (`customId`);

--
-- Indexes for table `wkx_import_raw`
--
ALTER TABLE `wkx_import_raw`
  ADD PRIMARY KEY (`importrawId`);

--
-- Indexes for table `wkx_keyword`
--
ALTER TABLE `wkx_keyword`
  ADD PRIMARY KEY (`keywordId`),
  ADD KEY `keywordKeyword` (`keywordKeyword`(100));

--
-- Indexes for table `wkx_language`
--
ALTER TABLE `wkx_language`
  ADD PRIMARY KEY (`languageId`);

--
-- Indexes for table `wkx_news`
--
ALTER TABLE `wkx_news`
  ADD PRIMARY KEY (`newsId`);

--
-- Indexes for table `wkx_plugin_soundexplorer`
--
ALTER TABLE `wkx_plugin_soundexplorer`
  ADD PRIMARY KEY (`pluginsoundexplorerId`);

--
-- Indexes for table `wkx_plugin_wordprocessor`
--
ALTER TABLE `wkx_plugin_wordprocessor`
  ADD PRIMARY KEY (`pluginwordprocessorId`);

--
-- Indexes for table `wkx_publisher`
--
ALTER TABLE `wkx_publisher`
  ADD PRIMARY KEY (`publisherId`),
  ADD KEY `publisherName` (`publisherName`(100));

--
-- Indexes for table `wkx_resource`
--
ALTER TABLE `wkx_resource`
  ADD PRIMARY KEY (`resourceId`),
  ADD KEY `resourceType` (`resourceType`(100));

--
-- Indexes for table `wkx_resource_attachments`
--
ALTER TABLE `wkx_resource_attachments`
  ADD PRIMARY KEY (`resourceattachmentsId`),
  ADD KEY `resourceattachmentsResourceId` (`resourceattachmentsResourceId`);

--
-- Indexes for table `wkx_resource_category`
--
ALTER TABLE `wkx_resource_category`
  ADD PRIMARY KEY (`resourcecategoryId`),
  ADD KEY `resourcecategoryCategoryId` (`resourcecategoryCategoryId`),
  ADD KEY `resourcecategoryResourceId` (`resourcecategoryResourceId`);

--
-- Indexes for table `wkx_resource_creator`
--
ALTER TABLE `wkx_resource_creator`
  ADD PRIMARY KEY (`resourcecreatorId`),
  ADD KEY `resourcecreatorResourceId` (`resourcecreatorResourceId`),
  ADD KEY `resourcecreatorCreatorId` (`resourcecreatorCreatorId`),
  ADD KEY `resourcecreatorCreatorSurname` (`resourcecreatorCreatorSurname`(100));

--
-- Indexes for table `wkx_resource_custom`
--
ALTER TABLE `wkx_resource_custom`
  ADD PRIMARY KEY (`resourcecustomId`),
  ADD KEY `resourcecustomCustomId` (`resourcecustomCustomId`),
  ADD KEY `resourcecustomResourceId` (`resourcecustomResourceId`);
ALTER TABLE `wkx_resource_custom` ADD FULLTEXT KEY `resourcecustomLong` (`resourcecustomLong`);

--
-- Indexes for table `wkx_resource_keyword`
--
ALTER TABLE `wkx_resource_keyword`
  ADD PRIMARY KEY (`resourcekeywordId`),
  ADD KEY `resourcekeywordKeywordId` (`resourcekeywordKeywordId`),
  ADD KEY `resourcekeywordResourceId` (`resourcekeywordResourceId`);

--
-- Indexes for table `wkx_resource_language`
--
ALTER TABLE `wkx_resource_language`
  ADD PRIMARY KEY (`resourcelanguageId`),
  ADD KEY `resourcelanguageResourceId` (`resourcelanguageResourceId`),
  ADD KEY `resourcelanguageLanguageId` (`resourcelanguageLanguageId`);

--
-- Indexes for table `wkx_resource_metadata`
--
ALTER TABLE `wkx_resource_metadata`
  ADD PRIMARY KEY (`resourcemetadataId`),
  ADD KEY `resourcemetadataResourceId` (`resourcemetadataResourceId`),
  ADD KEY `resourcemetadataMetadataId` (`resourcemetadataMetadataId`),
  ADD KEY `resourcemetadataAddUserId` (`resourcemetadataAddUserId`);
ALTER TABLE `wkx_resource_metadata` ADD FULLTEXT KEY `resourcemetadataText` (`resourcemetadataText`);

--
-- Indexes for table `wkx_resource_misc`
--
ALTER TABLE `wkx_resource_misc`
  ADD PRIMARY KEY (`resourcemiscId`),
  ADD KEY `resourcemiscCollection` (`resourcemiscCollection`),
  ADD KEY `resourcemiscPublisher` (`resourcemiscPublisher`);

--
-- Indexes for table `wkx_resource_page`
--
ALTER TABLE `wkx_resource_page`
  ADD PRIMARY KEY (`resourcepageId`);

--
-- Indexes for table `wkx_resource_summary`
--
ALTER TABLE `wkx_resource_summary`
  ADD PRIMARY KEY (`resourcesummaryId`);

--
-- Indexes for table `wkx_resource_text`
--
ALTER TABLE `wkx_resource_text`
  ADD PRIMARY KEY (`resourcetextId`);
ALTER TABLE `wkx_resource_text` ADD FULLTEXT KEY `resourcetextAbstract` (`resourcetextAbstract`);
ALTER TABLE `wkx_resource_text` ADD FULLTEXT KEY `resourcetextNote` (`resourcetextNote`);

--
-- Indexes for table `wkx_resource_timestamp`
--
ALTER TABLE `wkx_resource_timestamp`
  ADD PRIMARY KEY (`resourcetimestampId`),
  ADD KEY `resourcetimestampTimestampAdd` (`resourcetimestampTimestampAdd`),
  ADD KEY `resourcetimestampTimestamp` (`resourcetimestampTimestamp`);

--
-- Indexes for table `wkx_resource_user_tags`
--
ALTER TABLE `wkx_resource_user_tags`
  ADD PRIMARY KEY (`resourceusertagsId`),
  ADD KEY `resourceusertagsResourceId` (`resourceusertagsResourceId`);

--
-- Indexes for table `wkx_resource_year`
--
ALTER TABLE `wkx_resource_year`
  ADD PRIMARY KEY (`resourceyearId`),
  ADD KEY `resourceyearYear1` (`resourceyearYear1`(100));

--
-- Indexes for table `wkx_statistics`
--
ALTER TABLE `wkx_statistics`
  ADD PRIMARY KEY (`statisticsId`),
  ADD KEY `statisticsResourceId` (`statisticsResourceId`),
  ADD KEY `statisticsAttachmentId` (`statisticsAttachmentId`);

--
-- Indexes for table `wkx_subcategory`
--
ALTER TABLE `wkx_subcategory`
  ADD PRIMARY KEY (`subcategoryId`);

--
-- Indexes for table `wkx_tag`
--
ALTER TABLE `wkx_tag`
  ADD PRIMARY KEY (`tagId`);

--
-- Indexes for table `wkx_users`
--
ALTER TABLE `wkx_users`
  ADD PRIMARY KEY (`usersId`);

--
-- Indexes for table `wkx_user_bibliography`
--
ALTER TABLE `wkx_user_bibliography`
  ADD PRIMARY KEY (`userbibliographyId`),
  ADD KEY `userbibliographyTitle` (`userbibliographyTitle`(100));

--
-- Indexes for table `wkx_user_bibliography_resource`
--
ALTER TABLE `wkx_user_bibliography_resource`
  ADD PRIMARY KEY (`userbibliographyresourceId`),
  ADD KEY `userbibliographyresourceResourceId` (`userbibliographyresourceResourceId`);

--
-- Indexes for table `wkx_user_groups`
--
ALTER TABLE `wkx_user_groups`
  ADD PRIMARY KEY (`usergroupsId`);

--
-- Indexes for table `wkx_user_groups_users`
--
ALTER TABLE `wkx_user_groups_users`
  ADD PRIMARY KEY (`usergroupsusersId`);

--
-- Indexes for table `wkx_user_register`
--
ALTER TABLE `wkx_user_register`
  ADD PRIMARY KEY (`userregisterId`);

--
-- Indexes for table `wkx_user_tags`
--
ALTER TABLE `wkx_user_tags`
  ADD PRIMARY KEY (`usertagsId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wkx_bibtex_string`
--
ALTER TABLE `wkx_bibtex_string`
  MODIFY `bibtexstringId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_category`
--
ALTER TABLE `wkx_category`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wkx_collection`
--
ALTER TABLE `wkx_collection`
  MODIFY `collectionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `wkx_config`
--
ALTER TABLE `wkx_config`
  MODIFY `configId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `wkx_creator`
--
ALTER TABLE `wkx_creator`
  MODIFY `creatorId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `wkx_custom`
--
ALTER TABLE `wkx_custom`
  MODIFY `customId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_keyword`
--
ALTER TABLE `wkx_keyword`
  MODIFY `keywordId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wkx_language`
--
ALTER TABLE `wkx_language`
  MODIFY `languageId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_news`
--
ALTER TABLE `wkx_news`
  MODIFY `newsId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_plugin_soundexplorer`
--
ALTER TABLE `wkx_plugin_soundexplorer`
  MODIFY `pluginsoundexplorerId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_plugin_wordprocessor`
--
ALTER TABLE `wkx_plugin_wordprocessor`
  MODIFY `pluginwordprocessorId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_publisher`
--
ALTER TABLE `wkx_publisher`
  MODIFY `publisherId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `wkx_resource`
--
ALTER TABLE `wkx_resource`
  MODIFY `resourceId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `wkx_resource_attachments`
--
ALTER TABLE `wkx_resource_attachments`
  MODIFY `resourceattachmentsId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wkx_resource_category`
--
ALTER TABLE `wkx_resource_category`
  MODIFY `resourcecategoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `wkx_resource_creator`
--
ALTER TABLE `wkx_resource_creator`
  MODIFY `resourcecreatorId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT for table `wkx_resource_custom`
--
ALTER TABLE `wkx_resource_custom`
  MODIFY `resourcecustomId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_resource_keyword`
--
ALTER TABLE `wkx_resource_keyword`
  MODIFY `resourcekeywordId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `wkx_resource_language`
--
ALTER TABLE `wkx_resource_language`
  MODIFY `resourcelanguageId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_resource_metadata`
--
ALTER TABLE `wkx_resource_metadata`
  MODIFY `resourcemetadataId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `wkx_resource_user_tags`
--
ALTER TABLE `wkx_resource_user_tags`
  MODIFY `resourceusertagsId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_statistics`
--
ALTER TABLE `wkx_statistics`
  MODIFY `statisticsId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `wkx_subcategory`
--
ALTER TABLE `wkx_subcategory`
  MODIFY `subcategoryId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_tag`
--
ALTER TABLE `wkx_tag`
  MODIFY `tagId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wkx_users`
--
ALTER TABLE `wkx_users`
  MODIFY `usersId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wkx_user_bibliography`
--
ALTER TABLE `wkx_user_bibliography`
  MODIFY `userbibliographyId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wkx_user_bibliography_resource`
--
ALTER TABLE `wkx_user_bibliography_resource`
  MODIFY `userbibliographyresourceId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `wkx_user_groups`
--
ALTER TABLE `wkx_user_groups`
  MODIFY `usergroupsId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wkx_user_groups_users`
--
ALTER TABLE `wkx_user_groups_users`
  MODIFY `usergroupsusersId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wkx_user_register`
--
ALTER TABLE `wkx_user_register`
  MODIFY `userregisterId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wkx_user_tags`
--
ALTER TABLE `wkx_user_tags`
  MODIFY `usertagsId` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
