# schematic

Editor-friendly schema.org structured data for TYPO3 13.4 LTS and 14.

Editors maintain structured data in backend forms instead of pasting JSON-LD from external generators into HTML content elements. Type model and rendering come from [brotkrueml/schema](https://github.com/brotkrueml/schema); this extension adds the editor layer on top.

## Status

Phase 0 (spike). Internal evaluation, not for production use.

## Planned scope

- **Organization / LocalBusiness** as a record, linked from the root page. Full output on the home page, short `@id` reference on subpages where another type needs it (`hiringOrganization`, `publisher`, `organizer`).
- **FAQ content element** with question/answer pairs. Its Fluid template is meant to be overridden per project.
- **Mapping layer** that turns existing page properties and records (jobs, news, events) into schema.org types via site set configuration, so editors do not maintain data twice.
- **Admin-only JSON-LD field** for edge cases and migrating existing HTML-element snippets.

## Requirements

- PHP 8.2+
- TYPO3 13.4 LTS or 14.3+
- brotkrueml/schema 4.4+

## Installation

The repository is private. Add it as a VCS repository in the project's `composer.json`:

```json
"repositories": [
    { "type": "vcs", "url": "git@github.com:tobiwollender/schematic.git" }
]
```

```bash
composer require tobiwollender/schematic:@dev
```

Then add the site set `tobiwollender/schematic` to the site configuration.
