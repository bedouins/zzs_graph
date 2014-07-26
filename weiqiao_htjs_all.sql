



create table tmp_nsrsbh
as
select nsrsbh 
        from 
            (select  GF_NSRSBH as nsrsbh from CB_FPCGL_MX 
              group by  GF_NSRSBH
            union 
            select  XF_NSRSBH as nsrsbh from CB_FPCGL_MX 
              group by  XF_NSRSBH
            union 
              select  XF_NSRSBH as nsrsbh from rz_fpdkl_mx 
              group by  XF_NSRSBH
            union
            select  GF_NSRSBH as nsrsbh from rz_fpdkl_mx 
              group by  GF_NSRSBH)
        group by nsrsbh    ;

create table tmp_nsrmc
as
select a.nsrsbh, a.nsrmc
from         
    (select a.NSRSBH, b.nsrmc from tmp_nsrsbh a, view_dj_nsrxx b
    where a.NSRSBH = b.nsrsbh(+)) a
  where  a.nsrmc is not null
union
select a.nsrsbh, b.nsr_mc as nsrmc
from         
    (select a.NSRSBH, b.nsrmc from tmp_nsrsbh a, view_dj_nsrxx b
    where a.NSRSBH = b.nsrsbh(+)) a,
    ws_nsrxx b
  where a.nsrsbh=b.NSRSBH(+)
    and a.nsrmc is null;
 
 create index idx_tmp_nsrmc on tmp_nsrmc(nsrsbh);
 
 create table tmp_fpcgl
 as
 select xf_nsrsbh, gf_nsrsbh, to_char(kprq,'yyyy') as year,sum(je) as je,sum(se) as se 
 from CB_FPCGL_MX
 group by xf_nsrsbh, gf_nsrsbh, to_char(kprq,'yyyy');
 
 create index idx_tmp_fpcgl_xf on tmp_fpcgl(xf_nsrsbh);
 
 create index idx_tmp_fpcgl_gf on tmp_fpcgl(gf_nsrsbh);
 
  create table tmp_fpdkl
 as
 select xf_nsrsbh, gf_nsrsbh, to_char(rz_sj,'yyyy') as year,sum(je) as je,sum(se) as se 
 from rz_fpdkl_mx
 group by xf_nsrsbh, gf_nsrsbh, to_char(rz_sj,'yyyy');
 
 create index idx_tmp_fpdkl_xf on tmp_fpdkl(xf_nsrsbh);
 
 create index idx_tmp_fpdkl_gf on tmp_fpdkl(gf_nsrsbh);
 
 select c.nsrmc, b.nsrmc, a.* from tmp_fpcgl a, tmp_nsrmc b, tmp_nsrmc c
 where a.gf_nsrsbh = b.nsrsbh and a.xf_nsrsbh=c.nsrsbh
 and a.XF_NSRSBH in ('372330613858296','37233074568737X','37230267552104X','372321587175220','372323075788656','372324077954363','372325076950029',
              '372330576631585','372302586055278','372324581910837','372325593646850','372323062983057','372330167206000','372330745687361',
              '372330673188503','37230268484748X','37233079865802X','372321554396545','372330052363647','372330565218955','372330798658038');
 
create table tmp_input_list(nsrsbh varchar(30));
insert into tmp_input_list values('372330613858296','37233074568737X','37230267552104X','372321587175220','372323075788656','372324077954363','372325076950029',
              '372330576631585','372302586055278','372324581910837','372325593646850','372323062983057','372330167206000','372330745687361',
              '372330673188503','37230268484748X','37233079865802X','372321554396545','372330052363647','372330565218955','372330798658038');
 
drop table tmp_nodes;
create table tmp_nodes
as
select  rownum as nodeid, XF_NSRSBH,  grp,  nsrmc,  je
from
(SELECT  
  a.XF_NSRSBH,
  a.grp,
  b.NSRMC,
 sum(c.je) as je
FROM
  (SELECT XF_NSRSBH,
    1 AS grp 
  FROM tmp_fpcgl
  WHERE XF_NSRSBH IN ('372330613858296','37233074568737X','37230267552104X','372321587175220','372323075788656','372324077954363','372325076950029',
              '372330576631585','372302586055278','372324581910837','372325593646850','372323062983057','372330167206000','372330745687361',
              '372330673188503','37230268484748X','37233079865802X','372321554396545','372330052363647','372330565218955','372330798658038')
  GROUP BY XF_NSRSBH,
    1
  UNION
  SELECT GF_NSRSBH,
    2 AS grp 
  FROM tmp_fpcgl
  WHERE XF_NSRSBH   IN ('372330613858296','37233074568737X','37230267552104X','372321587175220','372323075788656','372324077954363','372325076950029',
              '372330576631585','372302586055278','372324581910837','372325593646850','372323062983057','372330167206000','372330745687361',
              '372330673188503','37230268484748X','37233079865802X','372321554396545','372330052363647','372330565218955','372330798658038')
  AND GF_NSRSBH NOT IN ('372330613858296','37233074568737X','37230267552104X','372321587175220','372323075788656','372324077954363','372325076950029',
              '372330576631585','372302586055278','372324581910837','372325593646850','372323062983057','372330167206000','372330745687361',
              '372330673188503','37230268484748X','37233079865802X','372321554396545','372330052363647','372330565218955','372330798658038')
  GROUP BY GF_NSRSBH,
    2
  ) a,
  tmp_nsrmc b,
  tmp_fpcgl c
WHERE a.xf_nsrsbh = b.nsrsbh(+) 
	and a.xf_nsrsbh = c.xf_nsrsbh
group by  a.XF_NSRSBH, a.grp,  b.NSRMC	
	having  sum(c.je)>100000000) ;
  
