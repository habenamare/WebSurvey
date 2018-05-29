-- CREATE USER username IDENTIFIED BY 'user_password';

-- CREATE DATABASE db_name;
-- USE db_name;

-- GRANT ALL ON db_name.* TO username;

-------------------------------------------------------------------
CREATE TABLE Researcher (
   username        varchar(10)  NOT NULL PRIMARY KEY,
   hashed_password varchar(255) NOT NULL
);
INSERT INTO Researcher (username, hashed_password)
VALUES ('admin', 'appropriate-hashed-password');
-------------------------------------------------------------------
CREATE TABLE Survey (
   survey_id    int            NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name         varchar(500)   NOT NULL UNIQUE,
   date_created date           NOT NULL,
   expire_date  date           NOT NULL
);
INSERT INTO Survey (survey_id, name, date_created, expire_date)
VALUES (1, 'Employee Engagement Survey', '2017-12-29', '2018-01-29');
-------------------------------------------------------------------
CREATE TABLE Respondent (
   respondent_id int          NOT NULL AUTO_INCREMENT PRIMARY KEY,
   first_name    char(20)     NOT NULL,
   last_name     char(20)     NOT NULL,
   email         varchar(254) NOT NULL UNIQUE
);
INSERT INTO Respondent (respondent_id, first_name, last_name, email)
VALUES (1, 'Haben', 'Amare', 'haben.amare@outlook.com');
INSERT INTO Respondent (respondent_id, first_name, last_name, email)
VALUES (2, 'Firaol', 'Dida', 'firaol.dida@gmail.com');
INSERT INTO Respondent (respondent_id, first_name, last_name, email)
VALUES (3, 'Ephrem', 'Chane', 'ephrem.chane@gmail.com');
INSERT INTO Respondent (respondent_id, first_name, last_name, email)
VALUES (4, 'Eyosias', 'Wondwossen', 'eyosias.wondwossen@gmail.com');
INSERT INTO Respondent (respondent_id, first_name, last_name, email)
VALUES (5, 'Nathan', 'Abebe', 'nathan.abebe@gmail.com');
-------------------------------------------------------------------
CREATE TABLE SurveyRespondent (
   survey_id            int     NOT NULL,
   respondent_id        int     NOT NULL,
   submission_code      char(9) NOT NULL,
   submission_code_used int     NOT NULL DEFAULT 0,
   no_of_emails_sent    int     NOT NULL DEFAULT 0,

   PRIMARY KEY (survey_id, respondent_id)
);
ALTER TABLE SurveyRespondent
ADD CONSTRAINT fk_SurveyRespondent_Survey
FOREIGN KEY (survey_id) REFERENCES Survey (survey_id)
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SurveyRespondent
ADD CONSTRAINT fk_SurveyRespondent_Respondent
FOREIGN KEY (respondent_id) REFERENCES Respondent (respondent_id)
ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO SurveyRespondent (survey_id, respondent_id, submission_code)
VALUES (1, 1, '010101aab');
INSERT INTO SurveyRespondent (survey_id, respondent_id, submission_code)
VALUES (1, 2, '020202ccd');
INSERT INTO SurveyRespondent (survey_id, respondent_id, submission_code)
VALUES (1, 3, '030303eef');
INSERT INTO SurveyRespondent (survey_id, respondent_id, submission_code)
VALUES (1, 4, '040404ggh');
INSERT INTO SurveyRespondent (survey_id, respondent_id, submission_code)
VALUES (1, 5, '050505iij');
-------------------------------------------------------------------
CREATE TABLE Question (
   question_id     int NOT NULL AUTO_INCREMENT PRIMARY KEY,
   question_number int NOT NULL,
   question        varchar(60000) NOT NULL,
   question_type   char NOT NULL DEFAULT 's',

   survey_id       int            NOT NULL
);
-- for question_type, 's' -> single select & 'm' -> multiple select
ALTER TABLE Question
ADD CONSTRAINT fk_Question_Survey
FOREIGN KEY (survey_id) REFERENCES Survey (survey_id)
ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO Question (question_id, question_number, question, question_type, survey_id)
VALUES (1, 1, 'I am satisfied with my opportunities for professional growth.', 's', 1);
INSERT INTO Question (question_id, question_number, question, question_type, survey_id)
VALUES (2, 2, 'I am pleased with the career advancement opportunities available to me.', 's', 1);
INSERT INTO Question (question_id, question_number, question, question_type, survey_id)
VALUES (3, 3, 'My organization is dedicated to my professional development.', 's', 1);
INSERT INTO Question (question_id, question_number, question, question_type, survey_id)
VALUES (4, 4, 'I am satisfied with the job-related training my organization offers.', 's', 1);
INSERT INTO Question (question_id, question_number, question, question_type, survey_id)
VALUES (5, 5, 'I am inspired to meet my goals at work.', 's', 1);
INSERT INTO Question (question_id, question_number, question, question_type, survey_id)
VALUES (6, 6, 'I get excited about going to work.', 's', 1);
INSERT INTO Question (question_id, question_number, question, question_type, survey_id)
VALUES (7, 7, 'I am often so involved in my work that the day goes by very quickly.', 's', 1);
-------------------------------------------------------------------
CREATE TABLE Choice (
   choice_id           int            NOT NULL AUTO_INCREMENT PRIMARY KEY,
   choice              varchar(60000) NOT NULL,
   no_of_times_chosen  int            NOT NULL DEFAULT 0,

   question_id         int            NOT NULL
);

ALTER TABLE Choice
ADD CONSTRAINT fk_Choice_Question
FOREIGN KEY (question_id) REFERENCES Question (question_id)
ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO Choice (choice_id, choice, question_id)
VALUES (1, 'Strongly Agree', 1);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (2, 'Agree', 1);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (3, 'Neutral', 1);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (4, 'Disagree', 1);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (5, 'Strongly Disagree', 1);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (6, 'Strongly Agree', 2);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (7, 'Agree', 2);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (8, 'Neutral', 2);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (9, 'Disagree', 2);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (10, 'Strongly Disagree', 2);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (11, 'Strongly Agree', 3);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (12, 'Agree', 3);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (13, 'Neutral', 3);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (14, 'Disagree', 3);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (15, 'Strongly Disagree', 3);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (16, 'Strongly Agree', 4);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (17, 'Agree', 4);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (18, 'Neutral', 4);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (19, 'Disagree', 4);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (20, 'Strongly Disagree', 4);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (21, 'Strongly Agree', 5);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (22, 'Agree', 5);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (23, 'Neutral', 5);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (24, 'Disagree', 5);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (25, 'Strongly Disagree', 5);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (26, 'Strongly Agree', 6);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (27, 'Agree', 6);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (28, 'Neutral', 6);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (29, 'Disagree', 6);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (30, 'Strongly Disagree', 6);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (31, 'Strongly Agree', 7);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (32, 'Agree', 7);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (33, 'Neutral', 7);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (34, 'Disagree', 7);
INSERT INTO Choice (choice_id, choice, question_id)
VALUES (35, 'Strongly Disagree', 7);
-------------------------------------------------------------------