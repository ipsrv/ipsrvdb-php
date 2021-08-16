<?php

$db = new ipsrv\ipsrvdb\IPSrvDB("/path/to/ipsrv.dat");
var_dump($db->get_header());
var_dump($db->get_date());
var_dump($db->get_description());
echo $db->find("8.8.8.255")."\n";
echo $db->find("2001:250::ffff")."\n";
var_dump($db->findx("2001:250::ffff"));
?>
