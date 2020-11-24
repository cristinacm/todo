#!/usr/bin/python3

import retrieve_from_PM as ret_data
import mysql_connect as get_from_sql
import pprint as pp
import os
import argparse

# pred_matrix = (open("PredicateMatrix_v1_3.tsv")).read()
# pred_matrix_split = pred_matrix.split("\n")

# args = {"fn:Motion": {"FHs": ["fn:go.v", "fn:move.v"], "SUMOs": ["mcr:Motion", "mcr:DirectionChange", "mcr:BodyMotion"]}, "fn:Taking": {"FHs": ["fn:take.v", "NULL"], "SUMOs": ["mcr:Guiding", "mcr:Transfer"]}} #cambiar LUs a FHs

def get_info(args, pred_matrix_split):
	PM_data = ret_data.extract_ili_predmatrix_morethanoneframe(pred_matrix_split, args)

	#PM_data -> {'fn:Motion': {'fn:go.v': {'mcr': ['ili-30-01835496-v', 'ili-30-01848718-v'], 'LUs': []}, 'fn:move.v': {'mcr': ['ili-30-00014549-v', 'ili-30-01831531-v', 'ili-30-01835496-v'], 'LUs': []}}, 'fn:Taking': {'fn:take.v': {'mcr': ['ili-30-01999798-v', 'ili-30-02077656-v', 'ili-30-02717102-v'], 'LUs': []}}}
	#PM_data -> {'Motion': {'go': {'mcr': ['ili-30-01835496-v', 'ili-30-01848718-v'], 'LUs': ''}, 'move': {'mcr': ['ili-30-00014549-v', 'ili-30-01831531-v', 'ili-30-01835496-v'], 'LUs': ''}}, 'Taking': {'take': {'mcr': ['ili-30-01999798-v', 'ili-30-02077656-v', 'ili-30-02717102-v'], 'LUs': ''}}}
	for frame in PM_data.keys():
		for fh in PM_data[frame].keys():
			ilis_list = PM_data[frame][fh]["mcr"]
			# print(ilis_list)
			words = get_from_sql.connect(ilis_list)
			# print("fh:", words)
			PM_data[frame][fh]["FHs"] = words
	
	# pp.pprint(PM_data)
	# print(PM_data)
	return(PM_data)

# PM_data = get_info(args, pred_matrix_split)

# PM_data -> {'Motion': {'go': {'mcr': ['ili-30-01835496-v', 'ili-30-01848718-v'], 'LUs': ['acudir', 'desplazarse', 'ir', 'mover', 'moverse', 'viajar', 'partir']}, 'move': {'mcr': ['ili-30-00014549-v', 'ili-30-01831531-v', 'ili-30-01835496-v'], 'LUs': ['estar_activo', 'moverse', 'mover', 'trasladar', 'acudir', 'desplazarse', 'ir', 'viajar']}}, 'Taking': {'take': {'mcr': ['ili-30-01999798-v', 'ili-30-02077656-v', 'ili-30-02717102-v'], 'LUs': ['apoderar', 'conducir', 'copar', 'dirigir', 'encaminar', 'guiar', 'llevar', 'acarrear', 'portar', 'tomar', 'traer', 'transportar', 'cargar']}}}

