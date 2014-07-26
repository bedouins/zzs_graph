<?php
// Create connection to Oracle

$link_th = $_REQUEST['link_th'];
$node_th = $_REQUEST['node_th'];
$nsrlist="'372330613858296','37233074568737X','37230267552104X','372321587175220','372323075788656','372324077954363','372325076950029',
              '372330576631585','372302586055278','372324581910837','372325593646850','372323062983057','372330167206000','372330745687361',
              '372330673188503','37230268484748X','37233079865802X','372321554396545','372330052363647','372330565218955','372330798658038'";
if(isset($_REQUEST['nsrlist'] )){
	$nsrlist = $_REQUEST['nsrlist'];
}
$alpha_g = 0.0000000000667;
$alpha_hooke= 0.000002;
//$conn = oci_connect("htjs", "htjs", "//192.168.0.1/weiqiao2",'utf8');
$conn = oci_connect("htjs", "htjs", "//127.0.0.1/orcl",'utf8');
if (!$conn) {
   $m = oci_error();
   echo $m['message'], "\n";
   exit;
}
//create nodes

$sql = "drop table tmp_nodes";
$stmt = oci_parse($conn,$sql);
// Note OCI_DEFAULT - begin a transaction
if ( !oci_execute($stmt,OCI_DEFAULT) ) {
oci_rollback($conn); 
}

$sql = "create table tmp_nodes
as
select  rownum as nodeid, XF_NSRSBH,  grp,  nsrmc,  je, round(sqrt(je)/3000) as r
from
(SELECT  a.XF_NSRSBH, a.grp,  b.NSRMC, sum(c.je) as je
FROM
  (SELECT XF_NSRSBH,  1 AS grp 
  FROM tmp_fpcgl
  WHERE XF_NSRSBH IN (".$nsrlist.")
  GROUP BY XF_NSRSBH,  1
  UNION
  SELECT GF_NSRSBH,   2 AS grp 
  FROM tmp_fpcgl
  WHERE XF_NSRSBH   IN (".$nsrlist.")
    AND GF_NSRSBH NOT IN (".$nsrlist.")
    GROUP BY GF_NSRSBH,   2   ) a,
  tmp_nsrmc b,
  tmp_fpcgl c
WHERE a.xf_nsrsbh = b.nsrsbh(+) 
	and a.xf_nsrsbh = c.xf_nsrsbh
group by a.XF_NSRSBH, a.grp,  b.NSRMC	
	having  sum(c.je)>  ".$node_th.')';
$stmt = oci_parse($conn,$sql);

// Note OCI_DEFAULT - begin a transaction
if ( !oci_execute($stmt,OCI_DEFAULT) ) {
oci_rollback($conn);
exit(1);
}
// Prepare the statement
$stid = oci_parse($conn, 'SELECT * FROM tmp_nodes');
if (!$stid) {
    $e = oci_error($conn);
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

// Perform the logic of the query
$r = oci_execute($stid);
if (!$r) {
    $e = oci_error($stid);
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

// Fetch the results of the query
print '{"nodes": [';
$i=0;
while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
    if($i>0){
    	print ",";
	}	
    print "\n";

        print '{"name":"'.htmlspecialchars(oci_result($stid,4))
		.'","id":'.htmlspecialchars(oci_result($stid,1))
        .',"nsrsbh":"'.htmlspecialchars(oci_result($stid,2))
        .'","value":'.htmlspecialchars(oci_result($stid,5))
        .',"r":'.htmlspecialchars(oci_result($stid,6))
        .',"group":'.htmlspecialchars(oci_result($stid,3)).'}';
	$i=$i+1;
}

// links
$sql = "drop table tmp_links";
$stmt = oci_parse($conn,$sql);
// Note OCI_DEFAULT - begin a transaction
if ( !oci_execute($stmt,OCI_DEFAULT) ) {
oci_rollback($conn); 
}

$stmt = oci_parse($conn,
'BEGIN PROC_TMP_FPCGL_LINKS; END;');

// Note OCI_DEFAULT - begin a transaction
if ( !oci_execute($stmt,OCI_DEFAULT) ) {
oci_rollback($conn);
exit(1);
}

$stid = oci_parse($conn, 
'select xfid,xf_grp, xfje, gfje, gfid,gf_grp,je,
CASE WHEN xf_grp=gf_GRP 
	THEN case when xfid> gfid then 1
		else 2 end
	ELSE case when xfid> gfid then 3
		else 4 end
	END AS COLOR,
round(greatest(power(('.$alpha_g.'*xfje*gfje)/(je+je2)/'.$alpha_hooke.',1/3),1)) as distance
from tmp_links');
if (!$stid) {
    $e = oci_error($conn);
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

// Perform the logic of the query
$r = oci_execute($stid);
if (!$r) {
    $e = oci_error($stid);
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

// Fetch the results of the query
print '], "links": [';
$i=0;
while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
    if($i>0){
    	print ",";
	}	
    print "\n";

        print '{"source":'.htmlspecialchars(oci_result($stid,1)).
        ' ,"target":'.htmlspecialchars(oci_result($stid,5)).
         ',"value":'.htmlspecialchars(oci_result($stid,7)).
         ',"color":'.htmlspecialchars(oci_result($stid,8)).
         ',"distance":'.htmlspecialchars(oci_result($stid,9)).'}';
	$i=$i+1;
}
print "] }";

oci_free_statement($stid);

// Close the Oracle connection
oci_close($conn);
?>