CREATE TABLE pages (
    tx_exdeliverwcag_conformance_level varchar(3) DEFAULT 'AA' NOT NULL,
    tx_exdeliverwcag_readability_score float DEFAULT '0' NOT NULL,
    tx_exdeliverwcag_problems text,
    tx_exdeliverwcag_improvements text
);