def info_to_sparql(args, pred_matrix_split):
	PM_data = get_info(args, pred_matrix_split)

	sparql_header = "DEFINE input:inference 'urn:ALKAdata' prefix dialSystemOnt: <http://www.semanticweb.org/linuxsii/ontologies/2020/6/DialSystemONT#>\nprefix dialSystemOntInst: <http://www.semanticweb.org/linuxsii/ontologies/2020/6/DialSystemONT-inst#>\nprefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#>\nprefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#>\nprefix domainOntWorld: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-World#>\nprefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#>\nprefix dialogueOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DialogueONT#>\nprefix dialogueOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DialogueONT-inst#>\ninsert in <urn:ALKAdata>{"

	frame_insert_template = "domainOntInst:{frame} a domainOntFrame:Frame ."
	idval_insert_template = 'domainOntInst:{element} domainOnt:IDval "{element}" .'
	framehead_insert_template = "domainOntInst:{framehead} a domainOntFrame:FrameHead ."
	frame_has_framehead_insert_template = "domainOntInst:{frame} domainOntFrame:hasFrameHead domainOntInst:{framehead} ." 
	lu_insert_template = "domainOntInst:{lu}.V a domainOnt:LexicalUnit ."
	framehead_has_lu_insert_template = "domainOntInst:{framehead} domainOnt:hasLexicalUnit domainOntInst:{lu}.V ."

	inserts = []
	inserts.append(sparql_header)

	for frame in PM_data.keys():
		frame_insert = frame_insert_template.format(frame=frame)
		idval_frame_insert = idval_insert_template.format(element=frame)
		inserts.extend([frame_insert, idval_frame_insert])
		for fh in PM_data[frame].keys():
			framehead_insert = framehead_insert_template.format(framehead=fh+"_"+frame)
			idval_framehead_insert = idval_insert_template.format(element=fh+"_"+frame)
			frame_has_framehead_insert = frame_has_framehead_insert_template.format(frame=frame, framehead=fh+"_"+frame)
			inserts.extend([framehead_insert, idval_framehead_insert, frame_has_framehead_insert])
			for lu in PM_data[frame][fh]["FHs"]:
				lu_insert = lu_insert_template.format(lu=lu)
				idval_lu_insert = idval_insert_template.format(element=lu+".V")
				framehead_has_lu_insert = framehead_has_lu_insert_template.format(framehead=fh+"_"+frame, lu=lu)
				inserts.extend([lu_insert, idval_lu_insert, framehead_has_lu_insert])


	inserts = "\n".join(inserts)
	inserts = inserts+"}"

	# pp.pprint(inserts)
	# print(inserts)
	return(inserts)

# insert = info_to_sparql(PM_data)

