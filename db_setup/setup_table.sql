drop table if exists Comments;
drop table if exists Comments_22;
drop table if exists Comments_251;
drop table if exists Comments_252;
drop table if exists TreePaths;
drop table if exists Comments_253;
drop table if exists Bugs;
drop table if exists Accounts;

create table Comments (
  comment_id   SERIAL PRIMARY KEY,
  parent_id    BIGINT UNSIGNED,
  comment      TEXT NOT NULL,
  FOREIGN KEY (parent_id) REFERENCES Comments(comment_id)
);

insert into Comments values(1,null ,'このバグの原因は何かな？'),
(2,1,'ヌルポインターのせいじゃないかな'),
(3,2,'そうじゃないよ。それは確認済みだ。'),
(4,1,'無効な入力を調べてみたら？'),
(5,4,'そうか、バグの原因はそれだな。'),
(6,4,'よし、じゃあチェック機能を追加してもらえるかな？'),
(7,6,'了解。修正したよ。');


create table Bugs(
  bug_id bigint unsigned not null auto_increment,
  primary key (bug_id)
);

create table Accounts(
  account_id bigint unsigned not null auto_increment,
  name varchar(10) not null,
  primary key (account_id)
);

-- 2.2
CREATE TABLE Comments_22 (
  comment_id   SERIAL PRIMARY KEY,
  parent_id    BIGINT UNSIGNED,
  bug_id bigint unsigned not null,
  author bigint unsigned not null,
  comment_date datetime not null, 
  comment      TEXT NOT NULL,
  FOREIGN KEY (parent_id) REFERENCES Comments_22(comment_id),
  foreign key (bug_id) references Bugs(bug_id),
  foreign key (author) references Accounts(account_id)
);

insert into Bugs values(1),(2),(3);
insert into Accounts values (1,'Fran'),(2,'Ollie'),(3,'Kukla'),(4,'hironomiu'),(5,'hoge'),(6,'fuga');

insert into Comments_22 values(null,null,1,1,now(),'このバグの原因は何かな？'),
(null,1,1,2,now(),'ヌルポインターのせいじゃないかな'),
(null,2,1,1,now(),'そうじゃないよ。それは確認済みだ。'),
(null,1,1,3,now(),'無効な入力を調べてみたら？'),
(null,4,1,2,now(),'そうか、バグの原因はそれだな。'),
(null,4,1,1,now(),'よし、じゃあチェック機能を追加してもらえるかな？'),
(null,6,1,3,now(),'了解。修正したよ。');


-- 2.5.1
CREATE TABLE Comments_251 (
  comment_id   SERIAL PRIMARY KEY,
  path varchar(1000),
  bug_id bigint unsigned not null,
  author bigint unsigned not null,
  comment_date datetime not null, 
  comment      TEXT NOT NULL,
  foreign key (bug_id) references Bugs(bug_id),
  foreign key (author) references Accounts(account_id)
);

insert into Comments_251 values(null,'1/',1,1,now(),'このバグの原因は何かな？'),
(null,'1/2/',1,2,now(),'ヌルポインターのせいじゃないかな'),
(null,'1/2/3/',1,1,now(),'そうじゃないよ。それは確認済みだ。'),
(null,'1/4/',1,3,now(),'無効な入力を調べてみたら？'),
(null,'1/4/5/',1,2,now(),'そうか、バグの原因はそれだな。'),
(null,'1/4/6/',1,1,now(),'よし、じゃあチェック機能を追加してもらえるかな？'),
(null,'1/4/6/7/',1,3,now(),'了解。修正したよ。');

-- 2.5.2
CREATE TABLE Comments_252 (
  comment_id   SERIAL PRIMARY KEY,
  nsleft int not null,
  nsright int not null,
  bug_id bigint unsigned not null,
  author bigint unsigned not null,
  comment_date datetime not null, 
  comment      TEXT NOT NULL,
  foreign key (bug_id) references Bugs(bug_id),
  foreign key (author) references Accounts(account_id)
);


insert into Comments_252 values(null,1,14,1,1,now(),'このバグの原因は何かな？'),
(null,2,5,1,2,now(),'ヌルポインターのせいじゃないかな'),
(null,3,4,1,1,now(),'そうじゃないよ。それは確認済みだ。'),
(null,6,13,1,3,now(),'無効な入力を調べてみたら？'),
(null,7,8,1,2,now(),'そうか、バグの原因はそれだな。'),
(null,9,12,1,1,now(),'よし、じゃあチェック機能を追加してもらえるかな？'),
(null,10,11,1,3,now(),'了解。修正したよ。');

-- 2.5.3
CREATE TABLE Comments_253 (
  comment_id   SERIAL PRIMARY KEY,
  bug_id bigint unsigned not null,
  author bigint unsigned not null,
  comment_date datetime not null, 
  comment      TEXT NOT NULL,
  foreign key (bug_id) references Bugs(bug_id),
  foreign key (author) references Accounts(account_id)
);
create table TreePaths (
  ancestor bigint unsigned not null,
  descendant bigint unsigned not null,
  primary key (ancestor,descendant),
  foreign key (ancestor) references Comments_253(comment_id),
  foreign key (descendant) references Comments_253(comment_id)
);

insert into Comments_253 values(null,1,1,now(),'このバグの原因は何かな？'),
(null,1,2,now(),'ヌルポインターのせいじゃないかな'),
(null,1,1,now(),'そうじゃないよ。それは確認済みだ。'),
(null,1,3,now(),'無効な入力を調べてみたら？'),
(null,1,2,now(),'そうか、バグの原因はそれだな。'),
(null,1,1,now(),'よし、じゃあチェック機能を追加してもらえるかな？'),
(null,1,3,now(),'了解。修正したよ。');

insert into TreePaths values (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(2,2),(2,3),(3,3),(4,4),(4,5),(4,6),(4,7),(5,5),(6,6),(6,7),(7,7);

