-- --------------------------------------------------------
-- Server:                       127.0.0.1
-- Versiune server:              5.1.37 - Source distribution
-- SO server:                    Win32
-- HeidiSQL Versiune:            12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Descarcă structura bazei de date pentru norme
CREATE DATABASE IF NOT EXISTS `norme` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `norme`;

-- Descarcă structura pentru tabelă norme.articole
CREATE TABLE IF NOT EXISTS `articole` (
  `codArticol` int(10) NOT NULL DEFAULT '0',
  `codCapitol` int(10) DEFAULT NULL,
  `Simbol` varchar(50) DEFAULT NULL,
  `Denumire` text,
  `Data` date DEFAULT NULL,
  PRIMARY KEY (`codArticol`),
  KEY `codCapitol` (`codCapitol`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.articolevariante
CREATE TABLE IF NOT EXISTS `articolevariante` (
  `Simbol` varchar(50) DEFAULT NULL,
  `codArticol` int(10) NOT NULL DEFAULT '0',
  `codCapitol` int(10) NOT NULL DEFAULT '0',
  `codVarianta` int(10) NOT NULL DEFAULT '0',
  `Um` varchar(50) DEFAULT NULL,
  `Denumire` text,
  `Data` date DEFAULT NULL,
  `Green` tinyint(4) DEFAULT NULL,
  KEY `codArticol` (`codArticol`),
  KEY `codCapitol` (`codCapitol`),
  KEY `codVarianta` (`codVarianta`),
  FULLTEXT KEY `Simbol` (`Simbol`),
  FULLTEXT KEY `Denumire` (`Denumire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.capitole
CREATE TABLE IF NOT EXISTS `capitole` (
  `codCapitol` int(10) NOT NULL DEFAULT '0',
  `codIndicator` int(10) DEFAULT NULL,
  `Simbol` varchar(50) DEFAULT NULL,
  `Denumire` text,
  PRIMARY KEY (`codCapitol`),
  KEY `codIndicator` (`codIndicator`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.colectii
CREATE TABLE IF NOT EXISTS `colectii` (
  `codColectie` int(10) NOT NULL DEFAULT '0',
  `denumire` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`codColectie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.curseuro
CREATE TABLE IF NOT EXISTS `curseuro` (
  `CodCurs` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Curs` decimal(25,10) DEFAULT NULL,
  `Data` date DEFAULT NULL,
  PRIMARY KEY (`CodCurs`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.devizonline_text
CREATE TABLE IF NOT EXISTS `devizonline_text` (
  `cod_text` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cod_modul` int(10) unsigned DEFAULT NULL,
  `text_ro` text CHARACTER SET utf8 COLLATE utf8_bin,
  `text_en` text CHARACTER SET utf8 COLLATE utf8_bin,
  PRIMARY KEY (`cod_text`)
) ENGINE=MyISAM AUTO_INCREMENT=240 DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.generalprices
CREATE TABLE IF NOT EXISTS `generalprices` (
  `GeneralPriceId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Symbol` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Name` text COLLATE utf8_unicode_ci NOT NULL,
  `MeasureUnit` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Supplier` text COLLATE utf8_unicode_ci NOT NULL,
  `SupplierId` int(10) unsigned NOT NULL DEFAULT '0',
  `SupplierOfferId` int(10) unsigned NOT NULL DEFAULT '0',
  `ProvenienceType` tinyint(1) unsigned NOT NULL,
  `Price` decimal(32,17) NOT NULL DEFAULT '0.00000000000000000',
  `Rating` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `Date` date NOT NULL,
  PRIMARY KEY (`GeneralPriceId`) USING BTREE,
  KEY `SupplierId` (`SupplierId`) USING BTREE,
  KEY `SupplierOfferId` (`SupplierOfferId`) USING BTREE,
  KEY `Symbol` (`Symbol`) USING BTREE,
  KEY `Name` (`Name`(333)) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.icc
CREATE TABLE IF NOT EXISTS `icc` (
  `Cod_indice` int(10) NOT NULL,
  `Data` varchar(50) DEFAULT NULL,
  `Materiale` decimal(5,2) DEFAULT NULL,
  `Total` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`Cod_indice`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.indicatoare
CREATE TABLE IF NOT EXISTS `indicatoare` (
  `codIndicator` int(10) NOT NULL DEFAULT '0',
  `codColectie` int(10) DEFAULT NULL,
  `Simbol` varchar(50) DEFAULT NULL,
  `Denumire` text,
  PRIMARY KEY (`codIndicator`),
  KEY `codColectie` (`codColectie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.indici
CREATE TABLE IF NOT EXISTS `indici` (
  `CodIndice` int(10) NOT NULL,
  `Aplicat` varchar(50) DEFAULT NULL,
  `Tip` varchar(50) DEFAULT NULL,
  `Denumire` varchar(300) NOT NULL,
  PRIMARY KEY (`CodIndice`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.linkrecapitulatiiindici
CREATE TABLE IF NOT EXISTS `linkrecapitulatiiindici` (
  `CodRecapitulatie` int(10) NOT NULL,
  `CodIndice` int(10) NOT NULL,
  `Valoare` decimal(25,10) DEFAULT NULL,
  `Pozitie` int(10) NOT NULL DEFAULT '0',
  `TipValoare` varchar(300) NOT NULL DEFAULT '%',
  PRIMARY KEY (`CodRecapitulatie`,`CodIndice`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.link_listaresurse_manopera
CREATE TABLE IF NOT EXISTS `link_listaresurse_manopera` (
  `codListaResurse` int(10) NOT NULL DEFAULT '0',
  `codManopera` int(10) NOT NULL DEFAULT '0',
  `Pozitie` int(10) DEFAULT NULL,
  `Raport` float DEFAULT NULL,
  KEY `codListaResurse` (`codListaResurse`),
  KEY `codManopera` (`codManopera`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.link_listaresurse_material
CREATE TABLE IF NOT EXISTS `link_listaresurse_material` (
  `codListaResurse` int(10) NOT NULL DEFAULT '0',
  `codMaterial` int(10) NOT NULL DEFAULT '0',
  `Pozitie` int(10) DEFAULT NULL,
  `Raport` float DEFAULT NULL,
  KEY `codListaResurse` (`codListaResurse`),
  KEY `codMaterial` (`codMaterial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.link_listaresurse_transport
CREATE TABLE IF NOT EXISTS `link_listaresurse_transport` (
  `codListaResurse` int(10) NOT NULL DEFAULT '0',
  `codTransport` int(10) NOT NULL DEFAULT '0',
  `Pozitie` int(10) DEFAULT NULL,
  `Raport` float DEFAULT NULL,
  KEY `codListaResurse` (`codListaResurse`),
  KEY `codTransport` (`codTransport`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.link_listaresurse_utilaj
CREATE TABLE IF NOT EXISTS `link_listaresurse_utilaj` (
  `codListaResurse` int(10) NOT NULL DEFAULT '0',
  `codUtilaj` int(10) NOT NULL DEFAULT '0',
  `Pozitie` int(10) DEFAULT NULL,
  `Raport` float DEFAULT NULL,
  KEY `codListaResurse` (`codListaResurse`),
  KEY `codUtilaj` (`codUtilaj`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.link_variante_listaresurse
CREATE TABLE IF NOT EXISTS `link_variante_listaresurse` (
  `codVarianta` int(10) NOT NULL DEFAULT '0',
  `codListaResurse` int(10) NOT NULL DEFAULT '0',
  `Pozitie` int(10) DEFAULT NULL,
  `Cantitate` decimal(25,10) DEFAULT NULL,
  `Formula` text,
  `NumeGeneric` text,
  `UM` varchar(50) DEFAULT NULL,
  KEY `codVarianta` (`codVarianta`),
  KEY `codListaResurse` (`codListaResurse`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.link_variante_manopera
CREATE TABLE IF NOT EXISTS `link_variante_manopera` (
  `codVarianta` int(10) NOT NULL DEFAULT '0',
  `codManopera` int(10) NOT NULL DEFAULT '0',
  `Pozitie` int(10) DEFAULT NULL,
  `Cantitate` decimal(25,10) DEFAULT NULL,
  `Formula` text,
  KEY `codVarianta` (`codVarianta`),
  KEY `codManopera` (`codManopera`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.link_variante_material
CREATE TABLE IF NOT EXISTS `link_variante_material` (
  `codVarianta` int(10) NOT NULL DEFAULT '0',
  `CodMaterial` int(10) NOT NULL DEFAULT '0',
  `Pozitie` int(10) DEFAULT NULL,
  `Cantitate` decimal(25,10) DEFAULT NULL,
  `Formula` text,
  KEY `codVarianta` (`codVarianta`),
  KEY `CodMaterial` (`CodMaterial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.link_variante_transport
CREATE TABLE IF NOT EXISTS `link_variante_transport` (
  `codVarianta` int(10) NOT NULL DEFAULT '0',
  `codTransport` int(10) NOT NULL DEFAULT '0',
  `Pozitie` int(10) DEFAULT NULL,
  `Cantitate` decimal(25,10) DEFAULT NULL,
  `Formula` text,
  KEY `codVarianta` (`codVarianta`),
  KEY `codTransport` (`codTransport`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.link_variante_utilaj
CREATE TABLE IF NOT EXISTS `link_variante_utilaj` (
  `codVarianta` int(10) NOT NULL DEFAULT '0',
  `codUtilaj` int(10) NOT NULL DEFAULT '0',
  `Pozitie` int(10) DEFAULT NULL,
  `Cantitate` decimal(25,10) DEFAULT NULL,
  `Formula` text,
  KEY `codVarianta` (`codVarianta`),
  KEY `codUtilaj` (`codUtilaj`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.listaresurse
CREATE TABLE IF NOT EXISTS `listaresurse` (
  `codListaResurse` int(10) NOT NULL DEFAULT '0',
  `codEx` varchar(50) DEFAULT NULL,
  `Denumire` varchar(800) DEFAULT NULL,
  `Tip` int(10) DEFAULT NULL,
  PRIMARY KEY (`codListaResurse`),
  KEY `codEx` (`codEx`),
  KEY `Denumire` (`Denumire`(255))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.liste
CREATE TABLE IF NOT EXISTS `liste` (
  `Cod` int(11) NOT NULL,
  `Denumire` text NOT NULL,
  `UM` text NOT NULL,
  PRIMARY KEY (`Cod`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.manopera
CREATE TABLE IF NOT EXISTS `manopera` (
  `codManopera` int(10) NOT NULL DEFAULT '0',
  `codEx` varchar(50) DEFAULT NULL,
  `Denumire` varchar(800) DEFAULT NULL,
  `UM` varchar(50) DEFAULT NULL,
  `Pret` decimal(25,10) DEFAULT NULL,
  PRIMARY KEY (`codManopera`),
  KEY `codEx` (`codEx`),
  KEY `Denumire` (`Denumire`(255))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.materialoferta
CREATE TABLE IF NOT EXISTS `materialoferta` (
  `codMaterialOferta` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codPartener` int(10) unsigned NOT NULL DEFAULT '0',
  `codEx` varchar(50) NOT NULL DEFAULT '',
  `Categorie` varchar(50) NOT NULL DEFAULT '',
  `Denumire` varchar(800) NOT NULL DEFAULT '',
  `UM` varchar(50) NOT NULL DEFAULT '',
  `Pret` decimal(25,10) NOT NULL DEFAULT '0.0000000000',
  `Moneda` varchar(50) NOT NULL DEFAULT '',
  `Descriere` text NOT NULL,
  `Categorii` text NOT NULL,
  `Specificatii` text NOT NULL,
  `Brand` varchar(50) NOT NULL DEFAULT '',
  `Dimensiuni` varchar(100) NOT NULL DEFAULT '',
  `PozaURL` text NOT NULL,
  `ProdusURL` text NOT NULL,
  `Data` date NOT NULL DEFAULT '0000-00-00',
  `Status` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`codMaterialOferta`),
  KEY `codPartener` (`codPartener`),
  KEY `Categorie` (`Categorie`),
  KEY `Data` (`Data`),
  FULLTEXT KEY `Denumire_codEx` (`Denumire`,`codEx`)
) ENGINE=MyISAM AUTO_INCREMENT=54639 DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.materialtehnologic
CREATE TABLE IF NOT EXISTS `materialtehnologic` (
  `codMaterial` int(10) NOT NULL DEFAULT '0',
  `codEx` varchar(50) DEFAULT NULL,
  `Denumire` varchar(800) DEFAULT NULL,
  `UM` varchar(50) DEFAULT NULL,
  `Greutate` decimal(25,10) DEFAULT NULL,
  `Pret` decimal(25,10) DEFAULT NULL,
  PRIMARY KEY (`codMaterial`),
  KEY `codEx` (`codEx`),
  KEY `Denumire` (`Denumire`(333))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.optiuni
CREATE TABLE IF NOT EXISTS `optiuni` (
  `CodOptiune` int(10) NOT NULL AUTO_INCREMENT,
  `Tip` varchar(50) DEFAULT NULL,
  `Optiune` varchar(50) DEFAULT NULL,
  `ValImplicita` varchar(50) DEFAULT NULL,
  `Data` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`CodOptiune`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.parametri
CREATE TABLE IF NOT EXISTS `parametri` (
  `CodParametru` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Simbol` varchar(50) NOT NULL,
  `Denumire` text NOT NULL,
  `ValImplicita` decimal(25,10) DEFAULT '0.0000000000',
  PRIMARY KEY (`CodParametru`),
  KEY `Simbol` (`Simbol`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.parteneri
CREATE TABLE IF NOT EXISTS `parteneri` (
  `codPartener` int(10) NOT NULL DEFAULT '0',
  `Denumire` varchar(50) DEFAULT NULL,
  `Localitate` varchar(50) DEFAULT NULL,
  `CodPostal` varchar(50) DEFAULT NULL,
  `Telefon` varchar(50) DEFAULT NULL,
  `Fax` varchar(50) DEFAULT NULL,
  `Mobil` varchar(50) DEFAULT NULL,
  `Email` text,
  `CodFiscal` varchar(50) DEFAULT NULL,
  `RegCom` varchar(50) DEFAULT NULL,
  `Cont` varchar(50) DEFAULT NULL,
  `Banca` varchar(50) DEFAULT NULL,
  `Contact` varchar(50) DEFAULT NULL,
  `CNPContact` varchar(50) DEFAULT NULL,
  `Atribute` varchar(50) DEFAULT NULL,
  `CodCM` int(10) DEFAULT NULL,
  `GrupaCM` varchar(300) DEFAULT NULL,
  `BifaFiltru` enum('True','False') DEFAULT NULL,
  `Adresa` varchar(300) DEFAULT NULL,
  `TreeJud` enum('True','False') DEFAULT NULL,
  `PreturiPublice` tinyint(3) DEFAULT '1',
  `PreturiActuale` tinyint(3) DEFAULT '0',
  `codClient` int(10) DEFAULT NULL,
  `Utilaj` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`codPartener`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.pondere_materiale
CREATE TABLE IF NOT EXISTS `pondere_materiale` (
  `Cod_pondere` int(10) NOT NULL,
  `Tip_proiect` varchar(50) DEFAULT NULL,
  `Pondere` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`Cod_pondere`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.recapitulatii
CREATE TABLE IF NOT EXISTS `recapitulatii` (
  `CodRecapitulatie` int(10) NOT NULL,
  `Tip` int(10) NOT NULL DEFAULT '0',
  `Denumire` varchar(300) NOT NULL,
  `Flag` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`CodRecapitulatie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.specificatii
CREATE TABLE IF NOT EXISTS `specificatii` (
  `codSpecificatie` int(10) DEFAULT NULL,
  `Simbol` varchar(50) DEFAULT NULL,
  `Descriere` longtext
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.template
CREATE TABLE IF NOT EXISTS `template` (
  `Id` int(11) DEFAULT NULL,
  `ParentId` int(11) DEFAULT NULL,
  `TemplateId` int(11) DEFAULT NULL,
  `Type` varchar(50) CHARACTER SET utf8 COLLATE utf8_romanian_ci DEFAULT NULL,
  `Symbol` varchar(50) CHARACTER SET utf8 COLLATE utf8_romanian_ci DEFAULT NULL,
  `Name` text CHARACTER SET utf8 COLLATE utf8_romanian_ci,
  `Position` int(11) DEFAULT NULL,
  `DG` varchar(11) DEFAULT NULL,
  `UM` varchar(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.transport
CREATE TABLE IF NOT EXISTS `transport` (
  `codTransport` int(10) NOT NULL DEFAULT '0',
  `codEx` varchar(50) DEFAULT NULL,
  `Denumire` varchar(800) DEFAULT NULL,
  `UM` varchar(50) DEFAULT NULL,
  `Pret` decimal(25,10) DEFAULT NULL,
  PRIMARY KEY (`codTransport`),
  KEY `codEx` (`codEx`),
  KEY `Denumire` (`Denumire`(255))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.utilaj
CREATE TABLE IF NOT EXISTS `utilaj` (
  `codUtilaj` int(10) NOT NULL DEFAULT '0',
  `codEx` varchar(50) DEFAULT NULL,
  `Denumire` varchar(800) DEFAULT NULL,
  `Tip` varchar(50) DEFAULT NULL,
  `UM` varchar(50) DEFAULT NULL,
  `Pret` decimal(25,10) DEFAULT NULL,
  PRIMARY KEY (`codUtilaj`),
  UNIQUE KEY `codUtilaj` (`codUtilaj`),
  KEY `codEx` (`codEx`),
  KEY `Denumire` (`Denumire`(255))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.variante
CREATE TABLE IF NOT EXISTS `variante` (
  `codVarianta` int(10) NOT NULL DEFAULT '0',
  `codArticol` int(10) DEFAULT NULL,
  `Simbol` varchar(50) DEFAULT NULL,
  `Denumire` text,
  `Valoare` decimal(25,10) unsigned DEFAULT NULL,
  `Um` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`codVarianta`),
  KEY `codArticol` (`codArticol`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme.versiune
CREATE TABLE IF NOT EXISTS `versiune` (
  `Versiune` int(10) DEFAULT NULL,
  `Tara` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

-- Descarcă structura pentru tabelă norme._traducere
CREATE TABLE IF NOT EXISTS `_traducere` (
  `CodCuvant` int(10) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `ro` varchar(255) NOT NULL,
  `en` varchar(255) NOT NULL,
  `test` varchar(255) NOT NULL,
  `categorie` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`key`),
  KEY `CodCuvant` (`CodCuvant`)
) ENGINE=MyISAM AUTO_INCREMENT=1466 DEFAULT CHARSET=utf8;

-- Exportarea datelor nu a fost selectată.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