# insert:
# prefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-GuideRobot#>
# prefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#>
# prefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#>
# prefix rdfs: <http://www.w3.org/2000/01/rd-schema#>
# prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
# insert data{
# domainOntInst:Motion a domainOntFrame:Frame .
# domainOntInst:Motion domainOnt:IDval "Motion" .
# domainOntInst:go_Motion a domainOntFrame:FrameHead .
# domainOntInst:go_Motion domainOnt:IDval "go_Motion" .
# domainOntInst:Motion domainOntFrame:hasFrameHead domainOntInst:go_Motion .
# domainOntInst:acudir a domainOnt:LexicalUnit .
# domainOntInst:acudir domainOnt:IDval "acudir" .
# domainOntInst:go_Motion domainOnt:hasLexicalUnit domainOntInst:acudir .
# domainOntInst:desplazarse a domainOnt:LexicalUnit .
# domainOntInst:desplazarse domainOnt:IDval "desplazarse" .
# domainOntInst:go_Motion domainOnt:hasLexicalUnit domainOntInst:desplazarse .
# domainOntInst:ir a domainOnt:LexicalUnit .
# domainOntInst:ir domainOnt:IDval "ir" .
# domainOntInst:go_Motion domainOnt:hasLexicalUnit domainOntInst:ir .
# domainOntInst:mover a domainOnt:LexicalUnit .
# domainOntInst:mover domainOnt:IDval "mover" .
# domainOntInst:go_Motion domainOnt:hasLexicalUnit domainOntInst:mover .
# domainOntInst:moverse a domainOnt:LexicalUnit .
# domainOntInst:moverse domainOnt:IDval "moverse" .
# domainOntInst:go_Motion domainOnt:hasLexicalUnit domainOntInst:moverse .
# domainOntInst:viajar a domainOnt:LexicalUnit .
# domainOntInst:viajar domainOnt:IDval "viajar" .
# domainOntInst:go_Motion domainOnt:hasLexicalUnit domainOntInst:viajar .
# domainOntInst:partir a domainOnt:LexicalUnit .
# domainOntInst:partir domainOnt:IDval "partir" .
# domainOntInst:go_Motion domainOnt:hasLexicalUnit domainOntInst:partir .
# domainOntInst:move_Motion a domainOntFrame:FrameHead .
# domainOntInst:move_Motion domainOnt:IDval "move_Motion" .
# domainOntInst:Motion domainOntFrame:hasFrameHead domainOntInst:move_Motion .
# domainOntInst:estar_activo a domainOnt:LexicalUnit .
# domainOntInst:estar_activo domainOnt:IDval "estar_activo" .
# domainOntInst:move_Motion domainOnt:hasLexicalUnit domainOntInst:estar_activo .
# domainOntInst:moverse a domainOnt:LexicalUnit .
# domainOntInst:moverse domainOnt:IDval "moverse" .
# domainOntInst:move_Motion domainOnt:hasLexicalUnit domainOntInst:moverse .
# domainOntInst:mover a domainOnt:LexicalUnit .
# domainOntInst:mover domainOnt:IDval "mover" .
# domainOntInst:move_Motion domainOnt:hasLexicalUnit domainOntInst:mover .
# domainOntInst:trasladar a domainOnt:LexicalUnit .
# domainOntInst:trasladar domainOnt:IDval "trasladar" .
# domainOntInst:move_Motion domainOnt:hasLexicalUnit domainOntInst:trasladar .
# domainOntInst:acudir a domainOnt:LexicalUnit .
# domainOntInst:acudir domainOnt:IDval "acudir" .
# domainOntInst:move_Motion domainOnt:hasLexicalUnit domainOntInst:acudir .
# domainOntInst:desplazarse a domainOnt:LexicalUnit .
# domainOntInst:desplazarse domainOnt:IDval "desplazarse" .
# domainOntInst:move_Motion domainOnt:hasLexicalUnit domainOntInst:desplazarse .
# domainOntInst:ir a domainOnt:LexicalUnit .
# domainOntInst:ir domainOnt:IDval "ir" .
# domainOntInst:move_Motion domainOnt:hasLexicalUnit domainOntInst:ir .
# domainOntInst:viajar a domainOnt:LexicalUnit .
# domainOntInst:viajar domainOnt:IDval "viajar" .
# domainOntInst:move_Motion domainOnt:hasLexicalUnit domainOntInst:viajar .
# domainOntInst:Taking a domainOntFrame:Frame .
# domainOntInst:Taking domainOnt:IDval "Taking" .
# domainOntInst:take_Taking a domainOntFrame:FrameHead .
# domainOntInst:take_Taking domainOnt:IDval "take_Taking" .
# domainOntInst:Taking domainOntFrame:hasFrameHead domainOntInst:take_Taking .
# domainOntInst:apoderar a domainOnt:LexicalUnit .
# domainOntInst:apoderar domainOnt:IDval "apoderar" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:apoderar .
# domainOntInst:conducir a domainOnt:LexicalUnit .
# domainOntInst:conducir domainOnt:IDval "conducir" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:conducir .
# domainOntInst:copar a domainOnt:LexicalUnit .
# domainOntInst:copar domainOnt:IDval "copar" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:copar .
# domainOntInst:dirigir a domainOnt:LexicalUnit .
# domainOntInst:dirigir domainOnt:IDval "dirigir" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:dirigir .
# domainOntInst:encaminar a domainOnt:LexicalUnit .
# domainOntInst:encaminar domainOnt:IDval "encaminar" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:encaminar .
# domainOntInst:guiar a domainOnt:LexicalUnit .
# domainOntInst:guiar domainOnt:IDval "guiar" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:guiar .
# domainOntInst:llevar a domainOnt:LexicalUnit .
# domainOntInst:llevar domainOnt:IDval "llevar" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:llevar .
# domainOntInst:acarrear a domainOnt:LexicalUnit .
# domainOntInst:acarrear domainOnt:IDval "acarrear" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:acarrear .
# domainOntInst:portar a domainOnt:LexicalUnit .
# domainOntInst:portar domainOnt:IDval "portar" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:portar .
# domainOntInst:tomar a domainOnt:LexicalUnit .
# domainOntInst:tomar domainOnt:IDval "tomar" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:tomar .
# domainOntInst:traer a domainOnt:LexicalUnit .
# domainOntInst:traer domainOnt:IDval "traer" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:traer .
# domainOntInst:transportar a domainOnt:LexicalUnit .
# domainOntInst:transportar domainOnt:IDval "transportar" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:transportar .
# domainOntInst:cargar a domainOnt:LexicalUnit .
# domainOntInst:cargar domainOnt:IDval "cargar" .
# domainOntInst:take_Taking domainOnt:hasLexicalUnit domainOntInst:cargar .}

