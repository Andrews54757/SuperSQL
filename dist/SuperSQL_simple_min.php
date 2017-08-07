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

// lib/connector/index.php
class Response{public$result;public$affected;public$ind;public$error;public$errorData;function __construct($a,$b){$this->error=!$b;if(!$b){$this->errorData=$a->errorInfo();}else{$this->result=$a->fetchAll();$this->affected=$a->rowCount();}$this->ind=0;$a->closeCursor();}function error(){return$this->error ?$this->errorData : false;}function getData(){return$this->result;}function getAffected(){return$this->affected;}function next(){return$this->result[$this->ind++];}function reset(){$this->ind=0;}}class Connector{public$queries=array();public$db;public$log=array();public$dev=false;function __construct($c,$d,$e){$this->db=new \PDO($c,$d,$e);$this->log=array();}function query($f,$g=null){$h=$this->db->prepare($f);if($g)$i=$h->execute($g);else$i=$h->execute();if($this->dev)array_push($this->log,array($f,$g));return new Response($h,$i);}function _query($j,$k,$l,$m){if(isset($this->queries[$j."|".$m])){$n=$this->queries[$j."|".$m];$h=$n[1];$o=&$n[0];foreach($k as$p=>$q){$o[$p][0]=$q[0];}if($this->dev)array_push($this->log,array("fromcache",$j,$m,$k,$l));}else{$h=$this->db->prepare($j);$o=$k;foreach($o as$p=>&$r){$h->bindParam($p + 1,$r[0],$r[1]);}$this->queries[$j."|".$m]=array(&$o,$h);if($this->dev)array_push($this->log,array($j,$m,$k,$l));}if(count($l)==0){$i=$h->execute();return new Response($h,$i);}else{$s=array();$i=$h->execute();array_push($s,new Response($h,$i));foreach($l as$p=>$t){foreach($t as$u=>$v){$o[$u][0]=$v;}$i=$h->execute();array_push($s,new Response($h,$i));}return$s;}}function close(){$this->db=null;$this->queries=null;}function clearCache(){$this->queries=array();}}
// lib/parser/Simple.php
class SimpleParser{public static function WHERE($a,&$b,&$c){if(count($a)!=0){$b.=" WHERE ";$d=0;foreach($a as$e=>$f){if($d!=0){$b.=" AND ";}$b.="`".$e."` = ?";array_push($c,$f);$d++;}}}public static function SELECT($g,$h,$a,$i){$b="SELECT ";$c=array();$j=count($h);if($j==0){$b.="*";}else{for($d=0;$d<$j;$d++){if($d!=0){$b.=", ";}$b.="`".$h[$d]."`";}}$b.="FROM `".$g."`";self::WHERE($a,$b);$b.=" ".$i;return array($b,$c);}public static function INSERT($g,$k){$b="INSERT INTO `".$g."` (";$l=") VALUES (";$c=array();$d=0;foreach($k as$e=>$f){if($d!=0){$b.=", ";$l.=", ";}$b.="`".$e."`";$l.="?";array_push($c,$f);$d++;}$b.=$l;return array($b,$c);}public static function UPDATE($g,$k,$a){$b="UPDATE `".$g."` SET ";$c=array();$d=0;foreach($k as$e=>$f){if($d!=0){$b.=", ";}$b.="`".$e."` = ?";array_push($c,$f);$d++;}self::WHERE($a,$b,$c);return array($b,$c);}public static function DELETE($g,$a){$b="DELETE FROM `".$g."`";$c=array();self::WHERE($a,$b,$c);return array($b,$c);}}
// index.php
class SuperSQL{public$connector;function __construct($a,$b,$c){$this->connector=new Connector($a,$b,$c);}function sSELECT($d,$e,$f,$g=""){$h=SimpleParser::SELECT($d,$e,$f,$g);return$this->connector->_query($h[0],$h[1],$h[2],$h[3]);}function sINSERT($d,$i){$h=SimpleParser::INSERT($d,$i);return$this->connector->_query($h[0],$h[1],$h[2],$h[3]);}function sUPDATE($d,$i,$f){$h=SimpleParser::UPDATE($d,$i,$f);return$this->connector->_query($h[0],$h[1],$h[2],$h[3]);}function sDELETE($d,$f){$h=SimpleParser::DELETE($d,$f);return$this->connector->_query($h[0],$h[1],$h[2],$h[3]);}function query($j,$k=null){return$this->connector->query($j,$k);}function close(){$this->connector->close();}function dev(){$this->connector->dev=true;}function getLog(){return$this->connector->log;}function clearCache(){$this->connector->clearCache();}}
?>