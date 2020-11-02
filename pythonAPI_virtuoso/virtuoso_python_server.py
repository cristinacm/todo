 
from fastapi import FastAPI
import virtuoso_python as virtuosopy
import json
import requests

from typing import Dict

app = FastAPI()

@app.post("/query_virtuoso")
async def query_virtuoso(request: Dict):
	# query_json = requests.get_json()
	query = request["query"]
	data=virtuosopy.sparqlQuery(query)
	# data_json = json.dumps(data, sort_keys=True, indent=4)
	return(data)