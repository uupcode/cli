# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-03-19

### Added
- `wp uup-plugin scaffold` command to generate a new WordPress plugin pre-wired with `uupcode/utilities`
- Interactive prompts for plugin name, vendor, description, author, and URIs
- Full stub set: `Plugin.php`, nine service providers, REST controller, AJAX request, model, webpack config, and distribution bundler
- Token replacement for slug, underscored slug, PSR-4 namespace, vendor, and all metadata fields
- Automatic `composer install` after scaffold
