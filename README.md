<p align="center">
  <a href="https://module-loader.de/" target="_blank" >
    <img alt="MMLC - Modified Module Loader Client" src="https://module-loader.de/images/mmlc-logo-transparent.png" width="200">
  </a>
</p>

# MMLC - Modified Module Loader Client

[![dicord](https://img.shields.io/discord/727190419158597683)](https://discord.gg/9NqwJqP)
[![CI](https://github.com/RobinTheHood/ModifiedModuleLoaderClient/actions/workflows/integration.yml/badge.svg)](https://github.com/RobinTheHood/ModifiedModuleLoaderClient/actions/workflows/integration.yml)


Der MMLC ist eine Software zur Verwaltung von Modulen in deinem modified eCommerce Shop.

<img alt="MMLC - Modul Übersichtsseite" src="https://module-loader.de/images/Modul_Uebersichtsseite_mit_Schatten.png">

## Installation und Anleitung für Shopbetreiber

Folge der Installationsanleitung auf [module-loader.de](https://module-loader.de).

## Installationsanleitung für Developer
...

## MMLC - Modified Module Loader Client CLI

🚀 The Modified Module Loader Client (MMLC) CLI is a powerful command-line tool for managing modules in your modified eCommerce shop. It simplifies the process of installing, updating, and uninstalling modules, making it a breeze for both shop owners and developers.

### Features

- **📦 Effortless Module Management**: MMLC CLI allows you to easily install and uninstall modules over the terminal.
- **💻 Developer-Friendly**: For developers, MMLC CLI makes module development more enjoyable. You can create, update, and manage modules effortlessly.
- **🧰 Interactive Module Creation**: Create new modules in MMLC CLI with ease. The interactive mode guides you through the module creation process.
- **👀 Wachting for Changes**: The `watch` command automatically detects and applies file changes, making it invaluable for module development.
- **🗑️ Discard Changes**: If you need to revert changes to a module, the `discard` command lets you do it quickly, with the option to enforce the discard.

## Getting Started

1. **Installation**: Install MMLC by following the installation instructions in the [documentation](link-to-documentation).
2. **Setup**: Open your shell and go into the ModifiyModuleLoaderClient directory.
3. **Usage**: Use MMLC's simple commands to manage your modules. Enter the following command into the shell to display a list of all available commands.

```bash
./mmlc
```

#### Nativ PHP-Host Environment 
```bash
./mmlc
./mmlc watch
```

#### DDEV Envionment
If you have a DDEV development environment, some file transactions in link mode may not be synchronized with your Docker container. If you are working in MMLC link mode, try calling the MMLC CLI via DDEV.

```bash
ddev exec --dir=/var/www/html/ModifiedModuleLoaderClient ./mmlc
ddev exec --dir=/var/www/html/ModifiedModuleLoaderClient ./mmlc watch
```

## Requirements
- PHP 7.4 or above
- modified 2.0.6.0 to 3.0.0

## Authors
- Robin Wieschendorf | <mail@robinwieschendorf.de> | [robinwieschendorf.de](https://robinwieschendorf.de)

## Contributing
We would be happy if you would like to take part in the development of this module. If you wish more features or you want to make improvements or to fix errors feel free to contribute. In order to contribute, you just have to fork this repository and make pull requests.

### Coding Style
We are using:
- [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1/)
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)

### Version and Commit-Messages
We are using:
- [Semantic Versioning 2.0.0](https://semver.org)
- [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)
