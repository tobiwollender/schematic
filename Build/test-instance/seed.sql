-- Demo content for the schematic test instance. Imported by "ddev schematic-setup" after "typo3 setup".

INSERT INTO pages (uid, pid, sorting, title, slug, doktype, is_siteroot, description, tx_schematic_organization, tx_schematic_entity, demo_job_teaser, demo_job_employmentType, demo_job_validUntil, perms_userid, perms_user, perms_group, perms_everybody, crdate, tstamp) VALUES
(1, 0, 256, 'Startseite', '/', 1, 1, 'Muster GmbH aus Regensburg: Beispielunternehmen für die schematic-Testinstanz.', 1, 0, NULL, NULL, 0, 1, 31, 27, 0, UNIX_TIMESTAMP('2026-09-01'), UNIX_TIMESTAMP()),
(2, 1, 256, 'Karriere', '/karriere', 1, 0, 'Offene Stellen bei der Muster GmbH.', 0, 0, NULL, NULL, 0, 1, 31, 27, 0, UNIX_TIMESTAMP('2026-09-01'), UNIX_TIMESTAMP()),
(3, 2, 256, 'Werkstudent Marketing (m/w/d)', '/karriere/werkstudent-marketing', 1760100001, 0, '', 0, 1, 'Du unterstützt unser Marketing-Team bei Social Media, Newsletter und Website. 15 bis 20 Stunden pro Woche, flexibel neben dem Studium.', 'PART_TIME', UNIX_TIMESTAMP('2026-12-31'), 1, 31, 27, 0, UNIX_TIMESTAMP('2026-10-01'), UNIX_TIMESTAMP()),
(4, 1, 512, 'Blog', '/blog', 1, 0, '', 0, 0, NULL, NULL, 0, 1, 31, 27, 0, UNIX_TIMESTAMP('2026-09-01'), UNIX_TIMESTAMP()),
(5, 4, 256, 'Strukturierte Daten in TYPO3 pflegen', '/blog/strukturierte-daten', 1, 0, 'Wie Redakteure schema.org-Daten ohne JSON-Kenntnisse im Backend pflegen.', 0, 1, NULL, NULL, 0, 1, 31, 27, 0, UNIX_TIMESTAMP('2026-09-15'), UNIX_TIMESTAMP()),
(6, 1, 768, 'Tag der offenen Tür', '/tag-der-offenen-tuer', 1, 0, 'Lernen Sie die Muster GmbH kennen: Führungen, Vorträge und Gespräche mit dem Team.', 0, 1, NULL, NULL, 0, 1, 31, 27, 0, UNIX_TIMESTAMP('2026-10-01'), UNIX_TIMESTAMP()),
(7, 1, 1024, 'FAQ', '/faq', 1, 0, 'Antworten auf häufige Fragen zur Muster GmbH.', 0, 0, NULL, NULL, 0, 1, 31, 27, 0, UNIX_TIMESTAMP('2026-09-01'), UNIX_TIMESTAMP()),
(8, 1, 1280, 'Über uns', '/ueber-uns', 1, 0, 'Seite ohne eigene strukturierte Daten.', 0, 0, NULL, NULL, 0, 1, 31, 27, 0, UNIX_TIMESTAMP('2026-09-01'), UNIX_TIMESTAMP()),
(9, 1, 1536, 'Stammdaten', '/stammdaten', 254, 0, '', 0, 0, NULL, NULL, 0, 1, 31, 27, 0, UNIX_TIMESTAMP('2026-09-01'), UNIX_TIMESTAMP());