select rownum, GF_NSRSBH from tmp_fpcgl;
--construct links;
select xfid,xf_grp, xfje, gfje, gfid,gf_grp,je,
CASE WHEN xf_grp=gf_GRP THEN xf_GRP ELSE case when xf_grp>gf_grp then xf_grp+1 else gf_grp+2 end END AS COLOR,
round(sqrt(greatest(sqrt(xfje*gfje)/(je+je2),1))) as distance
from tmp_links;
--CASE WHEN xf_grp=gf_GRP THEN xf_GRP ELSE case when xf_grp>gf_grp then xf_grp+1 else gf_grp+2 end END AS COLOR,
--round(sqrt(greatest(sqrt(xfje*gfje)/je,1))) as distance
drop  table tmp_links;
commit;
create table tmp_links
as 
select xfid,xf_grp,gfid,gf_grp,je
from
(SELECT b.nodeid -1 as xfid ,b.je as xfje, b.grp as xf_grp, c.nodeid -1 as gfid ,c.je as gfje, c.grp as gf_grp, sum(a.je) as je
FROM tmp_fpcgl a,
  tmp_nodes b,
  tmp_nodes c
WHERE a.XF_NSRSBH = b.XF_NSRSBH
AND a.GF_NSRSBH   = c.XF_NSRSBH 
group by b.nodeid    ,b.je, b.grp , c.nodeid   ,c.je,c.grp  ) 
where je >1000000;

select max(round(sqrt(greatest(sqrt(xfje*gfje)/je,1))))  
from
(SELECT b.nodeid -1 as xfid ,b.je as xfje, b.grp as xf_grp, c.nodeid -1 as gfid ,c.je as gfje, c.grp as gf_grp, sum(a.je) as je
FROM tmp_fpcgl a,
  tmp_nodes b,
  tmp_nodes c
WHERE a.XF_NSRSBH = b.XF_NSRSBH
AND a.GF_NSRSBH   = c.XF_NSRSBH 
group by b.nodeid    ,b.je, b.grp , c.nodeid   ,c.je,c.grp  ) ;

select * from tmp_fpcgl;
select count(*) from tmp_nodes group by XF_NSRSBH;

drop  table tmp_cross_links;
commit;
create table tmp_cross_links
as
select a.*,b.je as je2 
from
(select a.* from tmp_links a, tmp_links b
where a.xfid = b.gfid and a.gfid = b.xfid 
and a.xfid > a.gfid) a,
(select a.* from tmp_links a, tmp_links b
where a.xfid = b.gfid and a.gfid = b.xfid 
and a.xfid < a.gfid) b
where (a.xfid = b.gfid and a.gfid = b.xfid);


select a.*, 0 as je2  
  from tmp_links a
  where (xfid, gfid) not in ( 
        select xfid,gfid from tmp_cross_links
        union
        select xfid,gfid from tmp_cross_links);


select a.xfid, a.xf_grp, a.gfid, a.gf_grp, a.color, a.distance, a.je, 0 as je2 ,0  as distance2
from tmp_links a, tmp_links b
where a.xfid = b.gfid and a.gfid = b.xfid 
and a.xfid > a.gfid
union
select a.xfid, a.xf_grp, a.gfid, a.gf_grp, a.color, a.distance, 0 as je , a.je as je2 
from tmp_links a, tmp_links b
where a.xfid = b.gfid and a.gfid = b.xfid 
and a.xfid < a.gfid;


