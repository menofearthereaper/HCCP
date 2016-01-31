CREATE TABLE companyDetails (
  id                INT PRIMARY KEY AUTOINCREMENT NOT NULL,
  company           TEXT                          NOT NULL,
  proposedCode      TEXT UNIQUE     NOT NULL,
  listingDate       TEXT                          NOT NULL,
  contact           TEXT                          NOT NULL,
  activities        TEXT                          NOT NULL,
  industryGroup     TEXT                          NOT NULL,
  issuePrice        TEXT                          NOT NULL,
  issueType         TEXT                          NOT NULL,
  securityCode      TEXT UNIQUE     NOT NULL,
  capitalToRaise    TEXT                          NOT NULL,
  expextedCloseDate TEXT                          NOT NULL,
  underwriter       TEXT            NOT NULL
);