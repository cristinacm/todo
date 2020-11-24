#!/usr/bin/python3
import datetime
import json
import requests
import re
import call_virtuoso as virtuoso


#TODO: corregir queries
#TODO: mirar lo de global inference_graph (?)

# def sub_newline(freeling_tree):
	# freeling_tree_new = re.sub("\n", "@@@@", freeling_tree)
	# return(freeling_tree_new)

def call_freeling(user_request): #DONE-TESTED
	#TODO: llamar freeling
	freeling_URL = "http://10.0.0.194/FreelingServices/Freeling4"
	params_FL = {"text":user_request, "port":"30020"}
	freeling_tree = requests.post(freeling_URL, json.dumps(params_FL)).text
	# freeling_tree_json = json.loads(sub_newline(freeling_tree))
	freeling_tree_json = json.loads(freeling_tree)
	return(freeling_tree_json)

def call_KEE(freeling_tree_json, user_request):
	#TODO: lanzar request al KEE (falta servicio SRV)
	kee_URL = "http://10.0.0.131:8087/interpretar_peticion_GMAO"
	config = "GMAOES_config.xml"
	cluster = "0"
	params_kee = {"text":freeling_tree_json["analysis"], "command":user_request, "config":config, "cluster":cluster}
	#print(params_kee)
	# print(type(json.loads(params_kee)))

	key_elements = requests.post(kee_URL, json=params_kee)
	key_elements.encoding = "utf-8"
	#print(key_elements.text)
	return(json.loads(key_elements.text))


print(call_KEE("quiero ver el despiece de la máquina"))


graph_tesis = "urn:GuideRobotDialogKB"
graph_ALKA = "urn:ALKAdata"

inference_graph = "DEFINE input:inference '{graph}' ".format(graph=graph_ALKA)
header_alka = """
prefix dialSystemOnt: <http://www.semanticweb.org/linuxsii/ontologies/2020/6/DialSystemONT#>
prefix dialSystemOntInst: <http://www.semanticweb.org/linuxsii/ontologies/2020/6/DialSystemONT-inst#>
prefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#>
prefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#>
prefix domainOntWorld: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-World#>
prefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#>
prefix dialogueOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DialogueONT#>
prefix dialogueOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DialogueONT-inst#>"""

# header_alka = re.sub("\n", " ", header_alka)
# print(header_alka)

header_tesis = """
prefix dialSystemOnt: <http://www.semanticweb.org/linuxsii/ontologies/2020/6/DialSystemONT#>
prefix dialSystemOntInst: <http://www.semanticweb.org/linuxsii/ontologies/2020/6/DialSystemONT-inst#>
prefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#>
prefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#>
prefix domainOntWorld: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-World#>
prefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst#>
prefix dialogueOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DialogueONT#>
prefix dialogueOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DialogueONT-inst#>"""

def remove_newline_tab(text): #DONE-TESTED
	text = re.sub("\n", " ", text)
	text = re.sub("\t", "", text)
	return(text)

def create_str_json(query): #DONE-TESTED
	global inference_graph
	query_construct = '{"query":"'+inference_graph+query+'"}'
	query_new = remove_newline_tab(query_construct)
	return(query_new)


def insertDial(graph, header): #DONE-NOT TESTED
	# global header_alka

	identif_datetime = datetime.datetime.now().strftime("%Y%m%d%H%M")

	dial_identifier = "dial_"+identif_datetime

	insert_dial_query_template = """
	insert in <{graph}>{{
		dialSystemOntInst:{dial_identifier} a dialogueOnt:Dialogue .
		dialSystemOntInst:{dial_identifier} dialogueOnt:initDateTime '{identif_datetime}' .
		dialSystemOntInst:{dial_identifier} dialogueOnt:dialStatus 'open' .
		}}
	"""
	# insert_dial_query_template = re.sub("\n\t\t", "", insert_dial_query_template)
	
	insert_dial_query = header+insert_dial_query_template.format(graph=graph, dial_identifier=dial_identifier, identif_datetime=identif_datetime)
	
	query = create_str_json(insert_dial_query)
	# print(query)
	#query = {"query":"DEFINE input:inference 'urn:ALKAdata'  prefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#> prefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#> prefix domainOntWorld: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-World#> prefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#> insert in <urn:ALKAdata>{ dialSystemOntInst:dial_202010221724 a dialogueOnt:Dialogue . dialSystemOntInst:dial_202010221724 dialogueOnt:initDateTime '202010221724' . dialSystemOntInst:dial_202010221724 dialogueOnt:dialStatus 'open' . } "}
	return(query)
	#TODO: virtuoso_request = virtuoso.request_insert(json.loads(query))
	#TODO: return(virtuoso_request) 
	#TODO: return dial_identifier
