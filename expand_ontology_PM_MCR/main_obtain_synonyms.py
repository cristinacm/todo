import retrieve_from_PM as ret_data
import mysql_test as get_from_sql
import pprint as pp
import os
import argparse

pred_matrix = (open("PredicateMatrix_v1_3.tsv")).read()
pred_matrix_split = pred_matrix.split("\n")

args = {"fn:Motion": {"LUs": ["fn:go.v", "fn:move.v"], "SUMOs": ["mcr:Motion", "mcr:DirectionChange", "mcr:BodyMotion"]}, "fn:Taking": {"LUs": ["fn:take.v", "NULL"], "SUMOs": ["mcr:Guiding", "mcr:Transfer"]}} #cambiar LUs a FHs

def get_info(args, pred_matrix_split):
	PM_data = ret_data.extract_ili_predmatrix_morethanoneframe(pred_matrix_split, args)

	#PM_data -> {'fn:Motion': {'fn:go.v': {'mcr': ['ili-30-01835496-v', 'ili-30-01848718-v'], 'LUs': []}, 'fn:move.v': {'mcr': ['ili-30-00014549-v', 'ili-30-01831531-v', 'ili-30-01835496-v'], 'LUs': []}}, 'fn:Taking': {'fn:take.v': {'mcr': ['ili-30-01999798-v', 'ili-30-02077656-v', 'ili-30-02717102-v'], 'LUs': []}}}

	for frame in PM_data.keys():
		for fh in PM_data[frame].keys():
			ilis_list = PM_data[frame][fh]["mcr"]
			# print(ilis_list)
			words = get_from_sql.connect(ilis_list)
			# print("fh:", words)
			PM_data[frame][fh]["LUs"] = words
	
	# pp.pprint(PM_data)
	# print(PM_data)
	return(PM_data)

PM_data = get_info(args, pred_matrix_split)

# PM_data -> {'fn:Motion': {'fn:go.v': {'mcr': ['ili-30-01835496-v', 'ili-30-01848718-v'], 'LUs': ['acudir', 'desplazarse', 'ir', 'mover', 'moverse', 'viajar', 'partir']}, 'fn:move.v': {'mcr': ['ili-30-00014549-v', 'ili-30-01831531-v', 'ili-30-01835496-v'], 'LUs': ['estar_activo', 'moverse', 'mover', 'trasladar', 'acudir', 'desplazarse', 'ir', 'viajar']}}, 'fn:Taking': {'fn:take.v': {'mcr': ['ili-30-01999798-v', 'ili-30-02077656-v', 'ili-30-02717102-v'], 'LUs': ['apoderar', 'conducir', 'copar', 'dirigir', 'encaminar', 'guiar', 'llevar', 'acarrear', 'portar', 'tomar', 'traer', 'transportar', 'cargar']}}}

def info_to_sparql(PM_data):
	sparql_header = "prefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-GuideRobot#>\nprefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#>\nprefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#>\nprefix rdfs: <http://www.w3.org/2000/01/rd-schema#>\nprefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>\ninsert data"

	frame_insert_template = "domainOntInst:{frame} a domainOntFrame:Frame ."
	idval_insert_template = 'domainOntInst:{element} domainOnt:IDval "{element}" .'
	framehead_insert_template = "domainOntInst:{framehead} a domainOntFrame:FrameHead ."
	frame_has_framehead_insert_template = "domainOntInst:{frame} domainOntFrame:hasFrameHead domainOntInst:{framehead} ." 
	lu_insert_template = "domainOntInst:{lu} a domainOnt:LexicalUnit ."
	framehead_has_lu_insert_template = "domainOntInst:{framehead} domainOnt:hasLexicalUnit domainOntInst:{lu} ."

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
			for lu in PM_data[frame][fh]["LUs"]:
				lu_insert = lu_insert_template.format(lu=lu)
				idval_lu_insert = idval_insert_template.format(element=lu)
				framehead_has_lu_insert = framehead_has_lu_insert_template.format(framehead=fh+"_"+frame, lu=lu)
				inserts.extend([lu_insert, idval_lu_insert, framehead_has_lu_insert])
	
	#En cada insert, habría que meter "\n" o hacerlo al unirlo todo
	#Hay que vigilar qué pasa con idval_insert_template (comillas en element)

	inserts = "\n".join(inserts)

	# pp.pprint(inserts)
	# print(inserts)
	return(inserts)

