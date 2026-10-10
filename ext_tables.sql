CREATE TABLE tx_schematic_entity (
    parentid int(11) unsigned DEFAULT '0' NOT NULL,
    type varchar(64) DEFAULT '' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,
    description text,
    url varchar(1024) DEFAULT '' NOT NULL,
    image int(11) unsigned DEFAULT '0' NOT NULL,
    logo int(11) unsigned DEFAULT '0' NOT NULL,
    telephone varchar(64) DEFAULT '' NOT NULL,
    email varchar(255) DEFAULT '' NOT NULL,
    same_as text,
    opening_hours text,
    street varchar(255) DEFAULT '' NOT NULL,
    postal_code varchar(32) DEFAULT '' NOT NULL,
    locality varchar(255) DEFAULT '' NOT NULL,
    region varchar(255) DEFAULT '' NOT NULL,
    country varchar(64) DEFAULT '' NOT NULL,
    author_name varchar(255) DEFAULT '' NOT NULL,
    date_published int(11) DEFAULT '0' NOT NULL,
    date_posted int(11) DEFAULT '0' NOT NULL,
    valid_through int(11) DEFAULT '0' NOT NULL,
    employment_type varchar(255) DEFAULT '' NOT NULL,
    remote tinyint(1) unsigned DEFAULT '0' NOT NULL,
    salary_min double(11,2) DEFAULT '0.00' NOT NULL,
    salary_max double(11,2) DEFAULT '0.00' NOT NULL,
    salary_currency varchar(3) DEFAULT 'EUR' NOT NULL,
    salary_unit varchar(16) DEFAULT 'YEAR' NOT NULL,
    start_date int(11) DEFAULT '0' NOT NULL,
    end_date int(11) DEFAULT '0' NOT NULL,
    event_status varchar(64) DEFAULT '' NOT NULL,
    attendance_mode varchar(64) DEFAULT '' NOT NULL,
    location_name varchar(255) DEFAULT '' NOT NULL,
    online_url varchar(1024) DEFAULT '' NOT NULL,
    offer_price double(11,2) DEFAULT '0.00' NOT NULL,
    offer_currency varchar(3) DEFAULT 'EUR' NOT NULL,
    offer_url varchar(1024) DEFAULT '' NOT NULL
);

CREATE TABLE tx_schematic_faq_item (
    parentid int(11) unsigned DEFAULT '0' NOT NULL,
    question varchar(512) DEFAULT '' NOT NULL,
    answer text
);

CREATE TABLE pages (
    tx_schematic_entity int(11) unsigned DEFAULT '0' NOT NULL,
    tx_schematic_organization int(11) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE tt_content (
    tx_schematic_faq_items int(11) unsigned DEFAULT '0' NOT NULL
);
