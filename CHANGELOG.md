# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [3.0.1] - 2023-07-09

## Added
- Support for php-di/php-di v7

## [3.0.0] - 2023-07-09

This release streamlines this package and requires users to explicitly require the packages they use. 
This package is no longer functional as a meta package to include all dependent repositories as this introduced a lot
of unnecessary major version bumps or risked breaking projects depending on this package by doing major version upgrades 
to dependencies.

## Added

- Dependency on `szemul/bootstrap`

## Removed

- Dependency on `PDO`, `josegonsalez/dotenv`, `symfony/console`, `szemul/database`, `szemul/debugger`, `szemul/error-handler`, `szemul/helper`, `szemul/not-set-value`, `szemul/slim-error-handler-bridge`
- Bootstrappers have been moved to new packages or in the packages they are bootstrapping
- `DaoAbstract` and `EntityNotFoundException` have been moved to the `szemul/database` project
- Robo classes have been moved to `szemul/robo`
- The `RouterInterface` has been moved to `szemul/router`
- All not necessary dev dependencies

## [2.0.0] - 2023-06-28

### Modified

- Allowing version ^3.0.0 of `szemul/slim-error-handler-bridge` library

## [1.1.8] - 2023-03-21

### Added

- Allowing version 6 of `symfony/console` library

## [1.1.6] - 2022-10-03

### Added

- Allowing version 3 of `szemul/database` library
