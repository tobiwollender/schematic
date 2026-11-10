# schematic

Structured data (schema.org JSON-LD) for TYPO3, maintained by editors in normal backend forms instead of hand-written JSON.

schematic is built on [brotkrueml/schema](https://github.com/brotkrueml/schema), which renders the JSON-LD, and on [Content Blocks](https://github.com/FriendsOfTYPO3/content-blocks), which defines the forms. It adds the editor layer: which data exists, where editors maintain it and how page types fill it automatically.

## Contents

- [How it works](#how-it-works)
- [For editors](#for-editors)
- [For integrators](#for-integrators)
- [For developers](#for-developers)
- [Requirements](#requirements)
- [License](#license)

## How it works

Every page can carry structured data from three sources:

| Source | Maintained in | Output |
| --- | --- | --- |
| Organization of the site | a record in a folder, selected on the root page | in full (Organization or LocalBusiness plus WebSite) on the home page only. Other pages reference it as employer, publisher or organizer. |
| Structured data of the page | tab "Structured data" in the page properties, or automatically from the fields of a page type | Article, BlogPosting, NewsArticle, Event or JobPosting |
| FAQ | content element "FAQ" | FAQPage |

Empty fields fall back to the page: title, description, first image of the page and creation date.

Supported schema.org types: Organization, LocalBusiness, WebSite, Article, BlogPosting, NewsArticle, Event, JobPosting, FAQPage.

## For editors

**Organization.** Maintain name, logo, contact, address, social profiles and opening hours once as a record. Select it on the root page in the tab "Structured data".

**Structured data of a page.** Open the page properties, tab "Structured data", add a record and pick the type. The form then shows the fields of that type. Leave a field empty to use the matching page value.

**Page types with a fixed type.** On some page types, for example a job page, the type is set by the integrator. The type cannot be changed, and fields the page type already has are hidden. Only fill in what is missing.

**FAQ.** Add the content element "FAQ" and enter questions and answers. They appear as expandable entries on the page and as FAQPage in the structured data.

**Preview.** Every page form shows the JSON-LD of the saved state in the language of the page, with links to the Schema Markup Validator and the Google Rich Results Test. Both tools only work for publicly reachable pages.

**Translations.** Records and FAQ entries are translated like any other content. Each language outputs its own values.

**Workspaces.** Changes made in a workspace show up in the preview of that workspace only. The live site outputs them after publishing.

## For integrators

### Installation

Check the [requirements](#requirements) first, then install via Composer:

```bash
composer require tobiwollender/schematic
```

If the package is not available on Packagist in your setup, add the repository as a VCS source in the project's `composer.json` first:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/tobiwollender/schematic.git" }
]
```

Add the site set `tobiwollender/schematic` to the site configuration and run `typo3 extension:setup`. Then create the organization record in a folder and select it on the root page.

### Configuration

| Setting | Default | Effect |
| --- | --- | --- |
| `hideWebPageTypeField` | on | Hides "Type of web page" of EXT:schema in the page properties. The column stays, existing values keep working. Flush caches after changing. |

Recommended setting of EXT:schema: disable "Automatic embedding of the WebPage schema" (`automaticWebPageSchemaGeneration`). Without data of its own, the WebPage node stays empty.

### Fixed types for Content Blocks page types

Put a `schema.yaml` next to the `config.yaml` of a Content Blocks page type. `content-blocks:lint` ignores this file.

```yaml
type: JobPosting      # Article, BlogPosting, NewsArticle, Event or JobPosting
locked: true          # false: only preselect the type, editors may change it
properties:
  title: title                     # core page field
  description: teaser              # field identifier from config.yaml
  employmentType: employmentType
  validThrough: validUntil
```

- Keys are schema.org property names, the same names the fields in `ContentBlocks/RecordTypes/` use.
- Values are field identifiers from the page type or core page columns.
- Values from the page type win over values from the structured data record. Mapped fields are hidden in the structured data form.

If a select field stores its own values, translate them with the long form. Values without an entry are left out, duplicates are merged.

```yaml
properties:
  employmentType:
    field: employmentType
    values:
      vollzeit: FULL_TIME
      teilzeit: PART_TIME
      minijob: PART_TIME
```

### Custom JSON-LD for special cases

Admins find the field "Custom JSON-LD" in the tab "Structured data". Other editors need the exclude field to be granted. When filled, it replaces the structured data of the page and its FAQ; the organization on the home page stays. It is output as its own script element, so any schema.org vocabulary works. TYPO3 discards invalid JSON on save and keeps the previous value.

### FAQ template

The template is `ContentBlocks/ContentElements/faq/templates/frontend.html` and can be overridden per project the Content Blocks way.

## For developers

### Test instance

The repository contains a DDEV setup with TYPO3 14.3, Content Blocks and demo content.

```bash
ddev start
ddev schematic-setup
```

`ddev schematic-setup` installs a fresh instance and can be run again at any time to reset it. Frontend: https://schematic.ddev.site, backend user `admin`, the password is in `.ddev/commands/web/schematic-setup`.

| Page | Shows |
| --- | --- |
| Startseite | LocalBusiness and WebSite from the organization record in "Stammdaten" |
| Karriere → Werkstudent Marketing | Content Blocks page type "Stellenanzeige" with locked type JobPosting and mapped select values |
| Blog → Strukturierte Daten in TYPO3 pflegen | free choice: BlogPosting |
| Tag der offenen Tür | free choice: Event |
| FAQ | FAQ content element, output as FAQPage |
| Über uns | no structured data of its own, to try the free choice |
| /en/… | English translations of home, careers, job and FAQ page |

The instance has a workspace "Entwurf". Structured data follows workspace versions in the backend preview and in the frontend workspace preview.

To check TYPO3 13.4, then switch back:

```bash
ddev composer update -W --with typo3/cms-core:^13.4
ddev schematic-setup
ddev composer update -W
```

### Adding a field or a type

All record types are Content Blocks in `ContentBlocks/RecordTypes/`, the FAQ element is in `ContentBlocks/ContentElements/faq/`. Tables, columns and forms are generated from these files.

- Name a field after the schema.org property it fills, for example `identifier` or `directApply`. Simple values are passed on without PHP. Only nested structures such as address, salary, location and offers are built in `Classes/Builder/TypeAssembler.php`.
- Labels follow the Content Blocks convention: `language/labels.xlf` and `language/de.labels.xlf` in each block. Write the English label into the YAML, run `typo3 content-blocks:language:generate <name>`, remove the label from the YAML and add the German target.
- Fields used by several types live in `ContentBlocks/Basics/` and take their labels from `Resources/Private/Language/locallang_db.xlf` via `LLL:`, so each translation exists once.
- A property that EXT:schema does not know yet (schema.org "pending") is registered like `Classes/Schema/JobPostingAdditionalProperties.php`.
- Run `typo3 content-blocks:lint` on TYPO3 13 and 14. Content Blocks 1.6 is stricter in places, for example `size` must be 10 or more.

### Checks and releases

- Unit tests live in `Tests/Unit` and need no database: `ddev exec .Build/bin/phpunit`.
- CI runs Composer validation, PHP lint, `content-blocks:lint` and the unit tests on TYPO3 13.4 and 14.3.
- A release is a git tag `vX.Y.Z` on `main` plus a GitHub release with notes. The version comes from the tag only.
- `.gitattributes` keeps `.ddev/`, `.github/`, `Build/` and `Tests/` out of the archives Composer installs.

## Requirements

- TYPO3 13.4 LTS or 14.3 LTS
- PHP 8.2+
- [brotkrueml/schema](https://github.com/brotkrueml/schema) 4.4+
- [friendsoftypo3/content-blocks](https://github.com/FriendsOfTYPO3/content-blocks) 1.6 (TYPO3 13) or 2.4 (TYPO3 14)

## License

GPL-2.0-or-later, see [LICENSE](LICENSE).

## Credits

Created by [Tobi Wollender](https://www.wollender.com).