# print(insertDial(graph_ALKA, header_alka))



# print(insertDial(graph_ALKA, header_alka))
	

def insertMD(graph, header, user_request, dial): #DONE-NOT TESTED ~ REVISE
	identif_datetime = datetime.datetime.now().strftime("%Y%m%d%H%M")

	mainDial_identifier = "mainDial_"+identif_datetime
	userRequest_identifier = "userRequest_"+identif_datetime

	insert_mdial_query_template = """
	insert in <{graph}>{{
		dialSystemOntInst:{maindial_identifier} a dialogueOnt:MainDialogue .
		dialSystemOntInst:{maindial_identifier} dialogueOnt:initDateTime '{identif_datetime}' .
		dialSystemOntInst:{dialogue} dialogueOnt:hasMainDialogue {maindial_identifier} .
		dialSystemOntInst:{userrequest_identifier} a dialogueOnt:UserRequest .
		dialSystemOntInst:{userrequest_identifier} dialogueOnt:text '{UserRequestTranscription}' .
		dialSystemOntInst:{maindial_identifier} dialogueOnt:hasUserRequest dialSystemOntInst:{userrequest_identifier} .
		}}"""

	insert_mdial_query = insert_mdial_query_template.format(graph=graph, maindial_identifier=mainDial_identifier, dialogue=dial,  userrequest_identifier=userRequest_identifier, identif_datetime=identif_datetime, UserRequestTranscription=user_request)
	
	query = create_str_json(insert_mdial_query)
	# print(query)
	# query = {"query":"DEFINE input:inference 'urn:ALKAdata'  insert in <urn:ALKAdata>{ dialSystemOntInst:mainDial_202010221715 a dialogueOnt:MainDialogue . dialSystemOntInst:mainDial_202010221715 dialogueOnt:initDateTime '202010221715' . dialSystemOntInst:Dial_202010221710 dialogueOnt:hasMainDialogue mainDial_202010221715 . dialSystemOntInst:userRequest_202010221715 a dialogueOnt:UserRequest . dialSystemOntInst:userRequest_202010221715 dialogueOnt:text 'Muéstrame el plano' . dialSystemOntInst:mainDial_202010221715 dialogueOnt:hasUserRequest dialSystemOntInst:userRequest_202010221715 . }"}

	# TODO: virtuoso_request = virtuoso.request_insert(json.loads(query))
	# TODO: return(virtuoso_request)
	# TODO: return maindial_identifier, userrequest_identifier

# print(insertMD(graph_ALKA, header_alka, "Muéstrame el plano", "Dial_202010221710"))