insert = info_to_sparql(PM_data)

# insert:
# prefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-GuideRobot#>
# prefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#>
# prefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#>
# prefix rdfs: <http://www.w3.org/2000/01/rd-schema#>
# prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
# insert data
# domainOntInst:fn:Motion a domainOntFrame:Frame .
# domainOntInst:fn:Motion domainOnt:IDval "fn:Motion" .
# domainOntInst:fn:go.v_fn:Motion a domainOntFrame:FrameHead .
# domainOntInst:fn:go.v_fn:Motion domainOnt:IDval "fn:go.v_fn:Motion" .
# domainOntInst:fn:Motion domainOntFrame:hasFrameHead domainOntInst:fn:go.v_fn:Motion .
# domainOntInst:acudir a domainOnt:LexicalUnit .
# domainOntInst:acudir domainOnt:IDval "acudir" .
# domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:acudir .
# domainOntInst:desplazarse a domainOnt:LexicalUnit .
# domainOntInst:desplazarse domainOnt:IDval "desplazarse" .
# domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:desplazarse .
# domainOntInst:ir a domainOnt:LexicalUnit .
# domainOntInst:ir domainOnt:IDval "ir" .
# domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:ir .
# domainOntInst:mover a domainOnt:LexicalUnit .
# domainOntInst:mover domainOnt:IDval "mover" .
# domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:mover .
# domainOntInst:moverse a domainOnt:LexicalUnit .
# domainOntInst:moverse domainOnt:IDval "moverse" .
# domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:moverse .
# domainOntInst:viajar a domainOnt:LexicalUnit .
# domainOntInst:viajar domainOnt:IDval "viajar" .
# domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:viajar .
# domainOntInst:partir a domainOnt:LexicalUnit .
# domainOntInst:partir domainOnt:IDval "partir" .
# domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:partir .
# domainOntInst:fn:move.v_fn:Motion a domainOntFrame:FrameHead .
# domainOntInst:fn:move.v_fn:Motion domainOnt:IDval "fn:move.v_fn:Motion" .
# domainOntInst:fn:Motion domainOntFrame:hasFrameHead domainOntInst:fn:move.v_fn:Motion .
# domainOntInst:estar_activo a domainOnt:LexicalUnit .
# domainOntInst:estar_activo domainOnt:IDval "estar_activo" .
# domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:estar_activo .
# domainOntInst:moverse a domainOnt:LexicalUnit .
# domainOntInst:moverse domainOnt:IDval "moverse" .
# domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:moverse .
# domainOntInst:mover a domainOnt:LexicalUnit .
# domainOntInst:mover domainOnt:IDval "mover" .
# domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:mover .
# domainOntInst:trasladar a domainOnt:LexicalUnit .
# domainOntInst:trasladar domainOnt:IDval "trasladar" .
# domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:trasladar .
# domainOntInst:acudir a domainOnt:LexicalUnit .
# domainOntInst:acudir domainOnt:IDval "acudir" .
# domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:acudir .
# domainOntInst:desplazarse a domainOnt:LexicalUnit .
# domainOntInst:desplazarse domainOnt:IDval "desplazarse" .
# domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:desplazarse .
# domainOntInst:ir a domainOnt:LexicalUnit .
# domainOntInst:ir domainOnt:IDval "ir" .
# domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:ir .
# domainOntInst:viajar a domainOnt:LexicalUnit .
# domainOntInst:viajar domainOnt:IDval "viajar" .
# domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:viajar .
# domainOntInst:fn:Taking a domainOntFrame:Frame .
# domainOntInst:fn:Taking domainOnt:IDval "fn:Taking" .
# domainOntInst:fn:take.v_fn:Taking a domainOntFrame:FrameHead .
# domainOntInst:fn:take.v_fn:Taking domainOnt:IDval "fn:take.v_fn:Taking" .
# domainOntInst:fn:Taking domainOntFrame:hasFrameHead domainOntInst:fn:take.v_fn:Taking .
# domainOntInst:apoderar a domainOnt:LexicalUnit .
# domainOntInst:apoderar domainOnt:IDval "apoderar" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:apoderar .
# domainOntInst:conducir a domainOnt:LexicalUnit .
# domainOntInst:conducir domainOnt:IDval "conducir" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:conducir .
# domainOntInst:copar a domainOnt:LexicalUnit .
# domainOntInst:copar domainOnt:IDval "copar" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:copar .
# domainOntInst:dirigir a domainOnt:LexicalUnit .
# domainOntInst:dirigir domainOnt:IDval "dirigir" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:dirigir .
# domainOntInst:encaminar a domainOnt:LexicalUnit .
# domainOntInst:encaminar domainOnt:IDval "encaminar" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:encaminar .
# domainOntInst:guiar a domainOnt:LexicalUnit .
# domainOntInst:guiar domainOnt:IDval "guiar" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:guiar .
# domainOntInst:llevar a domainOnt:LexicalUnit .
# domainOntInst:llevar domainOnt:IDval "llevar" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:llevar .
# domainOntInst:acarrear a domainOnt:LexicalUnit .
# domainOntInst:acarrear domainOnt:IDval "acarrear" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:acarrear .
# domainOntInst:portar a domainOnt:LexicalUnit .
# domainOntInst:portar domainOnt:IDval "portar" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:portar .
# domainOntInst:tomar a domainOnt:LexicalUnit .
# domainOntInst:tomar domainOnt:IDval "tomar" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:tomar .
# domainOntInst:traer a domainOnt:LexicalUnit .
# domainOntInst:traer domainOnt:IDval "traer" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:traer .
# domainOntInst:transportar a domainOnt:LexicalUnit .
# domainOntInst:transportar domainOnt:IDval "transportar" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:transportar .
# domainOntInst:cargar a domainOnt:LexicalUnit .
# domainOntInst:cargar domainOnt:IDval "cargar" .
# domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:cargar .


