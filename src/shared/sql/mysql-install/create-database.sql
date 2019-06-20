--
-- Citrus-IT Administratie Systeem
-- Database creation script for MySQL 5
-- 
-- Database: boekhouding
-- 

CREATE DATABASE IF NOT EXISTS boekhouding DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL ON boekhouding.* TO 'projectcas'@'localhost' IDENTIFIED BY 'projectcas';
GRANT ALL ON boekhouding.* TO 'projectcas'@'%' IDENTIFIED BY 'projectcas';

