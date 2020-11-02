#!/usr/bin/python3
#
# Demonstrating use of a single query to populate a # Virtuoso Quad Store via Python. 
#

import requests
import json

# HTTP URL is constructed accordingly with JSON query results format in mind.

def sparqlQuery(query,format="application/json"):
	params={
		"default-graph": "",
		#"should-sponge": "soft",
		"query": query,
		"debug": "on",
		"timeout": "",
		"format": format,
		"save": "display",
		"fname": ""
	}
	
	# querypart=urllib.parse.urlencode(params).encode("utf-8")
	baseURL="http://10.0.0.183:8890/sparql/" #IP de máquina de virtuoso 8.3
	response = requests.get(baseURL,params)
	# print(response.text)
	return json.loads(response.text)


# # Setting Data Source Name (DSN)
# dsn="urn:ALKAdata"

# # Virtuoso pragmas for instructing SPARQL engine to perform an HTTP GET
# # using the IRI in FROM clause as Data Source URL
# query=""" DEFINE input:inference '%s'
# prefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#>
# prefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#>
# prefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#>
# prefix rdfs: <http://www.w3.org/2000/01/rd-schema#>
# prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
# select distinct ?intent ?action ?actionidval from <%s> where {
# ?intent a domainOntFrame:Intent.
# ?intent domainOntFrame:hasFrame ?frame.
# ?frame domainOntFrame:hasFrameHead ?framehead .
# ?framehead domainOnt:hasLexicalUnit ?lu .
# ?lu domainOnt:IDval ?luval .
# VALUES ?luval {"informar.V"}
# ?intent domainOntFrame:belongsToAction ?action .
# ?action domainOnt:IDval ?actionidval .
# }""" % (dsn,dsn)

# # print(query)

# data=sparqlQuery(query)

# print(data)

# print(json.dumps(data, sort_keys=True, indent=4))

#
# End