<? 	

function initializeKB(){
	//print "deleting\n";
	$deleteo=deleteKB();
	if(strpos(strtolower($deleteo),"successfully")==false){
		return false;
	}
	//print "creating\n";
	$createo=createKB();
	if(strpos(strtolower($createo),"successfully")==false){
		return false;
	}
	//print "populating\n";
	$populateo=populateWithSpaWN();
	return $populateo;
}

function deleteKB(){
	$url="http://localhost:5820/admin/databases/LER2016";
	//$response=http_get_stardog_query($url);
	$opts = array(
  'http'=>array(
    'method'=>"DELETE",
    'header'=>"Authorization: Basic YWRtaW46YWRtaW4=\r\n" .
              "Accept: application/json\r\n"
  )
);

	$context = stream_context_create($opts);

	// Open the file using the HTTP headers set above
	$file = file_get_contents($url, false, $context);
	//print $file;
	return $file;	
}

function createKB(){	
	
	//$options = 'root={"dbname" : "fourbythreeKB","options" : {"reasoning.type" : "SL", "icv.active.graphs" : "default" , "search.enabled" : true, "reasoning.consistency.automatic" : true, "reasoning.punning.enabled" : true, "reasoning.sameas" : "FULL" }, "files" : [{ "filename":"/opt/lampp/htdocs/fourbythree/data/NaturalInteraction.rdf", "context":"tag:stardog:api:context:default" }]}';
	$options= 'root={"dbname" : "LER2016","options" :{"database.namespaces":["rdf=http://www.w3.org/1999/02/22-rdf-syntax-ns#","rdfs=http://www.w3.org/2000/01/rdf-schema#","xsd=http://www.w3.org/2001/XMLSchema#","stardog=tag:stardog:api:"],"database.online":true,"database.connection.timeout":"1h","index.differential.enable.limit":1000000,"index.differential.merge.limit":10000,"index.literals.canonical":true,"index.named.graphs":true,"index.persist":true,"index.persist.sync":false,"index.statistics.update.automatic":true,"index.type":"Disk","spatial.enabled":false,"icv.active.graphs":["tag:stardog:api:context:default"],"icv.consistency.automatic":false,"icv.enabled":false,"icv.reasoning.enabled":false,"reasoning.type":"DL","reasoning.approximate":false,"reasoning.sameas":"FULL","reasoning.schema.graphs":["tag:stardog:api:context:all"],"reasoning.virtual.graph.enabled":true,"reasoning.punning.enabled":true,"reasoning.consistency.automatic":true,"reasoning.schema.timeout":"60s","search.enabled":true,"search.reindex.mode":"sync","transaction.logging":false,"transaction.isolation":"SNAPSHOT","database.name":"fourbythreeKB","database.archetypes":[]}, "files" : [{ "filename":"/opt/lampp/htdocs/LER2016/data/LER3-2014.rdf", "filename":"/opt/lampp/htdocs/LER2016/data/LER3-2014-ErisonoData.PeopleImportedAndInfered.rdf","context":"tag:stardog:api:context:default" }]}';
	//$options= 'root={"dbname" : "fourbythreeKB","options" :{"database.namespaces":["rdf=http://www.w3.org/1999/02/22-rdf-syntax-ns#","rdfs=http://www.w3.org/2000/01/rdf-schema#","xsd=http://www.w3.org/2001/XMLSchema#","stardog=tag:stardog:api:"],"database.online":true,"database.connection.timeout":"1h","index.differential.enable.limit":1000000,"index.differential.merge.limit":10000,"index.literals.canonical":true,"index.named.graphs":true,"index.persist":true,"index.persist.sync":false,"index.statistics.update.automatic":true,"index.type":"Disk","spatial.enabled":false,"icv.active.graphs":["tag:stardog:api:context:default"],"icv.consistency.automatic":false,"icv.enabled":false,"icv.reasoning.enabled":false,"reasoning.type":"DL","reasoning.approximate":false,"reasoning.sameas":"FULL","reasoning.schema.graphs":["tag:stardog:api:context:all"],"reasoning.virtual.graph.enabled":true,"reasoning.punning.enabled":true,"reasoning.consistency.automatic":true,"reasoning.schema.timeout":"60s","search.enabled":true,"search.reindex.mode":"sync","transaction.logging":false,"transaction.isolation":"SNAPSHOT","database.name":"fourbythreeKB","database.archetypes":[]}, "files" : []}';
	$opts = array(
	'http'=>array(
		'method'=>"POST",
		'header'=>"Authorization: Basic YWRtaW46YWRtaW4=\r\n" .
				  "Accept: */*\r\n" .
				  "Content-Type: multipart/form-data\r\n"  ,
		'content'=> $options
		)
	);

	$context = stream_context_create($opts);
	$url="http://localhost:5820/admin/databases/";

	// Open the file using the HTTP headers set above
	$file = file_get_contents($url, false, $context);
	//print $file;
	return $file;	
}

