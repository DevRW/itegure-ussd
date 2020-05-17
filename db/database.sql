CREATE TABLE menus (
  id int(11) NOT NULL,
  choice text NOT NULL,
  title_kinya text NOT NULL,
  title_en text NOT NULL,
  parent_id text NOT NULL
);

CREATE TABLE sessions (
  id int(11) NOT NULL,
  session text NOT NULL,
  phone text NOT NULL,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE stage (
  id int(11) NOT NULL,
  session text NOT NULL,
  stage text NOT NULL
);

CREATE TABLE user_exists (
  id int(11) NOT NULL,
  session text NOT NULL,
  exist text NOT NULL,
  token text NOT NULL,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE menus
  ADD PRIMARY KEY (id);

ALTER TABLE sessions
  ADD PRIMARY KEY (id);

ALTER TABLE stage
  ADD PRIMARY KEY (id);

ALTER TABLE user_exists
  ADD PRIMARY KEY (id);


ALTER TABLE menus
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE sessions
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE stage
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE user_exists
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
