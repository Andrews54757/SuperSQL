<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v1.0.2
 Built on: 17/08/2017
*/

// lib/connector/index.php
class Response{public$result;public$affected;public$ind=0;public$error;public$errorData;public$outTypes;public$complete=false;public$stmt;function __construct($a,$b,&$c,&$d){$this->error=!$b;if(!$b){$this->errorData=$a->errorInfo();}else{$this->outTypes=$c;$this->init($a,$d);$this->affected=$a->rowCount();}}private function init(&$a,&$d){if($d===0){$c=$this->outTypes;$e=$a->fetchAll();if($c){foreach($e as$f=>&$g){$this->map($g,$c);}}$this->result=$e;$this->complete=true;}else if($d===1){$this->stmt=$a;$this->result=array();}}function close(){$this->complete=true;if($this->stmt){$this->stmt->closeCursor();$this->stmt=null;}}private function fetchNextRow(){$g=$this->stmt->fetch();if($g){if($this->outTypes){$this->map($g,$this->outTypes);}array_push($this->result,$g);return$g;}else{$this->complete=true;$this->stmt->closeCursor();$this->stmt=null;return false;}}private function fetchAll(){while($g=$this->fetchNextRow()){}}function map(&$g,&$c){foreach($c as$h=>$i){if(isset($g[$h])){switch($i){case 'int':$g[$h]=(int)$g[$h];break;case 'string':$g[$h]=(string)$g[$h];break;case 'bool':$g[$h]=$g[$h]? true : false;break;case 'json':$g[$h]=json_decode($g[$h]);break;case 'obj':$g[$h]=unserialize($g[$h]);break;}}}}function error(){return$this->error ?$this->errorData : false;}function getData($j=false){if(!$this->complete&&!$j)$this->fetchAll();return$this->result;}function getAffected(){return$this->affected;}function countRows(){return count($this->result);}function next(){if(isset($this->result[$this->ind])){return$this->result[$this->ind++];}else if(!$this->complete){$g=$this->fetchNextRow();$this->ind++;return$g;}else{return false;}}function reset(){$this->ind=0;}}class Connector{public$db;public$log=array();public$dev=false;function __construct($k,$l,$m){$this->db=new \PDO($k,$l,$m);$this->log=array();}function query($n,$o=null,$c=null,$d=0){$p=$this->db->prepare($n);if($o)$q=$p->execute($o);else$q=$p->execute();if($this->dev)array_push($this->log,array($n,$o));if($d!==3){return new Response($p,$q,$c,$d);}else{return$p;}}function _query(&$r,$s,&$t,&$c=null,$d=0){$p=$this->db->prepare($r);if($this->dev)array_push($this->log,array($r,$s,$t));foreach($s as$u=>&$v){$p->bindParam($u+1,$v[0],$v[1]);}$q=$p->execute();if(!isset($t[0])){return new Response($p,$q,$c,$d);}else{$w=array();array_push($w,new Response($p,$q,$c,0));foreach($t as$u=>$x){foreach($x as$y=>&$z){$s[$y][0]=$z;}$q=$p->execute();array_push($w,new Response($p,$q,$c,0));}return$w;}}function close(){$this->db=null;$this->queries=null;}}
// lib/parser/Advanced.php
class AdvParser{static function getArg(&$a){if(isset($a[3])&&$a[0]==='['&&$a[3]===']'){$b=$a[1].$a[2];$a=substr($a,4);return$b;}else{return false;}}static function append(&$c,$d,$e,$f){if(is_array($d)&&$f[$e][2]<5){$g=count($d);for($h=1;$h<$g;$h++){if(!isset($c[$h-1]))$c[$h-1]=array();$c[$h-1][$e]=$d[$h];}}}static function append2(&$i,$j,$k,$f){function stripArgs(&$l){preg_match('/(?:\[.{2}\]){0,2}([^\[]*)/',$l,$m);return$m[1];}function escape($d,$k){if(!isset($k[2]))return$d;switch($k[2]){case 0: return$d ? '1' : '0';break;case 1: return(int)$d;break;case 2: return(string)$d;break;case 3: return$d;break;case 4: return null;break;case 5: return json_encode($d);break;case 6: return serialize($d);break;}}function recurse(&$n,$d,$j,$o,$f){foreach($d as$h=>&$p){if($h[0]==="#")continue;stripArgs($h);$q=$h.'#'.$o;if(isset($j[$q]))$r=$j[$q];else$r=$j[$h];$s=is_array($p)&&(!isset($f[$r][2])||$f[$r][2]<5);if($s){if(isset($p[0])){foreach($p as$t=>&$u){$v=$r+$t;if(isset($n[$v]))echo 'SUPERSQL WARN: Key collision: '.$h;$n[$v]=escape($u,$f[$v]);}}else{recurse($n,$p,$j,$o.'/'.$h,$f);}}else{if(isset($n[$r]))echo 'SUPERSQL WARN: Key collision: '.$h;$n[$r]=escape($p,$f[$r]);}}}$g=count($k);for($l=1;$l<$g;$l++){$d=$k[$l];if(!isset($i[$l-1]))$i[$l-1]=array();recurse($i[$l-1],$d,$j,'',$f);}}static function quote($a){preg_match('/([^.]*)\.?(.*)?/',$a,$m);if($m[2]!==''){return '`'.$m[1].'.'.$m[2].'`';}else{return '`'.$m[1].'`';}}static function table($w){if(is_array($w)){$x='';foreach($w as$t=>&$d){$y=self::getType($d);if($t!==0)$x.=', ';$x.='`'.$d.'`';if($y)$x.=' AS `'.$y.'`';}return$x;}else{return '`'.$w.'`';}}static function value($z,$aa){$ba=$z ?$z : gettype($aa);$z=\PDO::PARAM_STR;$ca=2;if($ba==='integer'||$ba==='int'||$ba==='double'||$ba==='doub'){$z=\PDO::PARAM_INT;$ca=1;$aa=(int)$aa;}else if($ba==='string'||$ba==='str'){$aa=(string)$aa;$ca=2;}else if($ba==='boolean'||$ba==='bool'){$z=\PDO::PARAM_BOOL;$aa=$aa ? '1' : '0';$ca=0;}else if($ba==='null'||$ba==='NULL'){$ca=4;$z=\PDO::PARAM_NULL;$aa=null;}else if($ba==='resource'||$ba==='lob'){$z=\PDO::PARAM_LOB;$ca=3;}else if($ba==='json'){$ca=5;$aa=json_encode($aa);}else if($ba==='obj'){$ca=6;$aa=serialize($aa);}else{$aa=(string)$aa;echo 'SUPERSQL WARN: Invalid type '.$ba.' Assumed STRING';}return array($aa,$z,$ca);}static function getType(&$a){if(isset($a[1])&&$a[strlen($a)-1]===']'){$da=strrpos($a,'[');if($da===false){return '';}$b=substr($a,$da+1,-1);$a=substr($a,0,$da);return$b;}else return '';}static function rmComments($a){preg_match('/([^#]*)/',$a,$m);return$m[1];}static function conditions($k,&$f=false,&$ea=false,&$e=0){$fa=function(&$fa,$k,&$ea,&$e,&$f,$ga=' AND ',$ha=' = ',$ia=''){$ja=0;$x='';foreach($k as$l=>&$d){if($l[0]==='#'){$ka=true;$l=substr($l,1);}else{$ka=false;}preg_match('/^(?:\[(?<a>.{2})\])?(?:\[(?<b>.{2})\])?(?<out>.*)/',$l,$m);$l=$m["out"];$la=isset($m["a"])?$m["a"]: false;$ma=isset($m["b"])?$m["b"]: false;$na=!isset($d[0]);$oa=$ga;$pa=$ha;$z=$ka ? false : self::getType($l);$qa=self::quote(self::rmComments($l));switch($la){case '||':$la=$ma;$oa=' OR ';break;case '&&':$la=$ma;$oa=' AND ';break;}switch($la){case '!=':$pa=' != ';break;case '>>':$pa=' > ';break;case '<<':$pa=' < ';break;case '>=':$pa=' >= ';break;case '<=':$pa=' <= ';break;case '~~':$pa=' LIKE ';break;case '!~':$pa=' NOT LIKE ';break;default: if(!$na||$la==='==')$pa=' = ';break;}if($ja!==0)$x.=$ga;if(is_array($d)&&$z!=='json'&&$z!=='obj'){if($na){$x.='('.$fa($fa,$d,$ea,$e,$f,$oa,$pa,$ia.'/'.$l).')';}else{if($ea!==false&&!$ka){$ea[$l]=$e;$ea[$l.'#'.$ia]=$e++;}foreach($aa as$h=>&$p){if($h!==0)$x.=$oa;$e++;$x.=$qa.$pa;if($ka){$x.=$p;}else if($f!==false){$x.='?';array_push($f,self::value($z,$p));}else{if(is_int($p)){$x.=$p;}else{$x.=self::quote($p);}}}}}else{$x.=$qa.$pa;if($ka){$x.=$d;}else{if($f!==false){$x.='?';array_push($f,self::value($z,$d));}else{if(is_int($d)){$x.=$d;}else{$x.=self::quote($d);}}if($ea!==false){$ea[$l]=$e;$ea[$l.'#'.$ia]=$e++;}}}$ja++;}return$x;};return$fa($fa,$k,$ea,$e,$f);}static function JOIN($ga,&$x){foreach($ga as$l=>&$d){if($l[0]==='#'){$ka=true;$l=substr($l,1);}else{$ka=false;}$la=self::getArg($l);switch($la){case '<<':$x.=' RIGHT JOIN ';break;case '>>':$x.=' LEFT JOIN ';break;case '<>':$x.=' FULL JOIN ';break;default:$x.=' JOIN ';break;}$x.='`'.$l.'` ON ';if($ka){$x.='val';}else{$x.=self::conditions($d);}}}static function columns($ra,&$x,&$sa){$ta='';$ua=$ra[0][0];if($ua==='D'||$ua==='I'){if($ra[0]==='DISTINCT'){$va=1;$x.='DISTINCT ';array_splice($ra,0,1);}else if(substr($ra[0],0,11)==='INSERT INTO'){$va=1;$x=$ra[0].' '.$x;array_splice($ra,0,1);}else if(substr($ra[0],0,4)==='INTO'){$va=1;$ta=' '.$ra[0].' ';array_splice($ra,0,1);}}if(isset($ra[0])){foreach($ra as$t=>&$d){preg_match('/(?<column>[a-zA-Z0-9_\.]*)(?:\[(?<alias>[^\]]*)\])?(?:\[(?<type>.*)\])?/',$d,$wa);$d=$wa["column"];$xa=false;if(isset($wa["alias"])){$xa=$wa["alias"];if(isset($wa["type"])){$z=$wa["type"];}else{if($xa==="json"||$xa==="obj"||$xa==="int"||$xa==="string"||$xa==="bool"){$z=$xa;$xa=false;}else$z=false;}if($z){if(!$sa)$sa=array();$sa[$xa ?$xa :$d]=$z;}}if($t!=0){$x.=', ';}$x.=self::quote($d);if($xa)$x.=' AS `'.$xa.'`';}}else$x.='*';$x.=$ta;}static function SELECT($w,$ra,$ya,$ga,$za){$x='SELECT ';$f=array();$i=array();$sa=null;if(!isset($ra[0])){$x.='*';}else{self::columns($ra,$x,$sa);}$x.=' FROM '.self::table($w);if($ga){self::JOIN($ga,$x);}if(!empty($ya)){$x.=' WHERE ';$e=array();if(isset($ya[0])){$x.=self::conditions($ya[0],$f,$e);self::append2($i,$e,$ya,$f);}else{$x.=self::conditions($ya,$f,$e);}}if($za){if(is_int($za)){$x.=' LIMIT '.$za;}else if(is_string($za)){$x.=' '.$za;}}return array($x,$f,$i,$sa);}static function INSERT($w,$ab){$x='INSERT INTO '.self::table($w).' (';$f=array();$i=array();$bb='';$t=0;$cb=0;$j=array();$db=isset($ab[0]);$k=$db ?$ab[0]:$ab;foreach($k as$l=>&$d){if($l[0]==='#'){$ka=true;$l=substr($l,1);}else{$ka=false;}if($cb!==0){$x.=', ';$bb.=', ';}$z=self::getType($l);$x.='`'.self::rmComments($l).'`';if($ka){$bb.=$d;}else{$bb.='?';array_push($f,self::value($z,$d));if($db){$j[$l]=$t++;}else{self::append($i,$d,$t++,$f);}}$cb++;}if($db)self::append2($i,$j,$ab,$f);$x.=') VALUES ('.$bb.')';return array($x,$f,$i);}static function UPDATE($w,$ab,$ya){$x='UPDATE '.self::table($w).' SET ';$f=array();$i=array();$t=0;$cb=0;$j=array();$db=isset($ab[0]);$k=$db ?$ab[0]:$ab;foreach($k as$l=>&$d){if($l[0]==='#'){$ka=true;$l=substr($l,1);}else{$ka=false;}if($cb!==0){$x.=', ';}if($ka){$x.='`'.$l.'` = '.$d;}else{$la=self::getArg($l);$x.='`'.$l.'` = ';switch($la){case '+=':$x.='`'.$l.'` + ?';break;case '-=':$x.='`'.$l.'` - ?';break;case '/=':$x.='`'.$l.'` / ?';break;case '*=':$x.='`'.$l.'` * ?';break;default:$x.='?';break;}$z=self::getType($l);array_push($f,self::value($z,$d));if($db){$j[$l]=$t++;}else{self::append($i,$d,$t++,$f);}}$cb++;}if($db)self::append2($i,$j,$ab,$f);if(!empty($ya)){$x.=' WHERE ';$e=array();if(isset($ya[0])){$x.=self::conditions($ya[0],$f,$e,$t);self::append2($i,$e,$ya,$f);}else{$x.=self::conditions($ya,$f,$e,$t);}}return array($x,$f,$i);}static function DELETE($w,$ya){$x='DELETE FROM '.self::table($w);$f=array();$i=array();if(!empty($ya)){$x.=' WHERE ';$e=array();if(isset($ya[0])){$x.=self::conditions($ya[0],$f,$e);self::append2($i,$e,$ya,$f);}else{$x.=self::conditions($ya,$f,$e);}}return array($x,$f,$i);}}
// index.php
class SuperSQL{public$con;public$lockMode=false;function __construct($a,$b,$c){$this->con=new Connector($a,$b,$c);}function SELECT($d,$e=array(),$f=array(),$g=null,$h=false){if((is_int($g)||is_string($g))&&!$h){$h=$g;$g=null;}$i=AdvParser::SELECT($d,$e,$f,$g,$h);return$this->con->_query($i[0],$i[1],$i[2],$i[3],$this->lockMode ? 0 : 1);}function INSERT($d,$j){$i=AdvParser::INSERT($d,$j);return$this->con->_query($i[0],$i[1],$i[2]);}function UPDATE($d,$j,$f=array()){$i=AdvParser::UPDATE($d,$j,$f);return$this->con->_query($i[0],$i[1],$i[2]);}function DELETE($d,$f=array()){$i=AdvParser::DELETE($d,$f);return$this->con->_query($i[0],$i[1],$i[2]);}function query($k,$l=null,$m=null,$n=0){return$this->con->query($k,$l,$m,$n);}function close(){$this->con->close();}function dev(){$this->con->dev=true;}function getLog(){return$this->con->log;}function transact($o){$this->con->db->beginTransaction();$p=$o($this);if($p===false)$this->con->db->rollBack();else$this->con->db->commit();return$p;}function modeLock($q){$this->lockMode=$q;}}
?>