function populateWithSpaWN(){
	//spa_WN 
	$IP="localhost";
	$conexion = mysql_connect($IP . ":3306", "root", "Sii206744+") or die(mysql_error());
	mysql_select_db("mcr30", $conexion) or die(mysql_error());


	//Get all the verbs in the initial KB
	$sparqlqueryActions=	<<<EOT
prefix base: <http://tekniker.robotassistant.es#> 
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX foaf:<http://xmlns.com/foaf/0.1/>
select distinct ?p ?s where {?p foaf:name ?s .
  				?p a geo:TemporalThing.
               }
EOT;
	//print $sparqlqueryActions . "\n";
	$queryresponse = curl_stardog_query ($sparqlqueryActions);
	$vstr=$queryresponse;
	//print $vstr;
	$vxmlDoc = new DOMDocument();
	$vxmlDoc->loadXML($vstr);
	
	$vx = new DomXPath($vxmlDoc);
	$vx->registerNamespace("s", "http://www.w3.org/2005/sparql-results#");
	
	$labels = $vx->query("//s:result/s:binding[@name='s']/s:literal/text()");
	$concepts= $vx->query("//s:result/s:binding[@name='p']/s:uri/text()");
	//$classes = $vx->query("//s:result/s:binding[@name='class']/s:uri/text()");
	//for each verb search WN for synomims (relation =12)
	//print_r($labels);
	//print_r($concepts);
	for ($counteri = 0; $counteri < $labels->length; $counteri += 1):
			$label = trim(str_replace("\n",'', $labels->item($counteri)->textContent));
			$concept= trim(str_replace("\n",'', $concepts->item($counteri)->textContent));
			//$class = trim(str_replace("\n",'', $classes->item($counteri)->textContent));
			$wordnetquery=<<<EOT
select distinct word from `wei_spa-30_variant` where offset in (select va.offset from `wei_spa-30_variant` as va inner join `wei_spa-30_synset` as s on va.offset=s.offset where va.word='{$label}' and va.pos='v')
EOT;
//SELECT distinct word FROM `wei_spa-30_relation` inner join `wei_spa-30_variant` on (sourceSynset=offset) WHERE (relation="12") and sourceSynset like '%-v' and (sourceSynset IN (select offset from `wei_spa-30_variant` where offset like '%-v' and word="{$label}") or targetSynset IN (select offset from `wei_spa-30_variant` where offset like '%-v' and word="{$label}"))


			//print $label . " " . $concept . "\n";
			//print $wordnetquery . "\n";
			$result = mysql_query($wordnetquery);	
			//Update KB with the synonims
			if (mysql_num_rows($result)>0) {
				//print $label . ": ";
				//print_r($result);
				$sparqlupdate = <<<EOT
prefix base: <http://tekniker.robotassistant.es#> 
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
insert data {
EOT;
				while ($row = mysql_fetch_assoc($result)) {
					//if($row["word"]!="ser" and $row["word"]!="estar" and $row["word"]!="dejar"){
					if($row["word"]!="querer" && $row["word"]!="ser" && $row["word"]!="estar"  && strpos($row["word"], '_') == FALSE){
						//$sparqlupdate .= " <" . $concept . "> fbt:tag " . '"' . utf8_encode($row["word"]) . '"' . "@es ." ;
						/*if(preg_match("/[áéíóúü]/",utf8_encode($row["word"]))!=1){
							$sparqlupdate .= " <" . $concept . "> fbt:tag " . '"' . $row["word"] . '"' . "@es ." ;
						}
						else{
							//print $concept . ": " . $row["word"] . "\t" . utf8_encode($row["word"]) . "\t" . utf8_decode($row["word"]) . "\n";
							$sparqlupdate .= " <" . $concept . "> fbt:tag " . '"' . utf8_encode($row["word"]) . '"' . "@es ." ;
						}*/
						$sparqlupdate .= " <" . $concept . "> base:wntag " . '"' . utf8_encode($row["word"]) . '" .'; //. "@es ." ;
					}
				}
				$sparqlupdate .= "}";
				//print $label . "\n";
				//print $label . ": " . $sparqlupdate . "\n";
				//$response = stardog_query($sparqlupdate);
				$response = curl_stardog_query($sparqlupdate);
				//print $response; 
				if(strpos(strtolower($response),"<boolean>true</boolean>")==false){
					//print $response; 
					mysql_close($conexion);
					return false;
				}				
			}		
	endfor;
	mysql_close($conexion);
	return true;
}




function interpretarComando($texto,$zona){

	$IP="localhost";
	$puerto="50020";
	
	$textoanalizado = analisis_sintactico_freeling($IP,$puerto,$texto);
	//print $textoanalizado;
	$destino=array("","","");
	$verbo=array("","");

	$destino=obtenerDestino($textoanalizado);
	$verbo=obtenerVerbo($textoanalizado);
	print "Destino\n";
	print_r($destino);
	print "Verbo\n";
	print_r($verbo);
	$resultado = valorarPeticion($verbo,$destino,$zona,$IP);

	return $resultado;
}
function valorarPeticion($verbo,$destino,$zona,$IP){
	//print time() ."\n";
	$textoOpcionesMaxSuperadas=array("Vaya, vaya! Me vienen demasiadas ideas a la cabeza. ¿Podrías reformular la petición y concretar un poco más?","Caramba! La cantidad de posibilidades que responden a tu petición me abruman. ¿Podrías concretar un poco más?");
	$textopt=sizeof($textoOpcionesMaxSuperadas)-1;
	$nummaxopciones=3;
	$informaciontopverbo=array("","","");
	$informacionverbo=array("","","");
	$zona=str_replace(" "," and ",$zona);
	#Existe acción en KB (tipo:sp/ob/pe)?
	//print_r($verbo);
	//print_r($destino);
	if($verbo[0]!="" && $verbo[0]!=$verbo[1]){
		$informaciontopverbo=existe_accion_KB($verbo[0]);
	}
	if($verbo[1]!=""){
		$informacionverbo=existe_accion_KB($verbo[1]);
	}
	
	#Tipo de destino si existe (sp/ob/pe)
	
	$dest_tipos=tipo_destino_KB($destino[1]);
	
	//print_r($dest_tipos);
	//print_r($informaciontopverbo);
	//print_r($informacionverbo);

	#Existe en WordNet acción como sinonimo (tipo:sp/ob/pe)?
	$wordnet=0;
	if($informaciontopverbo===array("","","") && $informacionverbo===array("","","")) { # No existe acción en KB
		//QUITANDO WN
		//print "Aqui\n" ;
		//if($informaciontopverbo===array("","","") && $informacionverbo===array("","","") && $verbo[1]!=""){
		
		if($verbo[1]!=""){
			$informacionverbo=existe_accion_equivalente_KB_WordNet($verbo[1],$IP,$dest_tipos);
		}
		//print_r($informacionverbo);
		//if($informaciontopverbo===array("","","") && $informacionverbo===array("","","") && $verbo[0]!="" && $verbo[0]!=$verbo[1]){
		if($verbo[0]!=""){
			$informaciontopverbo=existe_accion_equivalente_KB_WordNet($verbo[0],$IP,$dest_tipos);
		}
		//print_r($informaciontopverbo);
		//*/
		if($informaciontopverbo===array("","","") && $informacionverbo===array("","","")){ # No existe acción en WordNet acción como sinonimo
			//print "No existe sinonimo!\n";
			if($verbo[0]=="" && $verbo[1]==""){
				$fraseVerbo="No hay acción indicada en la petición,";
			}
			else{
				$fraseVerbo="La acción ". $verbo[1] ." no es válida,";
			}
			if($dest_tipos=== array("","","") && $destino[1]!=""){
				#No existe destino en KB
				//print time() ."\n";
				return array(0,0,0,  $fraseVerbo . " y el destino " . $destino[0] . " no es válido. Por favor, puede refurmular la petición?");
			}	
			else{
				if($destino[1]!=""){
					$destout=existe_destino_KB($destino[1],array("","",""),"",$wordnet,$zona,$dest_tipos);
				    //print $destout . "\n";
					if($destout!=""){ #Existe destino en KB
						if(cumple_numero_opciones_maximas($destout,$nummaxopciones)){
							//print time() ."\n";
							return array(0,1,$destout, $fraseVerbo . " pero si desea le puedo acompañar a ");
						}
						else{
							//print time() ."\n";
							return array(0,1,0, $textoOpcionesMaxSuperadas[rand(0,$textopt)]);
						}
					}
					else{ #No existe destino en KB
					//print time() ."\n";
						return array(0,0,0,  $fraseVerbo . " y el destino " . $destino[0] . " no es válido. Por favor, puede refurmular la petición?");
					}
				}
				else{
				//print time() ."\n";
					return array(0,0,0,$fraseVerbo . " y no hay destino indicado en la petición");
				}
			}
		}
		else{
			$wordnet=1;
		}
	}

	//print "WordNet: " . $wordnet . "\n";
	
	//return array(0,0,0,"" . "");


	#Existe acción bien sea en KB o como sinonimo en Wordnet.
	#if($destino[1]!=""){
	$destout="";
	$verbotmp=$verbo[1];
	//print_r($dest_tipos);
	//print_r($informacionverbo);
	if($destino[1]!="" && $dest_tipos !== array("","","")){
		$destout=existe_destino_KB($destino[1],$informacionverbo,$verbo[1],$wordnet,$zona,$dest_tipos);	
		//print $destout . "\n";
	}
	if($destout=="" && $verbo[1]!=$verbo[0] && $destino[1]!="" && $dest_tipos !== array("","","")){
		$destout=existe_destino_KB($destino[1],$informaciontopverbo,$verbo[0],$wordnet,$zona,$dest_tipos);
		$verbo[1]=$verbo[0];
		
	}
	#print "DestinoOut: -----------------------------" . $destout . "\n";
	if($destout!=""){#La acción y el destino existen y son compatibles.
		if(cumple_numero_opciones_maximas($destout,$nummaxopciones)){
			//print "Hemendik\n";
			//print time() ."\n";
			return array(1,1,$destout,"Por favor, confirme si lo que desea es que le guíe ");
		}
		else{
			//print time() ."\n";
			return array(1,1,0, $textoOpcionesMaxSuperadas[rand(0,$textopt)]);
		}
	}
	else{
		if($destino[1]!="" && $dest_tipos !== array("","","")){
			$destout=existe_destino_KB($destino[1],array("","",""),"",$wordnet,$zona,$dest_tipos);
		}
		if($destout!=""){#La acción y el destino existen pero no son compatibles.
			//if(cumple_numero_opciones_maximas($destout,$nummaxopciones=)){
				//print time() ."\n";
				return array(1,1,0,"La acción " . $verbo[1] . " no es compatible con el destino " . $destino[1] . ". Por favor, puede refurmular la petición?");
			//}
			//else{
			//	return array(1,1,0, $textoOpcionesMaxSuperadas[rand(0,$textopt)]);
			//}			
		}
		else{#La acción existe pero no el destino; Existen instancias asociadas a la accion?
			//print "Llega al calculo necesario \n";
			//print_r($informacionverbo);
			$destout=existen_instancias_KB_con_verbo($informacionverbo,$verbotmp,$wordnet,$zona);
			//print "Con el sinonimo del verbo: " . $destout . "\n";
			if($destout==""){
				$destout=existen_instancias_KB_con_verbo($informaciontopverbo,$verbo[0],$wordnet,$zona);
				$verbo[1]=$verbo[0];
			}
			
			if($destino[0]==""){
				$frasedestino="No se ha indicado destino ";
			}
			else{
				$frasedestino="El destino " . $destino[0] . " no existe ";
			}
			if($destout==""){#No hay destinos asociados a la acción.
				//print time() ."\n";
				return array(1,0,0, $frasedestino. " y la acción " . $verbo[1] . " no es aplicable a nada del entorno. Por favor, puede reformular la petición?");
			}
			else{#Existen destinos asociados a la acción.
				if(cumple_numero_opciones_maximas($destout,$nummaxopciones)){
					//print time() ."\n";
					return array(1,0,$destout, $frasedestino . " sin embargo si desea le puedo guiar ");
				}
				else{
					//print time() ."\n";
					return array(1,0,0, $frasedestino . " y como alternativas...". $textoOpcionesMaxSuperadas[rand(0,$textopt)]);
				}
			}
		}
	}
	#}
	
}


#############################FUNCIONES###################################

function analisis_sintactico_freeling($IP,$puerto,$texto)
{

//// -----------------------------------------------------
//// Alternatively, you can connect to an existing server on any machine.
////    Note that this will only work if you previously used the
////    "analyze" script to launch a server on that machine:port
//$b = new analyzer("192.168.163.129:50008");

$b = new analyzer($IP . ":" . $puerto);
$outputfreeling = $b->analyze_text($texto . ".");

$outputfreeling=utf8_decode($outputfreeling);

return $outputfreeling;

}

function obtenerDestino($output){
$form="";
$lem="";
$pos="";
//print $output."\n";
if(preg_match("/sn\/obj\-prep\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]+[s\-amfs]+\/[adjsn]+\-mod\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)[\s\]]+sp\-de\/sp\-obj\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]+sn\/obj\-prep\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)/", $output,$matches)){
	$form=$matches[1]. " " . $matches[4] . " " . $matches[7] . " " . $matches[10] ;
	$formo=$form ;
	$lem=$matches[2] . " " . $matches[5] . " " . $matches[8] . " " . $matches[11];
	$lemo = $lem;
	$pos=$matches[3] . " " . $matches[6] . " " . $matches[9] . " " . $matches[12];
	$poso = $pos;
	//print "1\n";
}
//grup-sp/sp-obj/(a a SPS00 -) [
//    sn/obj-prep/(sala sala NCFS000 03588287:0.022155/03089581:0.020457/02476792:0.017735) [
//      ...
//    ]
//  ]
//  sp-de/sp-obj/(de de SPS00 -) [
//    sn/obj-prep/(reuniones reuniÃ³n NCFP000 00798100:0.022690/00799023:0.015617/06166238:0.014208/00245609:0.011601/06167318:0.011463/00799654:0.008440/06258566:0.006550)
//  ]
//  sn/subj/(D d NP00000 -)
else if(preg_match("/sn\/[dobj\-presuatmnlch]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]*[\s\]]*sp\-de\/sp\-[objmd]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]+sn\/obj\-prep\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)[^\]]*[\s\]]*sn\/[subjod]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)/", $output,$matches)){
	$form=$matches[1]. " " . $matches[4] . " " . $matches[7] . " " . $matches[10] ;
	$formo=$form ;
	$lem=$matches[2] . " " . $matches[5] . " " . $matches[8] . " " . $matches[11];
	$lemo = $lem;
	$pos=$matches[3] . " " . $matches[6] . " " . $matches[9] . " " . $matches[12];
	$poso = $pos;
	//print "2\n";
}

//grup-sp/sp-obj/(a a SPS00 -) [
//    sn/obj-prep/(sala sala NCFS000 03588287:0.022155/03089581:0.020457/02476792:0.017735) [
//      ...
//    ]
//  ]
//  sp-de/sp-obj/(de de SPS00 -) [
//    sn/obj-prep/(reuniones reuniÃ³n NCFP000 00798100:0.022690/00799023:0.015617/06166238:0.014208/00245609:0.011601/06167318:0.011463/00799654:0.008440/06258566:0.006550)
//  ]
else if(preg_match("/sn\/[dobj\-presuatmnlch]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]*[\s\]]*sp\-de\/sp\-[objmd]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]+sn\/obj\-prep\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)/", $output,$matches)){
	$form=$matches[1]. " " . $matches[4] . " " . $matches[7];
	$formo=$form ;
	$lem=$matches[2] . " " . $matches[5] . " " . $matches[8];
	$lemo = $lem;
	$pos=$matches[3] . " " . $matches[6] . " " . $matches[9];
	$poso = $pos;
	//print "3\n";
}

//sn/dobj/(sala sala NCFS000 03588287:0.022155/03089581:0.020457/02476792:0.017735) [
//      ...
//      sp-de/sp-mod/(de de SPS00 -) [
//        sn/obj-prep/(micromontajes micromontaje NCMP000 -)
//      ]
//    ]
//else if(preg_match("/sn\/dobj\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]+sp\-de\/sp\-mod\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]+sn\/obj\-prep\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)/", $output,$matches)){
//	$form=$matches[1]. " " . $matches[4] . " " . $matches[7];
//	$formo=$form ;
//	$lem=$matches[2] . " " . $matches[5] . " " . $matches[8];
//	$lemo = $lem;
//	$pos=$matches[3] . " " . $matches[6] . " " . $matches[9];
//	$poso = $pos;
//}
//grup-sp/sp-obj/(a a SPS00 -) [
//    sn/obj-prep/(taller taller NCMS000 03632186:0.036042/03229145:0.022612/03632011:0.014390) [
//      ...
//      s-a-ms/adj-mod/(viejo viejo AQ0MS0 01579356:0.027715/01584395:0.013093/01580848:0.006361/01581218:0.006361/00631602:0.006234/00889260:0.006061/01629295:0.005864/01667095:0.005719)
//    ]
//  ]
//else if(preg_match("/sn\/obj\-prep\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]+s\-a\-[mf]s\/adj\-mod\/\(([^\s]+)\s([^\s]+)\s([^\s]+)/", $output,$matches)){
else if(preg_match("/sn\/[dobj\-presuatmnlch]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)\s\[[^\]]+[s\-amfs]+\/[adjsn]+\-mod\/\(([^\s]+)\s([^\s]+)\s([^\s]+)/", $output,$matches)){
	$form=$matches[1]. " " . $matches[4];
	$formo=$form ;
	$lem=$matches[2] . " " . $matches[5];
	$lemo = $lem;
	$pos=$matches[3] . " " . $matches[6];
	$poso = $pos;
	//print "4\n";
}
//grup-sp/sp-obj/(a a SPS00 -) [
//    sn/obj-prep/(baÃ±o baÃ±o NCMS000 00457130:0.018901/00165598:0.016510/02264436:0.016483/02461945:0.015773/00280204:0.015399/03512626:0.013624/02263808:0.012210/00280090:0.009953) [
//      ...
//    ]
//  ]


//AZKENA
else if(preg_match("/\[[\s]+sn\/obj\-prep\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)[\s]+\][\s]+sn\/subj\/\(([^\s]+)\s([^\s]+)\s([^\s]+)\s([^\s\)]+)\)/", $output,$matches)){
	$form=$matches[1] . " " . $matches[4];
	$formo=$form ;
	$lem=$matches[2] . " " . $matches[5];
	$lemo = $lem;
	$pos=$matches[3] . " " . $matches[6];
	$poso = $pos;
	//print "5\n";
}

// grup-sp/sp-obj/(a a SPS00 -) [
//    sn/obj-prep/(Lurra lurra NP00000 -)
//  ]
//  sn/subj/(1 1 Z -)


else if(preg_match("/sn\/obj\-prep\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)/", $output,$matches)){
	$form=$matches[1];
	$formo=$form ;
	$lem=$matches[2];
	$lemo = $lem;
	$pos=$matches[3];
	$poso = $pos;
	//print "5\n";
}



//sn/dobj/(taller taller NCMS000 03632186:0.036042/03229145:0.022612/03632011:0.014390) 

//sn/subj/(Roberto_OÃ±ate roberto_oÃ±ate NP00000 -) [
//    ...
//  ]
//else if(preg_match("/sn\/[dobj\-presuatmnlmnl]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)\s([0-9-]+)\)/", $output,$matches)){
else if(preg_match("/sn\/[dobj\-presuatmnlch]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[^\)]+\)/", $output,$matches)){
	$form=$matches[1];
	$formo=$form;
	$lem=$matches[2];
	$lemo = $lem;
	$pos=$matches[3];
	$poso = $pos;
	//print "6\n";
}

$origform=utf8_encode($form);

$form=utf8_encode($form);
//print $form;
$form=str_replace("ñ","in",$form);
$form=str_replace("Ñ","In",$form);
$form=str_replace("á","a",$form);
$form=str_replace("Á","A",$form);
$form=str_replace("é","e",$form);
$form=str_replace("É","e",$form);
$form=str_replace("í","i",$form);
$form=str_replace("Í","i",$form);
$form=str_replace("Ó","o",$form);
$form=str_replace("ó","o",$form);
$form=str_replace("ú","u",$form);
$form=str_replace("Ú","U",$form);
$form=str_replace("ü","u",$form);
$form=str_replace("Ü","u",$form);
$form=str_replace("_"," ",$form);
$form=str_replace(" de "," ",$form);
//print $form;

$lem=utf8_encode($lem);
$lem=str_replace("ñ","in",$lem);
$lem=str_replace("Ñ","In",$lem);
$lem=str_replace("á","a",$lem);
$lem=str_replace("Á","A",$lem);
$lem=str_replace("é","e",$lem);
$lem=str_replace("É","e",$lem);
$lem=str_replace("í","i",$lem);
$lem=str_replace("Í","i",$lem);
$lem=str_replace("Ó","o",$lem);
$lem=str_replace("ó","o",$lem);
$lem=str_replace("ú","u",$lem);
$lem=str_replace("Ú","U",$lem);
$lem=str_replace("ü","u",$lem);
$lem=str_replace("Ü","u",$lem);
$lem=str_replace("_"," ",$lem);
$lem=str_replace(" de "," ",$lem);

$o=array($origform,$lem,$pos);
//$o=array($form,$lem,$pos);
return $o;
}

function obtenerVerbo($output){

$form="";
$lem="";
$pos="";
$synset="";
if(preg_match("/grup\-verb/",$output,$matches)){

	//grup-verb/top/(Quiero querer VMIP1S0 01245362:0.009670/00808096:0.007240/01211759:0.007081/01530096:0.007007/00479719:0.006345/00479841:0.006265/00472243:0.005032) [
	//  subord-rel/modnomatch/(que que PR0CN000 -) [
	//    grup-verb/vsubord/(vaya ir VMM03S0

	//grup-verb/top/(saber saber VMN0000 00401762:0.040903/00402210:0.034257/00402992:0.024179) [
	//  VMIP1S0/dverb/(Quiero querer VMIP1S0 01245362:0.012888/00808096:0.009935/01211759:0.009440/01530096:0.009336/00479719:0.008460/00479841:0.008353/00472243:0.006709)
	//  subord-rel/modnomatch/(donde donde PR000000 -) [
	//    grup-verb/vsubord/(estÃ¡ estar VAIP3S0 01811792:0.037974/01867419:0.021588) [

	//subord-rel/top/(Donde donde PR000000 -) [
	//  grup-verb/vsubord/(estÃ¡ estar VAIP3S0 01811792:0.113861/01867419:0.064590) [

//grup-verb/top/(Quiero querer VMIP1S0 01245362:0.019324/00808096:0.014294/01211759:0.014157/01530096:0.013999/00479719:0.012689/00479841:0.012528/00472243:0.010063) [
//  subord/dobj/(que que CS -) [
//    grup-verb/vsubord/(guíes guiar VMSP2S0 01317898:0.042442/01364900:0.026844/01318647:0.024837/01738832:0.018510/01661609:0.014815) [


	if(preg_match("/subord[\-rel]*\/[modnatchpbj]+\/\([^\s]+\s[^\s]+\s[^\s]+[^\)]+\)\s\[[^\]]+grup\-verb\/vsubord\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[\s]+([0-9\:\.\-\/]+)[^\)]*\)/", $output,$matches)){
		$form=$matches[1];
		$formo=$form ;
		$lem=$matches[2];
		$lemo = $lem;
		$pos=$matches[3];
		$poso = $pos;
		$synset=$matches[4];
	}
	
	 //subord-ger/cc/(trayendo traer VMG0000 02077656-v:0.00791243/01629958-v:0.00763181/01629589-v:0.00736484/01433294-v:0.00720687/01432601-v:0.00700365/02080783-v:0.00681789) [
    //sn/dobj/(traspaleta traspaleta NCFS000 -) [
     // espec-fs/espec/(la el DA0FS0 -)
    //]
  //]

    else if(preg_match("/subord[\-a-zA-Z]*\/[a-zA-Z]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[\s]+([0-9\:\.\-\/]+)[^\)]*\)/", $output,$matches)){
		$form=$matches[1];
		$formo=$form ;
		$lem=$matches[2];
		$lemo = $lem;
		$pos=$matches[3];
		$poso = $pos;
		$synset=$matches[4];
	}
	// grup-verb-inf/dobj/(ir ir VMN0000 	01253107:0.006004/01331981:0.005896/01832891:0.005711/00236311:0.005604/01615757:0.005596/01371248:0.005225/01841936:0.004570/01781945:0.004379/01872318:0.003447/01833375:0.003205/01865057:0.002963) 
	//grup-verb-inf/obj-prep/(llegar llegar VMN0000 01368651:0.024011/01375966:0.009511/00236668:0.008606/01262658:0.008027/01369235:0.007749/01833691:0.007158/00721361:0.006284)
	else if(preg_match("/grup\-verb\-inf\/[obj\-pred]+\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[\s]+([0-9\:\.\-\/]+)[^\)]*\)/", $output,$matches)){
		$form=$matches[1];
		$formo=$form ;
		$lem=$matches[2];
		$lemo = $lem;
		$pos=$matches[3];
		$poso = $pos;
		$synset=$matches[4];
	}


	//grup-verb/top/(Vete ir VMM02S0 01253107:0.023722/01331981:0.023552/01832891:0.022829/00236311:0.022405/01615757:0.022371/01371248:0.020846/01841936:0.018280/01781945:0.017506/01872318:0.013780/01833375:0.012819/01865057:0.011849) 
	else if(preg_match("/grup\-verb[\-inf]*\/top\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[\s]+([0-9\:\.\-\/]+)[^\)]*\)/", $output,$matches)){
		$form=$matches[1];
		$formo=$form ;
		$lem=$matches[2];
		$lemo = $lem;
		$pos=$matches[3];
		$poso = $pos;
		$synset=$matches[4];
	}
}
else{
	//$form = "NV";
	//$lem = "NV";
	//$pos = "NV";
	//$synset="NV";
	$form = "";
	$lem = "";
	$pos = "";
	$synset="";
}
$topverbodeaccion=0;
$topsynset="";
$toplem="";

//Obtener el verbo principal
if(preg_match("/grup\-verb[\-a-zA-Z]*\/top\/\(([^\s]+)\s([^\s]+)\s([^\s]+)[\s]+([0-9\:\.\-\/]+)[^\)]*\)/", $output,$matches)){
	$topform=$matches[1];
	$toplem=$matches[2];
	$toppos=$matches[3];
	$topsynset=$matches[4];
}
else{
	//$topform="NTV";
	//$toplem="NTV";
	//$toppos="NTV";
	//$topsynset="NTV";
	$topform="";
	$toplem="";
	$toppos="";
	$topsynset="";
}

if(preg_match("/([0-9]+)\:/",$topsynset,$matches)){
	$topsynset=$matches[1];
}
if(preg_match("/([0-9]+)\:/",$synset,$matches)){
	$synset=$matches[1];
}


$o= $lem;
$to= $toplem;

$o=utf8_encode($o);
$o=str_replace("ñ","in",$o);
$o=str_replace("Ñ","In",$o);
$o=str_replace("á","a",$o);
$o=str_replace("Á","A",$o);
$o=str_replace("é","e",$o);
$o=str_replace("É","e",$o);
$o=str_replace("í","i",$o);
$o=str_replace("Í","i",$o);
$o=str_replace("Ó","o",$o);
$o=str_replace("ó","o",$o);
$o=str_replace("ú","u",$o);
$o=str_replace("Ú","U",$o);
$o=str_replace("ü","u",$o);
$o=str_replace("Ü","u",$o);
$o=str_replace("_"," ",$o);
$o = str_replace(" "," or ",$o);

$to=utf8_encode($to);
$to=str_replace("ñ","in",$to);
$to=str_replace("Ñ","In",$to);
$to=str_replace("á","a",$to);
$to=str_replace("Á","A",$to);
$to=str_replace("é","e",$to);
$to=str_replace("É","e",$to);
$to=str_replace("í","i",$to);
$to=str_replace("Í","i",$to);
$to=str_replace("Ó","o",$to);
$to=str_replace("ó","o",$to);
$to=str_replace("ú","u",$to);
$to=str_replace("Ú","U",$to);
$to=str_replace("ü","u",$to);
$to=str_replace("Ü","u",$to);
$to=str_replace("_"," ",$to);
$to = str_replace(" "," or ",$to);

$oo=array($to,$o);

return $oo;
}

function existe_accion_KB($v){
	$sparql=<<<EOT
prefix owl: <http://www.w3.org/2002/07/owl#> 
prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> 
prefix xml: <http://www.w3.org/XML/1998/namespace> 
prefix xsd: <http://www.w3.org/2001/XMLSchema#> 
prefix foaf: <http://xmlns.com/foaf/0.1/> 
prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
prefix rooms: <http://vocab.deri.ie/rooms#> 
prefix base: <http://tekniker.robotassistant.es#> 
PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
SELECT distinct ?s
WHERE {
{
?a a geo:TemporalThing .
?a a ?s .
?a foaf:name ?name .
?a rdfs:label ?label .
( ?label ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v}" 0.5 50 ) 
}
}
EOT;
	//print $sparql;
	
	$vpage=stardog_query($sparql);
	$vstr=preg_replace("/\<\?xml\-stylesheet[^\>]+\>\n/", "", $vpage);
	$vxmlDoc = new DOMDocument();
	$vxmlDoc->loadXML($vstr);
	//print $vstr;
	$vx = new DomXPath($vxmlDoc);

	$vx->registerNamespace("s", "http://www.w3.org/2005/sparql-results#");
	$vtipos = $vx->query("//s:result/s:binding[@name='s']/s:uri/text()");
	$tipopersona="";
	$tipoobjeto="";
	$tipoespacio="";
	//print "-------------------" . $vtipos->length . "\n";
	for ($counteri = 0; $counteri < $vtipos->length; $counteri += 1):
			$vtipo = trim(str_replace("\n",'', $vtipos->item($counteri)->textContent));
			if($vtipo == "http://tekniker.robotassistant.es#ActionOnPerson"){
				$tipopersona="1";
			}
			else if($vtipo == "http://tekniker.robotassistant.es#ActionOnObject"){
				$tipoobjeto="1";
			}
			else if($vtipo == "http://tekniker.robotassistant.es#ActionOnSpace"){
				$tipoespacio="1";
			}
	endfor;
	if($vtipos->length > 0 && $tipopersona=="" && $tipoobjeto=="" && $tipoespacio==""){
		$tipopersona=1;
		$tipoobjeto=1;
		$tipoespacio=1;
	}
	//print_r (array($tipopersona,$tipoobjeto,$tipoespacio)); 
	return array($tipopersona,$tipoobjeto,$tipoespacio);
}

function existe_accion_equivalente_KB_WordNet($v,$IP,$dest_tipos){
//Consultar en KB las wntag y obtener los que coincidan, incluyendo tipos.
//Preparar el output por los tipo indicado en el input.
	$outp="";
	$outo="";
	$outs="";
/*
	$sparql=<<<EOT
prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
prefix base: <http://tekniker.robotassistant.es#> 
PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX foaf:<http://xmlns.com/foaf/0.1/>
SELECT distinct ?action ?s
WHERE {
{
?a a geo:TemporalThing .
?a a ?s .
?a base:wntag ?name .
?a foaf:name ?action
VALUES ?name { "{$v}"@es}
}
}
EOT;*/
$sparql=<<<EOT
prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
prefix base: <http://tekniker.robotassistant.es#> 
PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX foaf:<http://xmlns.com/foaf/0.1/>
SELECT distinct ?action ?s
WHERE {
{
?a a geo:TemporalThing .
?a a ?s .
?a base:wntag ?name .
?a foaf:name ?action .
(?name ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v}" 0.5 50 ).
}
}
EOT;

//print $sparql;
	//print "accion_WN_sparql_s" . time() . "\n";
	//$vpage=curl_stardog_query($sparql);
	
	$vpage=stardog_query($sparql);

	//print "accion_WN_sparql_e" .time() . "\n";
	
	$vstr= $vpage;
	//print $vstr;
	$vxmlDoc = new DOMDocument();
	$vxmlDoc->loadXML($vstr);
	$vx = new DomXPath($vxmlDoc);
	$vx->registerNamespace("s", "http://www.w3.org/2005/sparql-results#");
	$vacciones = $vx->query("//s:result/s:binding[@name='s']/s:uri/../../s:binding[@name='action']/s:literal");
	$vtipos = $vx->query("//s:result/s:binding[@name='s']/s:uri");
	$accionesp="";
	$accioness="";
	$accioneso="";
	//print_r($vacciones);
	//print_r($vtipos);
	for ($counteri = 0; $counteri < $vacciones->length; $counteri += 1):
		$name = trim(str_replace("\n",'', $vacciones->item($counteri)->textContent));
		$vtipo = trim(str_replace("\n",'', $vtipos->item($counteri)->textContent));
		if($vtipo == "http://tekniker.robotassistant.es#ActionOnPerson" && $dest_tipos[0]!=""){
			if($outp==""){
				$outp= $name;
			}
			else{
				$outp= $outp . " or " . $name;
			}
		}
		else if($vtipo == "http://tekniker.robotassistant.es#ActionOnObject" && $dest_tipos[1]!=""){
			if($outp==""){
				$outo= $name;
			}
			else{
				$outo= $outo . " or " . $name;
			}
		}
		else if($vtipo == "http://tekniker.robotassistant.es#ActionOnSpace" && $dest_tipos[2]!=""){
			if($outs==""){
				$outs= $name;
			}
			else{
				$outs= $outs . " or " . $name;
			}
		}		
	endfor;
	if($vtipos->length > 0 && $outp=="" && $outo=="" && $outs==""){
		$outp= $name;
		$outo= $name;
		$outs= $name;
	}
	//print_r(array($outp,$outo,$outs));
	return array($outp,$outo,$outs);
}

function tipo_destino_KB($destl){
	if($destl!=""){
		$destlor = str_replace(" ","' or '",$destl);
		$destland = str_replace(" ","' and '",$destl);
		$destl = "('" . $destland . "')" . " or ('" . $destlor . "')";
	}
	//comprobar si el destino  existe y de que tipo es.
	
	$sparql=<<<EOT
PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rooms:<http://vocab.deri.ie/rooms#>
PREFIX dc:<http://purl.org/dc/elements/1.1/>
PREFIX foaf:<http://xmlns.com/foaf/0.1/>
PREFIX owl:<http://www.w3.org/2002/07/owl#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
PREFIX base: <http://tekniker.robotassistant.es#> 
select distinct ?s where {
?r foaf:name ?name .
?r rdfs:label ?label .
?r a ?s .
(?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
FILTER(sameTerm(?s,rooms:Room) || sameTerm(?s,foaf:Person) || sameTerm(?s,base:Object))
}
EOT;
	//print $sparql . "\n";
	$vpage=stardog_query($sparql);
	$vstr=$vpage;
	$vxmlDoc = new DOMDocument();
	$vxmlDoc->loadXML($vstr);
	
	$vx = new DomXPath($vxmlDoc);
	$vx->registerNamespace("s", "http://www.w3.org/2005/sparql-results#");
	
	$vtipos = $vx->query("//s:result/s:binding[@name='s']/s:uri/text()");
	$tipopersona="";
	$tipoobjeto="";
	$tipoespacio="";
	for ($counteri = 0; $counteri < $vtipos->length; $counteri += 1):
			$vtipo = trim(str_replace("\n",'', $vtipos->item($counteri)->textContent));
			if($vtipo == "http://xmlns.com/foaf/0.1/Person"){
				$tipopersona="1";
			}
			else if($vtipo == "http://tekniker.robotassistant.es#Object"){
				$tipoobjeto="1";
			}
			else if($vtipo == "http://vocab.deri.ie/rooms#Room"){
				$tipoespacio="1";
			}
	endfor;
	return array($tipopersona,$tipoobjeto,$tipoespacio);
}

function existe_destino_KB($destl,$tipos,$verbo,$wordnet,$zona,$dest_tipos){
	//print_r($tipos);
	
	if($wordnet==1){
		$v=$tipos;
	}
	else{
		$v=array($verbo,$verbo,$verbo);
	}
	
	if($destl!=""){
		$destlor = str_replace(" ","' or '",$destl);
		$destland = str_replace(" ","' and '",$destl);
		$destl = "('" . $destland . "')" . " or ('" . $destlor . "')";
	}
	//comprobar si el destino independientemente del verbo existe y de que tipo es.
	/*
	$sparql=<<<EOT
PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rooms:<http://vocab.deri.ie/rooms#>
PREFIX dc:<http://purl.org/dc/elements/1.1/>
PREFIX foaf:<http://xmlns.com/foaf/0.1/>
PREFIX owl:<http://www.w3.org/2002/07/owl#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
PREFIX base: <http://tekniker.robotassistant.es#> 
select distinct ?s where {
?r foaf:name ?name .
?r rdfs:label ?label .
?r a ?s .
(?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
FILTER(sameTerm(?s,rooms:Room) || sameTerm(?s,foaf:Person) || sameTerm(?s,base:Object))
}
EOT;
	//print $sparql;
	//print "dest_sparql1_s" .time() . "\n";
	$vpage=stardog_query($sparql);
	//print "dest_sparql1_e" .time() . "\n";
	$vstr=$vpage;
	$vxmlDoc = new DOMDocument();
	$vxmlDoc->loadXML($vstr);
	
	$vx = new DomXPath($vxmlDoc);
	$vx->registerNamespace("s", "http://www.w3.org/2005/sparql-results#");
	
	$vtipos = $vx->query("//s:result/s:binding[@name='s']/s:uri/text()");
	$tipopersona="";
	$tipoobjeto="";
	$tipoespacio="";
	for ($counteri = 0; $counteri < $vtipos->length; $counteri += 1):
			$vtipo = trim(str_replace("\n",'', $vtipos->item($counteri)->textContent));
			if($vtipo == "http://xmlns.com/foaf/0.1/Person"){
				$tipopersona="1";
			}
			else if($vtipo == "http://tekniker.robotassistant.es#Object"){
				$tipoobjeto="1";
			}
			else if($vtipo == "http://vocab.deri.ie/rooms#Room"){
				$tipoespacio="1";
			}
	endfor;
	*/
	$tipopersona=$dest_tipos[0];
	$tipoobjeto=$dest_tipos[1];
	$tipoespacio=$dest_tipos[2];
	if($tipos!=array("","","")){
		//print "\nHay restriccion tipo!\n";
		$sparqlhead=<<<EOT
PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rooms:<http://vocab.deri.ie/rooms#>
PREFIX dc:<http://purl.org/dc/elements/1.1/>
PREFIX foaf:<http://xmlns.com/foaf/0.1/>
PREFIX owl:<http://www.w3.org/2002/07/owl#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
PREFIX base: <http://tekniker.robotassistant.es#> 
select ?r ?x ?y ?a ?c ?name ?sc where {
EOT;
		$sparqltail="}";
		$sparqlunion="";
		if($tipos[0]!="" && $tipopersona!=""){
			$partialsparqlperson=<<<EOT
{?p a foaf:Person .
?r rooms:occupant ?p .
?r base:hasPoint ?po .
?p foaf:name ?name .
?p rdfs:label ?label .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ("{$zona}" 0.5 50 ).
?v base:isApplicableToaPerson ?p .
?v foaf:name ?vname .
( ?vname ?score1 ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v[0]}" 0.5 50 ).
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
			$sparqlunion=$partialsparqlperson;
		}
		if($tipos[1]!="" && $tipoobjeto!=""){
			$partialsparqlobject=<<<EOT
{
?r rooms:contains ?p . 
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
?p foaf:name ?name .
?p rdfs:label ?label .
?v base:isApplicableToanObject ?p .
?v foaf:name ?vname .
( ?vname ?score1 ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v[1]}" 0.5 50 ).
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
			if($sparqlunion==""){
				$sparqlunion=$partialsparqlobject;
			}
			else{
				$sparqlunion= $sparqlunion . "UNION" . $partialsparqlobject;
			}
		}
		if($tipos[2]!="" && $tipos[1]!="" && $tipoespacio!=""){
			$partialsparqlspace=<<<EOT
{?r a rooms:Room .
?r foaf:name ?name .
?r rdfs:label ?label .
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
?v base:isApplicableToaSpace ?r .
?v foaf:name ?vname .
( ?vname ?score1 ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v[1]}" 0.5 50 ).
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
			if($sparqlunion==""){
				$sparqlunion=$partialsparqlspace;
			}
			else{
				$sparqlunion= $sparqlunion . "UNION" . $partialsparqlspace;
			}
		}
		if($tipos[2]!="" && $tipos[1]==""  && $tipoespacio!=""){
			$partialsparqlspace=<<<EOT
{?r a rooms:Room .
?r foaf:name ?name .
?r rdfs:label ?label .
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
?v base:isApplicableToaSpace ?r .
?v foaf:name ?vname .
( ?vname ?score1 ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v[1]}" 0.5 50 ).
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
UNION 
{?r a rooms:Room .
?r rooms:contains ?rr .
?rr foaf:name ?name .
?rr rdfs:label ?label .
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
?v base:isApplicableToaSpace ?rr .
?v foaf:name ?vname .
( ?vname ?score1) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v[2]}" 0.5 50 ).
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
			if($sparqlunion==""){
				$sparqlunion=$partialsparqlspace;
			}
			else{
				$sparqlunion= $sparqlunion . "UNION" . $partialsparqlspace;
			}
		}
		$sparql=$sparqlhead . $sparqlunion . $sparqltail . "ORDER BY desc (?sc)";
	}
	else{
		$sparqlhead=<<<EOT
PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rooms:<http://vocab.deri.ie/rooms#>
PREFIX dc:<http://purl.org/dc/elements/1.1/>
PREFIX foaf:<http://xmlns.com/foaf/0.1/>
PREFIX owl:<http://www.w3.org/2002/07/owl#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
PREFIX base: <http://tekniker.robotassistant.es#> 
select ?r ?x ?y ?a ?c ?name ?sc where {
EOT;
		$sparqltail="}";
		$sparqlunion="";
		if($tipopersona!=""){
			$partialsparqlperson=<<<EOT
{?p a foaf:Person .
?r rooms:occupant ?p .
?r base:hasPoint ?po .
?p foaf:name ?name .
?p rdfs:label ?label .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ("{$zona}" 0.5 50 ).
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
			$sparqlunion=$partialsparqlperson;
		}
		if($tipoobjeto!=""){
			$partialsparqlobject=<<<EOT
{
?r rooms:contains ?p . 
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
?p foaf:name ?name .
?p rdfs:label ?label .
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
			if($sparqlunion==""){
				$sparqlunion=$partialsparqlobject;
			}
			else{
				$sparqlunion= $sparqlunion . "UNION" . $partialsparqlobject;
			}
		}
		if($tipoobjeto!="" && $tipoespacio!=""){
			$partialsparqlspace=<<<EOT
{?r a rooms:Room .
?r foaf:name ?name .
?r rdfs:label ?label .
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
			if($sparqlunion==""){
				$sparqlunion=$partialsparqlspace;
			}
			else{
				$sparqlunion= $sparqlunion . "UNION" . $partialsparqlspace;
			}
		}
		if($tipoobjeto==""  && $tipoespacio!=""){
			$partialsparqlspace=<<<EOT
{?r a rooms:Room .
?r foaf:name ?name .
?r rdfs:label ?label .
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
UNION 
{?r a rooms:Room .
?r rooms:contains ?rr .
?rr foaf:name ?name .
?rr rdfs:label ?label .
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
( ?label ?sc ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$destl}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
			if($sparqlunion==""){
				$sparqlunion=$partialsparqlspace;
			}
			else{
				$sparqlunion= $sparqlunion . "UNION" . $partialsparqlspace;
			}
		}
		$sparql=$sparqlhead . $sparqlunion . $sparqltail . "ORDER BY desc (?sc)";

	}
	//print $sparql ."\n";
	//print "dest_sparql2_s" . time() . "\n";
	//$time = round(microtime(true)*1000);
	//$vpage=curl_stardog_query($sparql);
	$vpage=stardog_query($sparql);
	//print "dest_sparql2_e" . time() . "\n";
	//$time = round(microtime(true)*1000) - $time;
	//print $time;
	$vstr=$vpage;
	//print $vstr;
	$vxmlDoc = new DOMDocument();
	$vxmlDoc->loadXML($vstr);
	
	$vx = new DomXPath($vxmlDoc);
	$vx->registerNamespace("s", "http://www.w3.org/2005/sparql-results#");
	
	
	$names = $vx->query("//s:result/s:binding[@name='r']");
		//$names = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='r']/");
	
	$outputnames = $vx->query("//s:result/s:binding[@name='name']/s:literal");

	$xs = $vx->query("//s:result/s:binding[@name='x']/s:literal");
		//$xs = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='x']/../@rdf:nodeID)]/res:value/text()");

	$ys = $vx->query("//s:result/s:binding[@name='y']/s:literal");
		//$ys = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='y']/../@rdf:nodeID)]/res:value/text()");

	$angles = $vx->query("//s:result/s:binding[@name='a']/s:literal");
		//$angles = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='a']/../@rdf:nodeID)]/res:value/text()");

	$scores = $vx->query("//s:result/s:binding[@name='sc']/s:literal");
		//$scores = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='sc']/../@rdf:nodeID)]/res:value/text()");
	
	//print_r($names);
	if($names->length==0){
		return "";
	}
	$printed{"a"} = "";
	$finaloutput="";
	for ($counteri = 0; $counteri < $names->length; $counteri += 1):
		$name = trim(str_replace("\n",'', $names->item($counteri)->textContent));
		$outputname = trim(str_replace("\n",'',$outputnames->item($counteri)->textContent));
		$x = trim(str_replace("\n",'',$xs->item($counteri)->textContent));
		$y = trim(str_replace("\n",'',$ys->item($counteri)->textContent));
		$angle = trim(str_replace("\n",'',$angles->item($counteri)->textContent));
		$score = trim(str_replace("\n",'',$scores->item($counteri)->textContent));
		$comment="-";
		$tc =  $counteri+1;
		$query = "//s:result[" . $tc . "]/s:binding[@name='c']/s:literal";
		//print $query;
		$comments = $vx->evaluate("count(" . $query . ")");
	
		if($comments>0){
			$comments = $vx->query($query);	
			//print $query;	
			$comment = trim(str_replace("\n",'',$comments->item(0)->textContent));
		}
		$outputname = preg_replace("/.*\/([a-zA-Z0-9]+)$/","$1",$outputname);
		$outputname = str_replace("iN","ñ",$outputname);
	    //print $outputname . "\n";
		
		//$output = utf8_decode($outputname) . "||" . $x . "||" . $y . "||" . $angle . "||" . $comment . "||" . $score . "@@@";
		//$outcomp = utf8_decode($outputname) . "||" . $x . "||" . $y . "||" . $angle . "||" . $comment;
		
		$output = $outputname . "||" . $x . "||" . $y . "||" . $angle . "||" . $comment . "||" . $score . "@@@";
		$outcomp = $outputname . "||" . $x . "||" . $y . "||" . $angle . "||" . $comment;
		
		if(!array_key_exists($outcomp, $printed)){
		//if($printed{$outcomp}!=1){
			$printed{$outcomp}=1;
			$finaloutput=$finaloutput . $output;
		}
	endfor;	
	//print $finaloutput;
	return $finaloutput; 
}

function existen_instancias_KB_con_verbo($tipos,$verbo,$wordnet,$zona){
	
	if($wordnet == 1){
		$v=$tipos;
	}
	else{
		$v=array($verbo,$verbo,$verbo);
	}
	$sparqlhead=<<<EOT
PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rooms:<http://vocab.deri.ie/rooms#>
PREFIX dc:<http://purl.org/dc/elements/1.1/>
PREFIX foaf:<http://xmlns.com/foaf/0.1/>
PREFIX owl:<http://www.w3.org/2002/07/owl#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
PREFIX base: <http://tekniker.robotassistant.es#> 
select ?r ?x ?y ?a ?c ?name ?sc where {
EOT;
	$sparqltail="}";
	$sparqlunion="";
	if($tipos[0]!=""){
		$partialsparqlperson=<<<EOT
{?p a foaf:Person .
?r rooms:occupant ?p . 
?p foaf:name ?name .
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
?v base:isApplicableToaPerson ?p .
?v foaf:name ?vname .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
( ?vname ?sc) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v[0]}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
		$sparqlunion=$partialsparqlperson;
	}
	if($tipos[1]!=""){
		$partialsparqlobject=<<<EOT
{
?r rooms:contains ?p . 
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
?p foaf:name ?name .
?v base:isApplicableToanObject ?p .
?v foaf:name ?vname .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
( ?vname ?sc) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v[1]}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
		if($sparqlunion==""){
			$sparqlunion=$partialsparqlobject;
		}
		else{
			$sparqlunion= $sparqlunion . "UNION" . $partialsparqlobject;
		}
	}
	if($tipos[2]!=""){
		$partialsparqlspace=<<<EOT
{?r a rooms:Room .
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
?r foaf:name ?name .
?v base:isApplicableToaSpace ?r .
?v foaf:name ?vname .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
( ?vname ?sc) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v[2]}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
UNION
{?r a rooms:Room .
?r rooms:contains ?rr .
?r base:hasPoint ?po .
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
?r foaf:name ?name .
?v base:isApplicableToaSpace ?rr .
?v foaf:name ?vname .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$zona}" 0.5 50 ).
( ?vname ?sc) <http://jena.hpl.hp.com/ARQ/property#textMatch> ( "{$v[2]}" 0.5 50 ).
OPTIONAL { ?po rdfs:comment ?c }
}
EOT;
		if($sparqlunion==""){
			$sparqlunion=$partialsparqlspace;
		}
		else{
			$sparqlunion= $sparqlunion . "UNION" . $partialsparqlspace;
		}
	}
	if($sparqlunion==""){
		return "";
	}
	$sparql=$sparqlhead . $sparqlunion . $sparqltail;
	
	//print $sparql;
	
	$vpage=stardog_query($sparql);
	$vstr=$vpage;
	$vxmlDoc = new DOMDocument();
	$vxmlDoc->loadXML($vstr);
	
	$vx = new DomXPath($vxmlDoc);
	$vx->registerNamespace("s", "http://www.w3.org/2005/sparql-results#");
	
	
	$names = $vx->query("//s:result/s:binding[@name='r']");
		//$names = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='r']/");

	$outputnames = $vx->query("//s:result/s:binding[@name='name']/s:literal");
	$xs = $vx->query("//s:result/s:binding[@name='x']/s:literal");
		//$xs = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='x']/../@rdf:nodeID)]/res:value/text()");

	$ys = $vx->query("//s:result/s:binding[@name='y']/s:literal");
		//$ys = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='y']/../@rdf:nodeID)]/res:value/text()");

	$angles = $vx->query("//s:result/s:binding[@name='a']/s:literal");
		//$angles = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='a']/../@rdf:nodeID)]/res:value/text()");

	$scores = $vx->query("//s:result/s:binding[@name='sc']/s:literal");
		//$scores = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='sc']/../@rdf:nodeID)]/res:value/text()");
	
	if($names->length==0){
		return "";
	}
	$finaloutput="";
	$printed{"a"} = "";
	for ($counteri = 0; $counteri < $names->length; $counteri += 1):
		$name = trim(str_replace("\n",'', $names->item($counteri)->textContent));
		$outputname = trim(str_replace("\n",'',$outputnames->item($counteri)->textContent));
		$x = trim(str_replace("\n",'',$xs->item($counteri)->textContent));
		$y = trim(str_replace("\n",'',$ys->item($counteri)->textContent));
		$angle = trim(str_replace("\n",'',$angles->item($counteri)->textContent));
		$score = trim(str_replace("\n",'',$scores->item($counteri)->textContent));
		$comment="-";
		$tc =  $counteri+1;
		$query = "//s:result[" . $tc . "]/s:binding[@name='c']/s:literal";
		//print $query;
		$comments = $vx->evaluate("count(" . $query . ")");
	
		if($comments>0){
			$comments = $vx->query($query);	
			//print $query;	
			$comment = trim(str_replace("\n",'',$comments->item(0)->textContent));
		}
		$outputname = preg_replace("/.*\/([a-zA-Z0-9]+)$/","$1",$outputname);
		$outputname = str_replace("iN","ñ",$outputname);
	
		//$output = utf8_decode($outputname) . "||" . $x . "||" . $y . "||" . $angle . "||" . $comment . "||" . $score . "@@@";
		//$outcomp = utf8_decode($outputname) . "||" . $x . "||" . $y . "||" . $angle . "||" . $comment;
		
		$output = $outputname . "||" . $x . "||" . $y . "||" . $angle . "||" . $comment . "||" . $score . "@@@";
		$outcomp = $outputname . "||" . $x . "||" . $y . "||" . $angle . "||" . $comment;
		
		if(!array_key_exists($outcomp, $printed)){
		//if($printed{$outcomp}!=1){
			$printed{$outcomp}=1;
			$finaloutput=$finaloutput . $output;
		}
	endfor;	
	//print $finaloutput;
	return $finaloutput;
}

function seleccionarelprimerDestino($dlista){
	$nummaxopciones=2;
	$textoOpcionesMaxSuperadas=array("Vaya, vaya! Me vienen demasiadas ideas a la cabeza. ¿Podrías reformular la petición y concretar un poco más?","Caramba! La cantidad de posibilidades que responden a tu petición me abruman. ¿Podrías concretar un poco más?");
	$textopt=sizeof($textoOpcionesMaxSuperadas)-1;
	$superado=FALSE;
	if($dlista != ""){
		$destinos=explode("@@@",$dlista);
		$d1=explode("||",$destinos[0]);
		$d1uri=$d1[0];
		$dcompleto=$d1uri;
		$d1x=$d1[1];
		$d1y=$d1[2];
		$d1a=$d1[3];
		$d1c=$d1[4];
		$d1score=$d1[5];
		//$d1score=$d1[3];

		$destinos[0]="";
	
	//print $d1uri;
	
		$i=1;
		$j=0;
		$out="";
		while($i<sizeof($destinos)){
		  $d=explode("||",$destinos[$i]);
		  if(sizeof($d)>1){	
			$duri=$d[0];
			$dx=$d[1];
			$dy=$d[2];
			$da=$d[3];
			$dc=$d[4];
			$dscore=$d[5];
			//print $destinos[$i] . "\n";
			if($dx==$d1x && $dy==$d1y && $da==$d1a){	
				//print "coord \n";
				if($duri != $d1uri && $dscore == $d1score){
					//print "uri \n";
					if($i<$nummaxopciones){
						$dcompleto = $dcompleto	. " o " . $duri;
					}
					else{
						$i=sizeof($destinos)+1;
						$superado=TRUE;
						$dcompleto = "";
					}
				}
				//Nuevo 
				else if($dscore!=$d1score){
					//print $destinos[$i] ."\n";
					$out[$j]=$destinos[$i];
					$j++;
				}
				$i++;
			}
			else{
				$out[$j]=$destinos[$i];
				$i++;
				$j++;
			}
	  	  }
		  else{
	  		$i++;
		  }
		}
		if($out !=""){
			$outdestinos = join("@@@",$out);
		}
		else{
			$outdestinos="";
		}
		$destinoseleccionado["nombre"]=$dcompleto;
		$destinoseleccionado["x"]=$d1x;
		$destinoseleccionado["y"]=$d1y;
		$destinoseleccionado["a"]=$d1a;
		$destinoseleccionado["comentario"]=$d1c;
		//$destinoseleccionado= $dcompleto . "\t" . $d1x . "\t" .$d1y . "\t". $d1a . "\t" . $d1c;
		if($superado==TRUE){
			$destinoseleccionado["nombre"]="";
			$destinoseleccionado["x"]="";
			$destinoseleccionado["y"]="";
			$destinoseleccionado["a"]="";
			$destinoseleccionado["comentario"]=$textoOpcionesMaxSuperadas[rand(0,$textopt)];
			$outdestinos="1";
		}
	}
	else{
		$destinoseleccionado["nombre"]="";
		$destinoseleccionado["x"]="";
		$destinoseleccionado["y"]="";
		$destinoseleccionado["a"]="";
		$destinoseleccionado["comentario"]="";
		$outdestinos="";
	}
	return array($destinoseleccionado,$outdestinos);
}

function eliminar_tildes($texto){
	$o=$texto;
	$o=str_replace("á","a",$o);
	$o=str_replace("Á","A",$o);
	$o=str_replace("é","e",$o);
	$o=str_replace("É","e",$o);
	$o=str_replace("í","i",$o);
	$o=str_replace("Í","i",$o);
	$o=str_replace("Ó","o",$o);
	$o=str_replace("ó","o",$o);
	$o=str_replace("ú","u",$o);
	$o=str_replace("Ú","U",$o);
	$o=str_replace("ü","u",$o);
	$o=str_replace("Ü","u",$o);
	$o=str_replace("_"," ",$o);
	return $o;
}
function cumple_numero_opciones_maximas ($opciones,$nummax){
		$destinos=explode("@@@",$opciones);
		$d1=explode("||",$destinos[0]);
		$d1uri=$d1[0];
		$dcompleto=$d1uri;
		$d1x=$d1[1];
		$d1y=$d1[2];
		$d1a=$d1[3];
		$d1c=$d1[4];
		$d1score=$d1[5];
		$i=1;
		while($i<sizeof($destinos)){
		  $d=explode("||",$destinos[$i]);
		  if(sizeof($d)>1){	
			$duri=$d[0];
			$dx=$d[1];
			$dy=$d[2];
			$da=$d[3];
			$dc=$d[4];
			$dscore=$d[5];
			if($dx==$d1x && $dy==$d1y && $da==$d1a){
				$i++;	
				//print $dscore . "\t" . $d1score . "\n";
				if($duri != $d1uri && $dscore == $d1score){
					if($i<$nummax){
						$dcompleto = $dcompleto	. " o " . $duri;
					}
					else{
						return FALSE;					
					}
				}
			}
			else{
				return TRUE;
			}
	  	  }
		  else{
	  		return TRUE;
		  }
		}
		return TRUE;
}
function stardog_query ($query){
	//$urli="http://10.1.22.105:5820/annex/Ler2014/sparql/query?query=";
	//$urle="&reasoning=SL";
	//$urli="http://10.1.22.105:5820/annex/Ler2014Inf/sparql/query?query=";
	//$urli="http://10.0.0.174:5820/annex/Ler2014Inf/sparql/query?query=";
	$urli="http://localhost:5820/annex/LER2016/sparql/query?query=";
	$urle="";
	$url=$urli . urlencode($query) . $urle;
    //print $query;
	$response=http_get_stardog_query($url);
	return $response;
}
function http_get_stardog_query($url){
// Create a stream
$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Authorization: Basic YWRtaW46YWRtaW4=\r\n" .
			  "Content-Type: application/x-www-form-urlencoded" .
              "Accept: application/sparql-results+xml\r\n"
  )
);

$context = stream_context_create($opts);

// Open the file using the HTTP headers set above
$file = file_get_contents($url, false, $context);
return $file;
}

function curl_stardog_query ($query){
	//open connection
	$ch = curl_init();
	//set options
	$stardogurl = "http://localhost:5820";

	$kb = "LER2016";
	$url=$stardogurl . "/annex/". $kb ."/sparql/query";
	$headers = array(
            "Authorization: Basic YWRtaW46YWRtaW4=" ,
                                               "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/sparql-results+xml" );
  
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
// Apply the XML to our curl call
	curl_setopt($ch, CURLOPT_POST, 1);
	$data="query=".urlencode($query);
	//print $data;
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	//execute post
	$result = curl_exec($ch);
	//close connection
	curl_close($ch);
	//print $result . "\n";
	return $result;
}


function obtenerDestinosDeZonaAleatorios($zona) {
	$numMaxPuntosAleatoriosResult = 4;
	$zona=str_replace(" "," and ",$zona);
	$sparql=<<<EOT
prefix owl: <http://www.w3.org/2002/07/owl#> 
prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> 
prefix xml: <http://www.w3.org/XML/1998/namespace> 
prefix xsd: <http://www.w3.org/2001/XMLSchema#> 
prefix foaf: <http://xmlns.com/foaf/0.1/> 
prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
prefix rooms: <http://vocab.deri.ie/rooms#> 
prefix base: <http://tekniker.robotassistant.es#> 
PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
SELECT distinct ?x ?y ?a
WHERE {
?po geo:lat ?x .
?po geo:long ?y .
?po base:angle ?a .
?po base:from ?zo .
?zo rdfs:label ?zolabel .
( ?zolabel ?score ) <http://jena.hpl.hp.com/ARQ/property#textMatch> ("{$zona}" 0.5 50 ).
}
EOT;
	//print $sparql;
	
	$vpage=stardog_query($sparql);
	$vstr=preg_replace("/\<\?xml\-stylesheet[^\>]+\>\n/", "", $vpage);
	//print $vstr;
	$vxmlDoc = new DOMDocument();
	$vxmlDoc->loadXML($vstr);

	$vx = new DomXPath($vxmlDoc);

	$vx->registerNamespace("s", "http://www.w3.org/2005/sparql-results#");
	
	
	$vx->registerNamespace("s", "http://www.w3.org/2005/sparql-results#");
	
	$xs = $vx->query("//s:result/s:binding[@name='x']/s:literal");
		//$xs = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='x']/../@rdf:nodeID)]/res:value/text()");

	$ys = $vx->query("//s:result/s:binding[@name='y']/s:literal");
		//$ys = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='y']/../@rdf:nodeID)]/res:value/text()");

	$angles = $vx->query("//s:result/s:binding[@name='a']/s:literal");
		//$angles = $xpath->query("//res:binding[@rdf:nodeID=(//res:binding/res:variable[text()='a']/../@rdf:nodeID)]/res:value/text()");
	
	if($numMaxPuntosAleatoriosResult>$xs->length){
		$numMaxPuntosAleatoriosResult = $xs->length;
	}
	
	$minrand = 0;
	$maxrand = $xs->length - 1;
	$result =array();
	$controlaleatory=array();
	for ($counteri = 0; $counteri < $numMaxPuntosAleatoriosResult; $counteri += 1):
		$elemind = rand($minrand, $maxrand);
		while(array_key_exists($elemind,$controlaleatory)){
			$elemind = rand($minrand, $maxrand);
		}
		//print $elemind;
		$controlaleatory[$elemind]=1;
		$x = trim(str_replace("\n",'',$xs->item($elemind)->textContent));
		$y = trim(str_replace("\n",'',$ys->item($elemind)->textContent));
		$angle = trim(str_replace("\n",'',$angles->item($elemind)->textContent));
		$r["x"]=$x;
		$r["y"]=$y;
		$r["a"]=$angle;
		$result[$counteri]=$r;
	endfor;
	return $result;
}


function voiceProcessing($audiofile){
/*	$curlcommand=<<<EOT
	"curl -w "@data/curl-format.txt" -s  -X POST --data-binary @{$audiofile} --header 'Content-type: audio/l16;rate=44100;' 'https://www.google.com/speech-api/v2/recognize?output=json&lang=es-es&key=AIzaSyC7-hXZya06d94hscA1tHlW6xdVXovODng'";
EOT;
* 
* 
*/

$ptime=microtime(true);
$url = 'https://www.google.com/speech-api/v2/recognize?output=json&lang=es-es&key=AIzaSyC7-hXZya06d94hscA1tHlW6xdVXovODng';
$fields = array('file' => "@".realpath($audiofile));
//print_r($fields);

//open connection
$ch = curl_init();

//set options 
//curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: audio/l16;rate=44100"));
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: audio/x-flac;rate=44100"));
//curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: audio/l16;rate=16000"));
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //needed so that the $result=curl_exec() output is the file and isn't just true/false

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);
//print $result;
$result=str_replace('{"result":[]}',"",$result);
$output=json_decode(trim($result));
//print $output["result"][0]["alternative"][0]["transcript"] . "\n";
//print $output->result->{'0'}->alternative->{'0'}->transcript . "\n";
$text=$output->result[0]->alternative[0]->transcript;
$confidence=$output->result[0]->alternative[0]->confidence;

$ptime=microtime(true) - $ptime;
//print $ptime . "\n";
print $text . "\t" . $confidence . "\n";
$nluout=interpretarComando($text,"zona hall");
return $nluout;
}

?>