def insertSD(graph, header, dial, maindial, sysRequest, usrResponse): #DONE-NOT TESTED
	identif_datetime = datetime.datetime.now().strftime("%Y%m%d%H%M")

	secDial_identifier = "secDial_"+identif_datetime
	sysRequest_identifier = "sysRequest_"+identif_datetime
	usrResponse_identifier = "usrResponse_"+identif_datetime

	insert_sdial_template = """
	insert in <{graph}>{{
		dialSystemOntInst:{secDial_identifier} a dialogueOnt:SecondaryDialogue .
		dialSystemOntInst:{sysRequest_identifier} a dialogueOnt:SystemRequest .
		dialSystemOntInst:{usrResponse_identifier} a dialogueOnt:UserResponse .
		dialSystemOntInst:{secDial_identifier} dialogueOnt:initDateTime {{"{identif_datetime}"}} .
		dialSystemOntInst:{sysRequest_identifier} dialogueOnt:text {{"{SystemRequestTranscription}"}} .
		dialSystemOntInst:{secDial_identifier} dialogueOnt:hasSystemRequest dialSystemOntInst:{sysRequest_identifier} .
		dialSystemOntInst:{usrResponse_identifier} dialogueOnt:text {{"{UserResponseTranscription}"}} .
		dialSystemOntInst:{secDial_identifier} dialogueOnt:hasUserResponse dialSystemOntInst:{usrResponse_identifier} .
		}}	"""
	insert_sdial_query = insert_sdial_template.format(graph=graph, secDial_identifier=secDial_identifier, sysRequest_identifier=sysRequest_identifier, usrResponse_identifier=usrResponse_identifier, identif_datetime=identif_datetime, SystemRequestTranscription=sysRequest, UserResponseTranscription=usrResponse)

	query = create_str_json(insert_sdial_query)
	# print(query)
	#query = {"query":"DEFINE input:inference 'urn:ALKAdata'  insert in <urn:ALKAdata>{ dialSystemOntInst:secDial_202010221719 a dialogueOnt:SecondaryDialogue . dialSystemOntInst:sysRequest_202010221719 a dialogueOnt:SystemRequest . dialSystemOntInst:usrResponse_202010221719 a dialogueOnt:UserResponse . dialSystemOntInst:secDial_202010221719 dialogueOnt:initDateTime {"202010221719"} . dialSystemOntInst:sysRequest_202010221719 dialogueOnt:text {"sysRequest"} . dialSystemOntInst:secDial_202010221719 dialogueOnt:hasSystemRequest dialSystemOntInst:sysRequest_202010221719 . dialSystemOntInst:usrResponse_202010221719 dialogueOnt:text {"usrResponse"} . dialSystemOntInst:secDial_202010221719 dialogueOnt:hasUserResponse dialSystemOntInst:usrResponse_202010221719 . }"}

	#TODO: virtuoso_request = virtuoso.request_insert(json.loads(query))
	#TODO: return(virtuoso_request)
	#TODO: return SecDial identifier + UserResponse identifier  

# print(insertSD(graph_ALKA, header_alka, "dial", "maindial", "sysRequest", "usrResponse"))


def getActionQuery(graph, header, KeyElements): #DONE-TESTED
	#coger frame y skill?
	global inference_graph
	sparql_kee_intent_action_template = """
	select distinct ?intent ?action ?actionidval
	FROM <{graph}>	
	where {{
		?intent a domainOntFrame:Intent. ?intent domainOntFrame:hasFrame ?frame.
		?frame domainOntFrame:hasFrameHead ?framehead .
		?framehead domainOnt:hasLexicalUnit ?lu .
		?lu domainOnt:IDval ?luval .
		VALUES ?luval {{'{verb_LU}'}}
		?intent domainOntFrame:belongsToAction ?action .
		?action domainOnt:IDval ?actionidval .
		}}"""
	
	# 	KeyElements = KElems = """{
	#     "elements": [
	#         {
	#             "objeto": {
	#                 "item": [
	#                     "resolución.n",
	#                     "problema.n"
	#                 ],
	#                 "target": [
	#                     "procedimiento.n"
	#                 ]
	#             },
	#             "verbo": [
	#                 "mostrar.V"
	#             ]
	#         }
	#     ]
	# }"""
	kee_verb = KeyElements["elements"][0]["verbo"][0]
	# print(kee_verb)

	##kee_ST = KEE_output["elements"][0]["destino"][0]["target"]
	##kee_ST_prep = KEE_output["elements"][0]["destino"][0]["prep"]
	##kee_ST_compl = KEE_output["elements"][0]["destino"][0]["compl"] #list!

	sparql_kee_intent_action = header+sparql_kee_intent_action_template.format(graph=graph, verb_LU=kee_verb)

	query = create_str_json(sparql_kee_intent_action)
	# print(query)

	virtuoso_request = virtuoso.request_select(json.loads(query))
	#out = [{'intent': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#RequestInformation', 'action': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#GMAO_InformationRequest_drawing', 'actionidval': 'GMAO_InformationRequest_drawing'}, {'intent': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#RequestInformation', 'action': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#GMAO_Information_Request_despiece', 'actionidval': 'GMAO_Information_Request_despiece'}]
	return(virtuoso_request)

