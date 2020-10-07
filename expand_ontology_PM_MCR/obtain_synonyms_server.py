#!/usr/bin/python3

from flask import Flask, request, jsonify
import main_obtain_synonyms as obtain_syns
import requests

#config = etree.parse("config/config.xml")
pred_matrix = (open("PredicateMatrix_v1_3.tsv")).read()
pred_matrix_split = pred_matrix.split("\n")

app = Flask(__name__)

@app.route('/update_synonyms_ontology', methods=['POST'])
def update_synonyms_ontology():

    '''
    Given a JSON with a selected frame, and its selected FHs and SUMO tags, it updates the ontology with said information and the MCR synonyms for each of the FHs, as LUs.
    '''

    data = request.get_json()
    sparql_query = obtain_syns.info_to_sparql(data, pred_matrix_split)

    update_ontology = requests.post("http://10.0.0.161:8083/CAMService/SPARQLInferenceUpdate", sparql_query)

    response_update = update_ontology.text

    return(update_ontology.text)
    # return interpreter.interpreter(tree, command, config, cluster), {'Content-Type': 'application/json'}

if __name__ == '__main__':
    app.run(host="localhost", port=8089)