INSERT INTO tx_schematic_entity (uid, pid, parentid, type, title, description, telephone, email, streetAddress, postalCode, addressLocality, addressRegion, addressCountry, sameAs, openingHours, logo, crdate, tstamp) VALUES
(1, 9, 0, 'LocalBusiness', 'Muster GmbH', 'Beispielunternehmen aus Regensburg für Beratung und Service.', '+49 941 1234560', 'info@example.com', 'Musterstraße 1', '93047', 'Regensburg', 'Bayern', 'DE', 'https://www.linkedin.com/company/example\nhttps://www.instagram.com/example', 'Mo-Fr 08:00-17:00', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO tx_schematic_entity (uid, pid, parentid, type, streetAddress, postalCode, addressLocality, addressRegion, addressCountry, datePosted, identifier, directApply, salaryMin, salaryMax, salaryCurrency, salaryUnit, crdate, tstamp) VALUES
(2, 3, 3, 'JobPosting', 'Musterstraße 1', '93047', 'Regensburg', 'Bayern', 'DE', UNIX_TIMESTAMP('2026-10-01'), 'MG-2026-014', 1, 15, 17, 'EUR', 'HOUR', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO tx_schematic_entity (uid, pid, parentid, type, author, datePublished, crdate, tstamp) VALUES
(3, 5, 5, 'BlogPosting', 'Alex Beispiel', UNIX_TIMESTAMP('2026-09-15'), UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO tx_schematic_entity (uid, pid, parentid, type, streetAddress, postalCode, addressLocality, addressRegion, addressCountry, startDate, endDate, eventStatus, eventAttendanceMode, locationName, offerCurrency, performer, crdate, tstamp) VALUES
(4, 6, 6, 'Event', 'Musterstraße 1', '93047', 'Regensburg', 'Bayern', 'DE', UNIX_TIMESTAMP('2026-11-20 10:00:00'), UNIX_TIMESTAMP('2026-11-20 16:00:00'), 'EventScheduled', 'OfflineEventAttendanceMode', 'Muster GmbH, Hauptsitz', 'EUR', 'Sam Muster', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO sys_file (uid, pid, storage, type, identifier, identifier_hash, folder_hash, extension, mime_type, name, sha1, size, creation_date, modification_date, tstamp) VALUES
(1, 0, 1, 2, '/stammdaten/logo.png', 'a721544a5797a4f4bd2b993f44303863edfa3188', '8fcb5598893c33cc51de8f04d33fbcc681445b54', 'png', 'image/png', 'logo.png', '04ec95081ed219a1fd444918fbd875bcc6fa1cb2', 13146, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO sys_file_metadata (uid, pid, file, width, height, tstamp, crdate) VALUES
(1, 0, 1, 400, 400, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO sys_file_reference (uid, pid, uid_local, uid_foreign, tablenames, fieldname, sorting_foreign, tstamp, crdate) VALUES
(1, 9, 1, 1, 'tx_schematic_entity', 'logo', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO tt_content (uid, pid, sorting, colPos, CType, header, bodytext, tx_schematic_faq_items, crdate, tstamp) VALUES
(1, 7, 256, 0, 'schematic_faq', 'Häufige Fragen', NULL, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 1, 256, 0, 'text', 'Willkommen bei der Muster GmbH', '<p>Auf der Startseite stehen Organization und WebSite als strukturierte Daten. Gepflegt wird die Organisation einmal als Datensatz im Ordner „Stammdaten“.</p>', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, 3, 256, 0, 'text', 'Deine Aufgaben', '<p>Diese Seite hat den Seitentyp „Stellenanzeige“ aus Content Blocks. Der schema.org-Typ JobPosting ist fest zugeordnet, Titel, Kurzbeschreibung, Beschäftigungsart und Bewerbungsschluss kommen direkt aus den Seitenfeldern.</p>', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, 5, 256, 0, 'text', '', '<p>Normale Seite mit frei gewähltem Typ BlogPosting in den Seiteneigenschaften, Reiter „Strukturierte Daten“.</p>', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, 6, 256, 0, 'text', '', '<p>Normale Seite mit frei gewähltem Typ Event.</p>', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO tx_schematic_faq_item (uid, pid, parentid, sorting, question, answer, crdate, tstamp) VALUES
(1, 7, 1, 256, 'Wo finde ich die Muster GmbH?', '<p>In der Musterstraße 1 in 93047 Regensburg, fünf Minuten vom Hauptbahnhof.</p>', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 7, 1, 512, 'Wann sind Sie erreichbar?', '<p>Montag bis Freitag von 8 bis 17 Uhr unter +49 941 1234560.</p>', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, 7, 1, 768, 'Bieten Sie Praktika an?', '<p>Ja, offene Praktika und Werkstudentenstellen stehen auf der Seite <strong>Karriere</strong>.</p>', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

UPDATE be_users SET lang = 'de' WHERE username = 'admin';