def getActionArgs(graph, header, action): #DONE-TESTED
	#TODO: separar CoreArg de OptionalArg y filtrar hasArgument
	global inference_graph
	sparql_action_args_template = """
	select ?argument ?relation ?type
	FROM <{graph}>
	where {{
		?argument a domainOntFrame:Argument .
		<{action}> domainOntFrame:hasArgument ?argument .
		<{action}> ?relation ?argument .
		?argument domainOnt:hasType ?t .
		?t domainOnt:IDval ?type.
		}}"""

	#example: {"query": "DEFINE input:inference 'urn:ALKAdata' prefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#> prefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#> prefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#> prefix rdfs: <http://www.w3.org/2000/01/rd-schema#> prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> select distinct ?argument ?relation ?type where {?argument a domainOntFrame:Argument .domainOntInst:GMAO_Information_Request_despiece domainOntFrame:hasArgument ?argument .domainOntInst:GMAO_Information_Request_despiece ?relation ?argument .?argument domainOnt:hasType ?t .?t domainOnt:IDval ?type.}"}

	sparql_action_args = header+sparql_action_args_template.format(graph=graph, action=action)
	

	# print(sparql_action_args)
	# query = '{"query":"'+inference_graph+sparql_action_args+'"}'
	query = create_str_json(sparql_action_args)

	virtuoso_request = virtuoso.request_select(json.loads(query))
	#out = [{'argument': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#InformationRequest_TargetItem_despiece', 'relation': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#hasArgument', 'type': 'targetitem'}, {'argument': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#InformationRequest_TargetItem_despiece', 'relation': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#hasOptionalArgument', 'type': 'targetitem'}]
	return(virtuoso_request)

# def argumentAssign(args, keyElements, action):

def getArgsListforQuery(header, args, actionTrace, mainDial_identifier): 
	#TODO: Meter aquí el hasValue después de la asignación
	identif_datetime = datetime.datetime.now().strftime("%Y%m%d%H%M")

	

	# arg_template_core = "dialSystemOntInst:{ActionTrace} domainOntFrame:hasCoreArgument dialSystemOntInst:{ArgTrace} ."
	# arg_template_optional = "dialSystemOntInst:{ActionTrace} domainOntFrame:hasOptionalArgument dialSystemOntInst:{ArgTrace} ."

	
	
	arg_query_out_list = []
	# print(args)

	#este bloque es si se hace distinción entre core y optional (sin relación en query para consultar args)
	# for argument in args:
	# 	# argument_trace = argument+maindial+date #TODO: hay que meter el identif del MainDial (o Dial) aquí
	# 	argclass_query = "dialSystemOntInst:{ArgTrace} a dialSystemOntInst:ArgumentTrace . ".format(graph=graph, ArgTrace=argument)
	# 	if arg == "core":
	# 		arg_formatted = arg_template_core.format(graph=graph, ArgTrace=argument)
	# 	if arg == "optional":
	# 		arg_formatted = arg_template_optional.format(graph=graph, ArgTrace=argument)
	# 	arg_query_out_list.append(arg_formatted)
	
	#este bloque es si se obtiene relación en la query para consultar args:
	for argument in args:
		# print(argument)
		# argument_trace = argument+maindial+date
		# #TODO: hay que meter el identif del MainDial (o Dial) aquí

		argval = argument["argument"].split("#")[1]
		# print(argval)
		relation = argument["relation"]

		ArgTrace = "argval_"+mainDial_identifier+"_"+identif_datetime
		

		all_args_template = """
		dialSystemOntInst:{ArgTrace} a domainOnt:ArgumentTrace . 
		dialSystemOntInst:{actionTrace} <{relation}> dialSystemOntInst:{ArgTrace} .
		"""
		
		all_args = all_args_template.format(ArgTrace=ArgTrace, actionTrace=actionTrace, relation=relation)
		# print(all_args)

		arg_query_out_list.append(all_args)



	args_query = " ".join(arg_query_out_list)
	# print(args_query)

	# #out=dialSystemOntInst:argval_MainDial23102020_202010230856 a dialSystemOntInst:ArgumentTrace . 
	# 	dialSystemOntInst:ActionTrace http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#hasArgument dialSystemOntInst:argval_MainDial23102020_202010230856 .
		 
	# 	dialSystemOntInst:argval_MainDial23102020_202010230856 a dialSystemOntInst:ArgumentTrace . 
	# 	dialSystemOntInst:ActionTrace http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#hasOptionalArgument dialSystemOntInst:argval_MainDial23102020_202010230856 .
	return args_query

