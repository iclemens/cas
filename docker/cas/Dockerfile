FROM php:7.3.6-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2enmod rewrite
RUN apt-get update && apt-get install -y texlive-latex-extra
RUN apt-get install -y texlive-lang-all
