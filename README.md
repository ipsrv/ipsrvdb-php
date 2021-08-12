# ipsrvdb-php

# Feature
1. Support IPv4 & IPv6.
2. Support output db date, description and header.
3. Support output raw IP info and IP info in a array.

# Installing
```
composer require ipsrv/ipsrvdb
```

# Example
```
<?php

$db = new ipsrv\IPSrvDB("/path/to/ipsrv.dat");
var_dump($db->get_header());
var_dump($db->get_date());
var_dump($db->get_description());
echo $db->find("8.8.8.255")."\n";
echo $db->find("2001:250::ffff")."\n";
var_dump($db->findx("2001:250::ffff"));
?>
```

# Output
```
array(10) {
  [0]=>
  string(14) "continent_code"
  [1]=>
  string(12) "continent_zh"
  [2]=>
  string(16) "country_iso_code"
  [3]=>
  string(10) "country_zh"
  [4]=>
  string(17) "province_iso_code"
  [5]=>
  string(11) "province_zh"
  [6]=>
  string(9) "city_code"
  [7]=>
  string(7) "city_zh"
  [8]=>
  string(6) "isp_zh"
  [9]=>
  string(3) "org"
}
string(8) "20210811"
string(25) "IPSrv, Inc. Dat database."
NA,北美洲,US,美国,,,,,,
AS,亚洲,CN,中国,11,北京市,,,中国教育网,
array(10) {
  ["continent_code"]=>
  string(2) "AS"
  ["continent_zh"]=>
  string(6) "亚洲"
  ["country_iso_code"]=>
    }
  string(2) "CN"
  ["country_zh"]=>
  string(6) "中国"
  ["province_iso_code"]=>
  string(2) "11"
  ["province_zh"]=>
  string(9) "北京市"
  ["city_code"]=>
  string(0) ""
  ["city_zh"]=>
  string(0) ""
  ["isp_zh"]=>
  string(15) "中国教育网"
  ["org"]=>
  string(0) ""
}
```
