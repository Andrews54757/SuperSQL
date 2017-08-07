<?php
/*
MIT License

Copyright (c) 2017 Andrew S

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

/*
 Author: Andrews54757
 License: MIT
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v2.5.0
 Built on: 07/08/2017
*/

class SQLHelper{public$s;public$connections;function __construct($a,$b=null,$c=null,$d=null,$e=array()){$this->connections=array();$f=gettype($a);if($f=="array"){if(gettype($a[0])=="array"){foreach($a as$g=>$h){$i=isset($h["host"])?$h["host"]: "";$b=isset($h["db"])?$h["db"]: "";$c=isset($h["user"])?$h["user"]: "";$d=isset($h["password"])?$h["password"]: "";$j=isset($h["options"])?$h["options"]: array();$k=self::connect($i,$b,$c,$d,$j);array_push($this->connections,$k);}}else{foreach($a as$g=>$h){array_push($this->connections,$h);}}$this->s=$this->connections[0];}else if($f=="string"){$this->s=self::connect($a,$b,$c,$d,$e);array_push($this->connections,$this->s);}else{array_push($this->connections,$a);$this->s=$a;}}static function connect($i,$b,$c,$d,$e=array()){$l="mysql";$m=false;if(gettype($e)=="string"){if(strpos($e,":")!==false){$m=$e;}else{$l=strtolower($e);}}else if(isset($e["dbtype"]))$l=strtolower($e["dbtype"]);if(!$m){$n="";switch($l){case "pgsql":$n="pgsql";$o=array("dbname"=>$b,"host"=>$i);if(isset($e["port"]))$o["port"]=$e["port"];break;case "sybase":$n="dblib";$o=array("dbname"=>$b,"host"=>$i);if(isset($e["port"]))$o["port"]=$e["port"];break;case "oracle":$n="oci";$o=array("dbname"=>isset($i)? "//".$i.":".(isset($e["port"])?$e["port"]: "1521")."/".$b :$b);break;default:$n="mysql";$o=array("dbname"=>$b);if(isset($e["socket"]))$o["unix_socket"]=$e["socket"];else{$o["host"]=$i;if(isset($e["port"]))$o["port"]=$e["port"];}break;}$m=$n.":";if(isset($e['charset'])){$o['charset']=$e['charset'];}$m=$n.":";$p=0;foreach($o as$g=>$q){if($p!=0){$m.=";";}$m.=$g."=".$q;$p++;}}return new SuperSQL($m,$c,$d);}function change($r){$this->s=$this->connections[$r];return$this->s;}function getCon($s=false){if($s){return$this->connections;}else{return$this->s;}}function get($t,$u,$v,$w=null){$x=$this->s->SELECT($t,$u,$v,$w,1)->getData();return($x &&$x[0])?$x[0]: false;}function create($t,$o){$y="CREATE TABLE `".$t."` (";$z=0;foreach($o as$g=>$q){if($z!=0){$y.=", ";}$y.="`".$g."` ".$q;$z++;}$y.=")";return$this->s->query($y);}function drop($t){return$this->s->query("DROP TABLE `".$t."`");}}
?>