-- 2.2
select c1.*,c2.* from Comments_22 c1 left outer join Comments_22 c2 on c2.parent_id = c1.comment_id;

explain select c1.*,c2.* from Comments_22 c1 left outer join Comments_22 c2 on c2.parent_id = c1.comment_id\G

select c1.*,c2.*,c3.*,c4.* from Comments_22 c1 left outer join Comments_22 c2 on c2.parent_id = c1.comment_id left outer join Comments_22 c3 on c3.parent_id = c2.comment_id left outer join Comments_22 c4 on c4.parent_id = c3.comment_id;


-- 2.5.1
select * from Comments_251 as c where '1/4/6/7' like concat(c.path , '%');
select * from Comments_251 as c where c.path like concat('1/4/', '%');
select c.author,count(*) from Comments_251 as c where c.path like '1/4/' || '%' group by c.author;

-- 2.5.2
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



