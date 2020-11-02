#!/usr/bin/python3
import requests

query = """DEFINE input:inference 'urn:ALKAdata'
prefix domainOntInst: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-inst-ALKA#>
prefix domainOntFrame: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT-FrameAction#>
prefix domainOnt: <http://www.semanticweb.org/caceta/ontologies/2020/3/DomainONT#>
prefix rdfs: <http://www.w3.org/2000/01/rd-schema#>
prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
select distinct ?intent ?action ?actionidval
from <urn:ALKAdata>
where {
?intent a domainOntFrame:Intent.
?intent domainOntFrame:hasFrame ?frame.
?frame domainOntFrame:hasFrameHead ?framehead .
?framehead domainOnt:hasLexicalUnit ?lu .
?lu domainOnt:IDval ?luval .
VALUES ?luval {"informar.V"}
?intent domainOntFrame:belongsToAction ?action .
?action domainOnt:IDval ?actionidval .
}"""

req = requests.post("http://127.0.0.1:8000/query_virtuoso", json={"query":query})

print(req.text)