# inserts:
# ['domainOntInst:fn:Motion a domainOntFrame:Frame .',
#  'domainOntInst:fn:Motion domainOnt:IDval fn:Motion .',
#  'domainOntInst:fn:go.v_fn:Motion a domainOntFrame:FrameHead .',
#  'domainOntInst:fn:go.v_fn:Motion domainOnt:IDval fn:go.v_fn:Motion .',
#  'domainOntInst:fn:Motion domainOntFrame:hasFrameHead '
#  'domainOntInst:fn:go.v_fn:Motion .',
#  'domainOntInst:acudir a domainOnt:LexicalUnit .',
#  'domainOntInst:acudir domainOnt:IDval acudir .',
#  'domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:acudir .',
#  'domainOntInst:desplazarse a domainOnt:LexicalUnit .',
#  'domainOntInst:desplazarse domainOnt:IDval desplazarse .',
#  'domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:desplazarse .',
#  'domainOntInst:ir a domainOnt:LexicalUnit .',
#  'domainOntInst:ir domainOnt:IDval ir .',
#  'domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:ir .',
#  'domainOntInst:mover a domainOnt:LexicalUnit .',
#  'domainOntInst:mover domainOnt:IDval mover .',
#  'domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:mover .',
#  'domainOntInst:moverse a domainOnt:LexicalUnit .',
#  'domainOntInst:moverse domainOnt:IDval moverse .',
#  'domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:moverse .',
#  'domainOntInst:viajar a domainOnt:LexicalUnit .',
#  'domainOntInst:viajar domainOnt:IDval viajar .',
#  'domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:viajar .',
#  'domainOntInst:partir a domainOnt:LexicalUnit .',
#  'domainOntInst:partir domainOnt:IDval partir .',
#  'domainOntInst:fn:go.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:partir .',
#  'domainOntInst:fn:move.v_fn:Motion a domainOntFrame:FrameHead .',
#  'domainOntInst:fn:move.v_fn:Motion domainOnt:IDval fn:move.v_fn:Motion .',
#  'domainOntInst:fn:Motion domainOntFrame:hasFrameHead domainOntInst:fn:move.v_fn:Motion .',
#  'domainOntInst:estar_activo a domainOnt:LexicalUnit .',
#  'domainOntInst:estar_activo domainOnt:IDval estar_activo .',
#  'domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:estar_activo .',
#  'domainOntInst:moverse a domainOnt:LexicalUnit .',
#  'domainOntInst:moverse domainOnt:IDval moverse .',
#  'domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:moverse .',
#  'domainOntInst:mover a domainOnt:LexicalUnit .',
#  'domainOntInst:mover domainOnt:IDval mover .',
#  'domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:mover .',
#  'domainOntInst:trasladar a domainOnt:LexicalUnit .',
#  'domainOntInst:trasladar domainOnt:IDval trasladar .',
#  'domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:trasladar .',
#  'domainOntInst:acudir a domainOnt:LexicalUnit .',
#  'domainOntInst:acudir domainOnt:IDval acudir .',
#  'domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:acudir .',
#  'domainOntInst:desplazarse a domainOnt:LexicalUnit .',
#  'domainOntInst:desplazarse domainOnt:IDval desplazarse .',
#  'domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:desplazarse .',
#  'domainOntInst:ir a domainOnt:LexicalUnit .',
#  'domainOntInst:ir domainOnt:IDval ir .',
#  'domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:ir .',
#  'domainOntInst:viajar a domainOnt:LexicalUnit .',
#  'domainOntInst:viajar domainOnt:IDval viajar .',
#  'domainOntInst:fn:move.v_fn:Motion domainOnt:hasLexicalUnit domainOntInst:viajar .',
#  'domainOntInst:fn:Taking a domainOntFrame:Frame .',
#  'domainOntInst:fn:Taking domainOnt:IDval fn:Taking .',
#  'domainOntInst:fn:take.v_fn:Taking a domainOntFrame:FrameHead .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:IDval fn:take.v_fn:Taking .',
#  'domainOntInst:fn:Taking domainOntFrame:hasFrameHead domainOntInst:fn:take.v_fn:Taking .',
#  'domainOntInst:apoderar a domainOnt:LexicalUnit .',
#  'domainOntInst:apoderar domainOnt:IDval apoderar .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:apoderar .',
#  'domainOntInst:conducir a domainOnt:LexicalUnit .',
#  'domainOntInst:conducir domainOnt:IDval conducir .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:conducir .',
#  'domainOntInst:copar a domainOnt:LexicalUnit .',
#  'domainOntInst:copar domainOnt:IDval copar .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:copar .',
#  'domainOntInst:dirigir a domainOnt:LexicalUnit .',
#  'domainOntInst:dirigir domainOnt:IDval dirigir .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:dirigir .',
#  'domainOntInst:encaminar a domainOnt:LexicalUnit .',
#  'domainOntInst:encaminar domainOnt:IDval encaminar .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:encaminar .',
#  'domainOntInst:guiar a domainOnt:LexicalUnit .',
#  'domainOntInst:guiar domainOnt:IDval guiar .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:guiar .',
#  'domainOntInst:llevar a domainOnt:LexicalUnit .',
#  'domainOntInst:llevar domainOnt:IDval llevar .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:llevar .',
#  'domainOntInst:acarrear a domainOnt:LexicalUnit .',
#  'domainOntInst:acarrear domainOnt:IDval acarrear .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:acarrear .',
#  'domainOntInst:portar a domainOnt:LexicalUnit .',
#  'domainOntInst:portar domainOnt:IDval portar .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:portar .',
#  'domainOntInst:tomar a domainOnt:LexicalUnit .',
#  'domainOntInst:tomar domainOnt:IDval tomar .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:tomar .',
#  'domainOntInst:traer a domainOnt:LexicalUnit .',
#  'domainOntInst:traer domainOnt:IDval traer .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:traer .',
#  'domainOntInst:transportar a domainOnt:LexicalUnit .',
#  'domainOntInst:transportar domainOnt:IDval transportar .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:transportar .',
#  'domainOntInst:cargar a domainOnt:LexicalUnit .',
#  'domainOntInst:cargar domainOnt:IDval cargar .',
#  'domainOntInst:fn:take.v_fn:Taking domainOnt:hasLexicalUnit domainOntInst:cargar .']



