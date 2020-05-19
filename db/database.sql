-- DB Structure
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

-- Core Data
INSERT INTO menus (id, choice, title_kinya, title_en, parent_id) VALUES
(1, '0', 'Ikaze kuri Learning Reminder', 'Welcome to Learning Reminder', 'WELCOME'),
(2, '1', 'Andikisha umwana', 'Register Child', '0'),
(3, '2', 'Abanyanditseho', 'My children', '0'),
(4, '0', 'Andika ishuri yigamo (Ex. P3 niba ari Primary 3 cyangwa S3 niba ari Secondary 3)', 'Please enter your child\'s school year (Ex. P3 if Primary 3 or S3 if Secondary 3)', '-'),
(5, '0', 'Student\'s name', 'Amazina y\'umunyeshuri', '-'),
(6, '0', 'School name', 'Izina ry\'ishuri', '0'),
(7, '0', 'Umunyeshuri yanditswe, muzajya mubona ubutumwa bugufi igihe afite amasomo', 'Student registered, you will receive SMS for learning reminding', '0'),
(8, '0', 'Andika neza ishuri', 'Please write the class name correctly', '0'),
(9, '0', 'at', 'kuri', '0'),
(10, '0', 'in', 'muri', '0'),
(11, '0', 'Kwiyandikisha byakozwe, Ubu wakwandika umunyeshuri.', 'Successfully registered, You can now add student.', '0'),
(12, '0', '', '', '0');