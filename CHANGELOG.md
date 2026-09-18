# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1]

### Added

- `event` JSON-LD schema builder (`Crustum\Meta\Schema\Event`) with `name`, `description`, `startDate`, `endDate`, `doorTime`, `organizer`, and `offers`, registered as a first-class `SchemaFactory` type

## [1.0.0]

Initial release of `crustum/meta` (`Crustum\Meta`).

CakePHP 5 plugin for document head management: a fluent builder, route metadata, error pages, Open Graph / Twitter cards, JSON-LD schemas, and a view helper. See `docs/index.md`.

### Added

- `HeadManager` / `HeadBuilder` fluent API — title, description, canonical, robots, theme color, app metadata, icons, PWA, performance hints, and custom meta / link tags
- Four-layer resolution: page defaults, route metadata, runtime metadata, and error pages
- Route head metadata via `RouteAttributeParser` (Cake route options, layered `head` metadata, resource `connectOptions`)
- `Crustum/Meta.Head` view helper (`HeadHelper`) with `render()`, `toArray()`, and `toElements()`
- Open Graph and Twitter cards, including repeatable media and `Media` enum conditions
- Built-in JSON-LD schema builders (`article`, `blogPosting`, `product`, `offer`, `brand`, `breadcrumbs`, `faq`, `organization`, `person`, `webPage`, `webSite`) plus custom schema registration
- Pagination `rel="prev"` / `rel="next"` links via Cake `PaginatorHelper` / `PaginatedInterface`
- `when()` / `unless()` conditional metadata
- DI wiring via `MetaPlugin::services()` + `ContainerRegistry`
- Config: `config/meta.php`
- Public docs: `docs/index.md`, `docs/Versions.md`