args = [{'argument': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#InformationRequest_TargetItem_despiece', 'relation': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#hasArgument', 'type': 'targetitem'}, {'argument': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#InformationRequest_TargetItem_despiece', 'relation': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#hasOptionalArgument', 'type': 'targetitem'}]

# print(getArgsListforQuery(header_alka, args, "ActionTrace", "MainDial23102020"))

def insertTraces(graph, header, skill, frame, intent, action, actionidval, getActionArgs_response, userrequest_identifier, mainDial_identifier): #DONE-NOT TESTED
	identif_datetime = datetime.datetime.now().strftime("%Y%m%d%H%M")

	# TODO: meter en *Trace la marca de tiempo y el identif del MainDial (o Dial)
	skill_indiv = skill.split("#")[1]
	SkillTrace = skill_indiv+"_"+mainDial_identifier+"_"+identif_datetime+"_trace"
	
	frame_indiv = frame.split("#")[1]
	FrameTrace = frame_indiv+"_"+mainDial_identifier+"_"+identif_datetime+"_trace"
	
	intent_indiv = intent.split("#")[1]
	IntentTrace = frame_indiv+"_"+mainDial_identifier+"_"+identif_datetime+"_trace"

	ActionTrace = actionidval+"_"+mainDial_identifier+"_"+identif_datetime+"_trace"

	args_query = getArgsListforQuery(header, getActionArgs_response, ActionTrace, mainDial_identifier)

	# query_updateTraces_template = """
	# insert in <{graph}>{{
	# 	dialSystemOntInst:{SkillTrace} a domainOntFrame:SkillTrace .
	# 	dialSystemOntInst:{FrameTrace} a domainOntFrame:FrameTrace .
	# 	dialSystemOntInst:{IntentTrace} a domainOntFrame:IntentTrace .
	# 	dialSystemOntInst:{ActionTrace} a domainOntFrame:ActionTrace .
	# 	dialSystemOntInst:{IntentTrace} domainOntFrame:isIntentOf dialSystemOntInst:{SkillTrace} .
	# 	dialSystemOntInst:{IntentTrace} domainOntFrame:hasFrame dialSystemOntInst:{FrameTrace} .
	# 	dialSystemOntInst:{ActionTrace} domainOntFrame:hasIntent dialSystemOntInst:{IntentTrace} .
	# 	dialSystemOntInst:{CurrentUserRequest} dialSystemOnt:hasActionTrace dialSystemOntInst:{ActionTrace} . {ArgsQuery}
	# 	}}"""

	query_updateTraces_template = """
		insert in <{graph}>{{
		dialSystemOntInst:{SkillTrace} a domainOnt:SkillTrace .
		dialSystemOntInst:{FrameTrace} a domainOnt:FrameTrace .
		dialSystemOntInst:{IntentTrace} a domainOnt:IntentTrace .
		dialSystemOntInst:{ActionTrace} a domainOnt:ActionTrace .
		dialSystemOntInst:{IntentTrace} domainOntFrame:isIntentOf dialSystemOntInst:{SkillTrace} .
		dialSystemOntInst:{IntentTrace} domainOntFrame:hasFrame dialSystemOntInst:{FrameTrace} .
		dialSystemOntInst:{ActionTrace} domainOntFrame:hasIntent dialSystemOntInst:{IntentTrace} .
		{ArgsQuery} }}""" #para probar servicio inserts
	
	query_updateTraces = header+query_updateTraces_template.format(graph=graph, SkillTrace=SkillTrace, FrameTrace=FrameTrace, IntentTrace=IntentTrace, ActionTrace=ActionTrace, CurrentUserRequest=userrequest_identifier, ArgsQuery=args_query)
	# print(query_updateTraces)
	query = create_str_json(query_updateTraces)
	print(query)

	#TODO: virtuoso_request = virtuoso.request_insert(json.loads(query))
	#TODO: return(virtuoso_request) 
	#TODO: return?

#TODO: gestionar si toda la info está disponible a través de asignación o a través de query?

print(insertTraces(graph_ALKA, header_alka, "#skillX", "#frameX", "#intentX", "#actionX", "actionidval", args, "userrequest_23102020", "MainDial23102020"))

def getInfoCompatible(graph, header, action, arg_LexUnit): #DONE-TESTED
	#arg_LexUnit ~ key element
	sparql_target_adequacy_template = """
	select distinct ?argument ?type ?value
	FROM <{graph}>
	where {{
		<{action}> domainOntFrame:hasArgument ?argument .
		?argument domainOnt:appliesToWorldElementGroup ?group .
		?argument domainOnt:hasType ?t .
		?t domainOnt:IDval ?type .
		?group domainOnt:hasGroupMember ?elem .
		?elem domainOnt:hasLexicalUnit ?elemlu .
		?elemlu domainOnt:IDval ?elemluidval .
		?elem domainOnt:hasTargetSystemReadableInfo ?elemTSRI .
		?elemTSRI domainOnt:val ?value .
		VALUES ?elemluidval {{'{targetLU}'}} 
		}}"""

	sparql_target_adequacy = header+sparql_target_adequacy_template.format(graph=graph, action=action, targetLU=arg_LexUnit)
	
	query = create_str_json(sparql_target_adequacy)
	print(query)

	virtuoso_request = virtuoso.request_select(json.loads(query))
	#out = [{'argument': 'http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#InformationRequest_TargetItem_drawing', 'type': 'targetitem', 'value': 'maq0956'}]
		
	#return T/F?
	return(virtuoso_request) 

# print(getInfoCompatible(graph_ALKA, header_alka, "http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#GMAO_InformationRequest_drawing", "rectificadora.N"))


def getReadyInfo(graph, header, action): #FUTURE
	# sparql_info_ready = """
	# ASK {
	# 	?arg a domainOntFrame:argument .
	# 	?arg domainOntFrame:isCoreArgumentOf domainOntInst:{action} .
	# 	VALUES ?arg {""} . 
	# 	}
	# """ #esto devuelve T/F - ver comment siguiente
	sparql_info_ready_template ="""
	SELECT distinct ?arg
	FROM <{graph}>
	where {{
		?argTrace a domainOntFrame:ArgumentTrace .
		?argTrace domainOntFrame:isCoreArgumentOf domainOntFrame:{actionTrace} .
		?argTrace domainOntFrame:hasValue geo:SpatialThing .}}
	"""

	sparql_info_ready = header+sparql_info_ready_template.format(graph=graph, action=action)
	query = create_str_json(sparql_info_ready)
	# print(query)

	# virtuoso_request = virtuoso.request_select(json.loads(query))
	# print(virtuoso_request)

# print(getReadyInfo(graph_ALKA, header_alka, ""))


	#sparql

def updateArgValue(graph, arg_identifier):
	identif_datetime = datetime.datetime.now().strftime("%Y%m%d%H%M")
	
	updateArgVal_template = """
	DELETE ?rel FROM <{graph}> {
		dialSystemOntInst:{ArgTrace} DomainOnt:hasValue DomainOnt:ArgumentTrace .
		}

	insert data {
		dialSystemOntInst:{arg_identifier} domainOntFrame:hasValue domainOntWorldInst:{{WorldElement}} .
	}
	""" #realmente es domainOntWorldInst?



# def dialogue(userRequest): #main






