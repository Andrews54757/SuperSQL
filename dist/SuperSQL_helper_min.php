<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v1.1.6
 Built on: 07/02/2019
*/

namespace SuperSQL;

class SQLHelper{public$s;public$connections;function __construct($a,$b=null,$c=null,$d=null,$e=array()){$this->connections=array();if(is_array($a)){if(is_array($a[0])){foreach($a as$f=>$g){$h=isset($g['host'])?$g['host']:'';$b=isset($g['db'])?$g['db']:'';$c=isset($g['user'])?$g['user']:'';$d=isset($g['password'])?$g['password']:'';$i=isset($g['options'])?$g['options']:array();$j=self::connect($h,$b,$c,$d,$i);array_push($this->connections,$j);}}else{foreach($a as$f=>$g){array_push($this->connections,$g);}}$this->s=$this->connections[0];}else if(is_string($a)){$this->s=self::connect($a,$b,$c,$d,$e);array_push($this->connections,$this->s);}else{array_push($this->connections,$a);$this->s=$a;}}static function connect($a,$b,$c,$d,$e=array()){$f='mysql';$g=false;if(is_string($e)){if(strpos($e,':')!==false){$g=$e;}else{$f=strtolower($e);}}else if(isset($e['dbtype']))$f=strtolower($e['dbtype']);if(!$g){$h='';switch($f){case 'pgsql':$h='pgsql';$i=array('dbname'=>$b,'host'=>$a);if(isset($e['port']))$i['port']=$e['port'];break;case 'sybase':$h='dblib';$i=array('dbname'=>$b,'host'=>$a);if(isset($e['port']))$i['port']=$e['port'];break;case 'oracle':$h='oci';$i=array('dbname'=>isset($a)?'//'.$a.':'.(isset($e['port'])?$e['port']:'1521').'/'.$b:$b);break;default:$h='mysql';$i=array('dbname'=>$b);if(isset($e['socket']))$i['unix_socket']=$e['socket'];else{$i['host']=$a;if(isset($e['port']))$i['port']=$e['port'];}break;}$g=$h.':';if(isset($e['charset'])){$i['charset']=$e['charset'];}$g=$h.':';$j=0;foreach($i as$k=>$l){if($j!=0){$g.=';';}$g.=$k.'='.$l;$j++;}}return new SuperSQL($g,$c,$d);}function escape($a){$b=strtolower(gettype($a));if($b=='boolean'){$a=$a?'1':'0';}else if($b=='string'){$a=$this->s->con->db->quote($a);}else if($b=='double'||$b=='integer'){$a=(int)$a;}else if($b=='null'){$a='0';}return$a;}function change($a){$this->s=$this->connections[$a];return$this->s;}function getCon($a=false){if($a){return$this->connections;}else{return$this->s;}}function get($a,$b=array(),$c=array(),$d=null){return$this->s->SELECT($a,$b,$c,$d,1)[0];}function create($a,$b){$c='CREATE TABLE `'.$a.'` (';$d=0;foreach($b as$e=>$f){if($d!=0){$c.=', ';}$c.='`'.$e.'` '.$f;$d++;}$c.=')';return$this->s->query($c);}function drop($a){return$this->s->query('DROP TABLE `'.$a.'`');}function replace($a,$b,$c=array()){$d=array();foreach($b as$e=>$f){$g='`'.Parser::rmComments($e).'`';foreach($f as$h=>$i){$g='REPLACE('.$g.', '.$this->escape($h).', '.$this->escape($i).')';}$d['#'.$e]=$g;}return$this->s->UPDATE($a,$d,$c);}function select($a,$b=array(),$c=array(),$d=null,$e=false){return$this->s->SELECT($a,$b,$c,$d,$e);}function insert($a,$b){return$this->s->INSERT($a,$b);}function update($a,$b,$c=array()){return$this->s->UPDATE($a,$b,$c);}function delete($a,$b=array()){return$this->s->DELETE($a,$b);}function sqBase($a,$b,$c){$d=0;$e=array();if($c){Parser::JOIN($c,$a,$e,$d);}if(count($b)!=0){$a.=' WHERE ';$a.=Parser::conditions($b,$e);}$f=$this->_query($a,$e);return$f[0]->fetchColumn();}function count($a,$b=array(),$c=array()){return$this->sqBase('SELECT COUNT(*) FROM `'.$a.'`',$b,$c);}function avg(){return$this->sqBase('SELECT AVG(`'.$column.'`) FROM `'.$table.'`',$a,$b);}function max($a,$b,$c=array(),$d=array()){return$this->sqBase('SELECT MAX(`'.$b.'`) FROM `'.$a.'`',$c,$d);}function min($a,$b,$c=array(),$d=array()){return$this->sqBase('SELECT MIN(`'.$b.'`) FROM `'.$a.'`',$c,$d);}function sum($a,$b,$c=array(),$d=array()){return$this->sqBase('SELECT SUM(`'.$b.'`) FROM `'.$a.'`',$c,$d);}function _query($a,$b){$c=$this->s->con->db->prepare($a);foreach($b as$d=>&$e){$c->bindParam($d+1,$e[0],$e[1]);}$f=$c->execute();return array($c,$f);}function query($a,$b=null){return$this->s->con->query($a,$b);}function transact($a){return$this->s->transact($a);}function selectMap($a,$b,$c=array(),$d=null,$e=false){$f=array();$g=array();function recurse($a,&$b,&$c,&$d){foreach($a as$e=>$f){if(is_int($e)){array_push($c,$f);$g=Parser::getType($f);if($g){$h=Parser::getType($f);if($h&&($h==='int'||$h==='bool'||$h==='string'||$h==='json'||$h==='object'||$h==='double')){$g=false;}}if($g){array_push($b,$g);}else{preg_match('/(?:[^\.]*\.)?(.*)/',$f,$i);array_push($b,$i[1]);}}else{$b[$e]=array();recurse($f,$b[$e],$c,$d);}}}recurse($j,$d,$c,$d);$k=$this->s->select($l,$c,$m,$n,$o);$p=$k->getData();function recurse2($a,$b,&$c){$c=array();foreach($a as$d=>$e){if(is_int($d)){$c[$e]=$b[$e];}else{recurse2($e,$b,$c[$d]);}}}$r->result=array();foreach($d as$f=>$b){recurse2($g,$b,$r->result[$f]);}return$r;}function info(){return array('server'=>$this->s->con->db->getAttribute(\PDO::ATTR_SERVER_INFO),'driver'=>$this->s->con->db->getAttribute(\PDO::ATTR_DRIVER_NAME),'client'=>$this->s->con->db->getAttribute(\PDO::ATTR_CLIENT_VERSION),'version'=>$this->s->con->db->getAttribute(\PDO::ATTR_SERVER_VERSION),'connection'=>$this->s->con->db->getAttribute(\PDO::ATTR_CONNECTION_STATUS));}function getLog(){$a=$this->s->getLog();$b=array();foreach($a as$c){$d=explode('?',$c[0]);$e='';foreach($d as$f=>$g){$e.=$g.(isset($c[1][$f])?$this->escape($c[1][$f][0]):'');}array_push($b,$e);if(isset($c[2])){foreach($c[2]as$h){foreach($h as$i=>$j){$c[1][$i][0]=$j;}$e='';foreach($d as$f=>$g){$e.=$g.(isset($c[1][$f])?$this->escape($c[1][$f][0]):'');}array_push($b,$e);}}}return$b;}function dev(){$this->s->dev();}}
?>