select XF_NSRSBH, b.nsrmc as xf_nsrmc,  GF_NSRSBH, c.NSRMC as gf_nsrmc,  kprq,  je,    se 
  from     
    (select XF_NSRSBH, GF_NSRSBH, to_char(kprq,'yyyy') as kprq, sum(je) as je, sum(se) as se from CB_FPCGL_MX
    where XF_NSRSBH in
    ('372330613858296','37233074568737X','37230267552104X','372321587175220','372323075788656','372324077954363','372325076950029',
    '372330576631585','372302586055278','372324581910837','372325593646850','372323062983057','372330167206000','372330745687361',
    '372330673188503','37230268484748X','37233079865802X','372321554396545','372330052363647','372330565218955','372330798658038')
    group by XF_NSRSBH, GF_NSRSBH, to_char(kprq,'yyyy')) a, tmp_wqnsr b, tmp_wqnsr c
  where a.XF_NSRSBH = b.nsrsbh
    and a.GF_NSRSBH = c.NSRSBH;
    
select XF_NSRSBH, b.nsrmc as xf_nsrmc,  GF_NSRSBH, c.NSRMC as gf_nsrmc,  rz_sj,  je,    se 
  from     
    (select XF_NSRSBH, GF_NSRSBH, to_char(rz_sj,'yyyy') as rz_sj, sum(je) as je, sum(se) as se from rz_fpdkl_mx
    where GF_NSRSBH in
    ('372330613858296','37233074568737X','37230267552104X','372321587175220','372323075788656','372324077954363','372325076950029',
    '372330576631585','372302586055278','372324581910837','372325593646850','372323062983057','372330167206000','372330745687361',
    '372330673188503','37230268484748X','37233079865802X','372321554396545','372330052363647','372330565218955','372330798658038')
    group by XF_NSRSBH, GF_NSRSBH, to_char(rz_sj,'yyyy')) a, tmp_wqnsr b, tmp_wqnsr c
  where a.XF_NSRSBH = b.nsrsbh
    and a.GF_NSRSBH = c.NSRSBH;    
    
select * from rz_fpdkl_mx;    

exec PROC_TMP_FPCGL_LINKS;

select xfid,xf_grp, xfje, gfje, gfid,gf_grp,je
from
(SELECT b.nodeid -1 as xfid ,b.je as xfje, b.grp as xf_grp, c.nodeid -1 as gfid ,c.je as gfje, c.grp as gf_grp, sum(a.je) as je
FROM tmp_fpcgl a,  
tmp_nodes b,  
tmp_nodes c
WHERE a.XF_NSRSBH = b.XF_NSRSBH
AND a.GF_NSRSBH   = c.XF_NSRSBH 
group by b.nodeid    ,b.je, b.grp , c.nodeid   ,c.je,c.grp  ) 
where je >1000000;


CREATE OR REPLACE PROCEDURE PROC_TMP_FPCGL_LINKS
Authid Current_User  
AS 
  v_int integer;
BEGIN


  select count(*) into v_int  from user_tables where lower(table_name)='tmp_links_pre';
  if (v_int >0) then
    EXECUTE immediate 'drop  table tmp_links_pre';
  end if;
  
  EXECUTE immediate ' 
create table tmp_links_pre
as 
select xfid,xf_grp, xfje, gfje, gfid,gf_grp,je
from
(SELECT b.nodeid -1 as xfid ,b.je as xfje, b.grp as xf_grp, c.nodeid -1 as gfid ,c.je as gfje, c.grp as gf_grp, sum(a.je) as je
FROM tmp_fpcgl a,  
tmp_nodes b,  
tmp_nodes c
WHERE a.XF_NSRSBH = b.XF_NSRSBH
AND a.GF_NSRSBH   = c.XF_NSRSBH 
group by b.nodeid    ,b.je, b.grp , c.nodeid   ,c.je,c.grp  ) 
where je >1000000';

  select count(*) into v_int  from user_tables where lower(table_name)='tmp_cross_links';
  if (v_int >0) then
  EXECUTE immediate 'drop  table tmp_cross_links';
  end if;
  EXECUTE immediate 'create table tmp_cross_links
as
select a.*,b.je as je2 
from
(select a.* from tmp_links_pre a, tmp_links_pre b
where a.xfid = b.gfid and a.gfid = b.xfid 
and a.xfid > a.gfid) a,
(select a.* from tmp_links_pre a, tmp_links_pre b
where a.xfid = b.gfid and a.gfid = b.xfid 
and a.xfid < a.gfid) b
where (a.xfid = b.gfid and a.gfid = b.xfid)';

  select count(*) into v_int  from user_tables where lower(table_name)='tmp_links';
  if (v_int >0) then
  EXECUTE immediate 'drop  table tmp_links';
  end if;
  EXECUTE immediate 'create table tmp_links
as
select a.*, 0 as je2  
  from tmp_links_pre a
  where (xfid, gfid) not in ( 
        select xfid,gfid from tmp_cross_links
        union
        select xfid,gfid from tmp_cross_links)
      union
      select * from tmp_cross_links';
        
END PROC_TMP_FPCGL_LINKS;