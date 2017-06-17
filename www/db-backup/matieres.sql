-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le : Sam 17 Juin 2017 à 09:25
-- Version du serveur: 5.5.50
-- Version de PHP: 5.3.10-1ubuntu3.24

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `montserrat`
--

-- --------------------------------------------------------

--
-- Structure de la table `matieres`
--

CREATE TABLE IF NOT EXISTS `matieres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tri` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `descriptif` text NOT NULL,
  `image` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `lettrage_type` varchar(50) NOT NULL,
  `fond_type` varchar(50) NOT NULL,
  `actif` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Contenu de la table `matieres`
--

INSERT INTO `matieres` (`id`, `tri`, `nom`, `descriptif`, `image`, `type`, `lettrage_type`, `fond_type`, `actif`) VALUES
(1, 1, 'PLAQUE PROPLEXIGLASS   transparente', 'PLAQUE PROPLEXIGLASS   transparente-  5 mm\r\nFIXATION ALU COMPRISE\r\n', '', '1', 'transparent', 'null', 1),
(2, 2, 'PLAQUE PRO PLEXIGLASS  fond couleur', 'PLAQUE PRO PLEXIGLASS  fond couleur-  5 mm', '', '1', '', 'couleur', 1),
(3, 3, 'plaque alu brossé or et argent', 'plaque alu brossé or et argent', '', '1', 'or', 'argent', 1),
(6, 4, 'PLAQUE PRO PLEXIGLASS  depoli-  5 mm', 'PLAQUE PRO PLEXIGLASS  depoli-  5 mm', '', '1', 'couleur', '', 1),
(7, 5, 'PLAQUE PRO dibond', 'PLAQUE PRO dibond 3 mm FIXATION ALU COMPRISE\r\n', '', '1', 'couleur', '', 1),
(8, 6, 'DOUBLE PLAQUE PRO', 'DOUBLE PLAQUE PRO', '', '2', 'couleur', 'null', 1),
(9, 7, 'Laiton', 'LAITON', '', '1', 'couleur', 'null', 1),
(12, 8, 'Plaque gravé or et noir', 'Plaque gravé or et noir\r\n39,90 ht la plaque en 30x20 uniquement\r\n', '', '1', 'or', 'noir', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
