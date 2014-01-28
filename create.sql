create database chapter2;

drop table Comments;

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


drop table Comments_22;

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

select c1.*,c2.* from Comments_22 c1 left outer join Comments_22 c2 on c2.parent_id = c1.comment_id;

explain select c1.*,c2.* from Comments_22 c1 left outer join Comments_22 c2 on c2.parent_id = c1.comment_id\G

select c1.*,c2.*,c3.*,c4.* from Comments_22 c1 left outer join Comments_22 c2 on c2.parent_id = c1.comment_id left outer join Comments_22 c3 on c3.parent_id = c2.comment_id left outer join Comments_22 c4 on c4.parent_id = c3.comment_id;

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

select * from Comments_251 as c where '1/4/6/7' like concat(c.path , '%');
select * from Comments_251 as c where c.path like concat('1/4/', '%');

select c.author,count(*) from Comments_251 as c where c.path like '1/4/' || '%' group by c.author;

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

select c2.* from Comments_252 as c1 inner join Comments_252 as c2 on c2.nsleft between c1.nsleft and c1.nsright where c1.comment_id = 4;
select c2.* from Comments_252 as c1 inner join Comments_252 as c2 on c2.nsleft between c1.nsleft and c1.nsright where c1.comment_id = 6;

select c1.comment_id ,count(c2.comment_id) as depth from Comments_252 as c1 inner join Comments_252 as c2 on c1.nsleft between c2 .nsleft and c2.nsright where c1.comment_id =7 group by c1.comment_id;

delete from Comments_252 where comment_id = 6;

select c1.comment_id ,count(c2.comment_id) as depth from Comments_252 as c1 inner join Comments_252 as c2 on c1.nsleft between c2 .nsleft and c2.nsright where c1.comment_id =7 group by c1.comment_id;

select parent.* from Comments_252 as c inner join Comments_252 as parent on parent.nsleft < c.nsleft and c.nsleft < parent.nsright
left outer join Comments_252 as in_between on in_between.nsleft < c.nsleft and c.nsleft < in_between.nsright and parent.nsleft < in_between.nsleft and in_between.nsleft < parent.nsright
where c.comment_id = 6
and in_between.comment_id is null;

select * from Comments_252;
update Comments_252 set nsleft = case when nsleft > 7 then nsleft + 2 else nsleft end , nsright = nsright + 2 where nsright >= 7;
select * from Comments_252;

insert into Comments_252(nsleft,nsright,bug_id,author,comment_date,comment) values(8,9,1,3,now(),'私もそう思います');

-- 2.5.3

drop table Comments_253;
drop table TreePaths;

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

select c.* from Comments_253 as c inner join TreePaths as t on comment_id = t.descendant where t.ancestor = 4;
select c.* from Comments_253 as c inner join TreePaths as t on comment_id = t.ancestor where t.descendant = 6;

select * from Comments_253;
select * from TreePaths;
insert into Comments_253 (comment_id,bug_id,author,comment_date,comment) values (8,1,3,now(),'確認お願いします。');
insert into TreePaths(ancestor,descendant) select t.ancestor,8 from TreePaths as t where t.descendant = 5 union all select 8,8;
select * from Comments_253;
select * from TreePaths;

delete form TreePaths where descendant = 7;

delete from TreePaths where descendant in (select x.id from (select descendant as id from TreePaths where ancestor = 4 ) as x);

delete from TreePaths where descendant in (select x.id from (select descendant as id from TreePaths where ancestor = 6 ) as x) and ancestor in (select y.id from (select ancestor as id from TreePaths where descendant = 6 and ancestor != descendant ) as y);



