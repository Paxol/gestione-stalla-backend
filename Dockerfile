FROM php:8.0.2-apache

RUN docker-php-ext-install mysqli
