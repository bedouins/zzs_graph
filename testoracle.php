<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
<?php
// Create connection to Oracle
$conn = oci_connect("htjs", "htjs", "//192.168.0.1/weiqiao2",'utf8');
if (!$conn) {
   $m = oci_error();
   echo $m['message'], "\n";
   exit;
}
else {
   print "Connected to Oracle!";
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
print "<table border='1'>\n";
while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
    print "<tr>\n";
    foreach ($row as $item) {
        print "    <td>" . ($item !== null ? htmlspecialchars($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
    }
	print "    <td>" .   htmlspecialchars(oci_result($stid,4))   . "</td>\n";
    print "</tr>\n";
}
print "</table>\n"; 
oci_free_statement($stid);

// Close the Oracle connection
oci_close($conn);
?>