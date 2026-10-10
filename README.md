# schematic

Editor-friendly schema.org structured data for TYPO3 13.4 LTS and 14.3 LTS.

Editors maintain structured data in backend forms instead of pasting JSON-LD from external generators into HTML content elements. Type model and JSON-LD rendering come from [brotkrueml/schema](https://github.com/brotkrueml/schema); this extension adds the editor layer on top.

## Status

MVP for internal evaluation. Not for production use yet.

## What editors get

| Where | What |
| --- | --- |
| Root page → tab "Structured data" | Select the organization of the site. It is output in full (Organization or LocalBusiness plus WebSite) on the home page only. Subpages reference it as employer, publisher or organizer. |
| Folder with records | The organization itself: name, logo, contact, address, social profiles, opening hours. |
| Any page → tab "Structured data" | Pick a type freely: Article, BlogPosting, NewsArticle, Event or JobPosting. Empty fields fall back to page title, description, first page image and creation date. |
| Page types with a fixed type | The type is preselected and locked. Fields the page type already has are hidden, editors only fill what is missing. |
| Content element "FAQ" | Questions and answers, rendered as `<details>` and output as FAQPage. The template is `ContentBlocks/ContentElements/faq/templates/frontend.html` and can be overridden per project the Content Blocks way. |
| Every page form, admins only | "Custom JSON-LD" for special cases. When filled, it replaces the page's own type and FAQ; the organization on the home page stays. It is output as its own script element, so any schema.org vocabulary works. TYPO3 discards invalid JSON on save and keeps the previous value. |
| Every page form | A read-only JSON-LD preview of the saved state in the language of the edited page, with links to the Schema Markup Validator and the Rich Results Test. |

## Fixed types for Content Blocks page types

Put a `schema.yaml` next to the `config.yaml` of a Content Blocks page type. `content-blocks:lint` ignores this file, `config.yaml` itself stays untouched.

```yaml
type: JobPosting      # Article, BlogPosting, NewsArticle, Event or JobPosting
locked: true          # false: only preselect the type, editors may change it
properties:
  title: title                     # core page field
  description: teaser              # field identifier from config.yaml
  employmentType: employmentType
  validThrough: validUntil
```

Keys are schema.org property names, the same names the fields in `ContentBlocks/RecordTypes/` use. Values are field identifiers from the page type or core page columns. Values from the page type win over values from the structured data record, and mapped fields are hidden in the structured data form.

## Adding a field or a type

All record types are Content Blocks in `ContentBlocks/RecordTypes/`, the FAQ element is in `ContentBlocks/ContentElements/faq/`. Tables, columns and forms are generated from these files.

- Name a field after the schema.org property it fills, for example `identifier` or `directApply`. Simple values are passed on without PHP. Only nested structures such as address, salary, location and offers are built in `Classes/Builder/TypeAssembler.php`.
- Labels follow the Content Blocks convention: `language/labels.xlf` and `language/de.labels.xlf` in each block. Write the English label into the YAML, run `typo3 content-blocks:language:generate <name>`, remove the label from the YAML and add the German target.
- Fields used by several types live in `ContentBlocks/Basics/` and take their labels from `Resources/Private/Language/locallang_db.xlf` via `LLL:`, so each translation exists once.
- A property that EXT:schema does not know yet (schema.org "pending") is registered like `Classes/Schema/JobPostingAdditionalProperties.php`.
- Run `typo3 content-blocks:lint` afterwards, on TYPO3 13 and 14. The CI does both. Content Blocks 1.6 is stricter in places, for example `size` must be 10 or more.

## Settings

| Setting | Default | Effect |
| --- | --- | --- |
| `hideWebPageTypeField` | on | Hides "Type of web page" of EXT:schema in the page properties. The column stays, existing values keep working. Flush caches after changing. |

## Recommended settings of EXT:schema

- Disable "Automatic embedding of the WebPage schema" (`automaticWebPageSchemaGeneration`). Without own data the WebPage node stays empty, schematic outputs the types that carry content.

## Requirements

- PHP 8.2+
- TYPO3 13.4 LTS or 14.3 LTS
- brotkrueml/schema 4.4+
- friendsoftypo3/content-blocks 1.6 (TYPO3 13) or 2.4 (TYPO3 14)

## Installation in a project

The repository is private. Add it as a VCS repository in the project's `composer.json`:

```json
"repositories": [
    { "type": "vcs", "url": "git@github.com:tobiwollender/schematic.git" }
]
```

```bash
composer require tobiwollender/schematic:@dev
```

Then add the site set `tobiwollender/schematic` to the site configuration and run `typo3 extension:setup`.

## Test instance

The repository contains a DDEV setup with TYPO3 14.3, Content Blocks and demo content.

```bash
ddev start
ddev schematic-setup
```

`ddev schematic-setup` installs a fresh instance and can be run again at any time to reset it. The backend user is `admin`, the password is in `.ddev/commands/web/schematic-setup`.

| Page | Shows |
| --- | --- |
| Startseite | LocalBusiness and WebSite from the organization record in "Stammdaten" |
| Karriere → Werkstudent Marketing | Content Blocks page type "Stellenanzeige" with locked type JobPosting |
| Blog → Strukturierte Daten in TYPO3 pflegen | Free choice: BlogPosting |
| Tag der offenen Tür | Free choice: Event |
| FAQ | FAQ content element, output as FAQPage |
| Über uns | No structured data of its own, to try the free choice |
| /en/… | English translations of home, careers, job and FAQ page |

To try TYPO3 13.4 instead:

```bash
ddev composer update -W --with typo3/cms-core:^13.4
ddev schematic-setup
```
