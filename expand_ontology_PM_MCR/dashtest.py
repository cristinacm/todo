import dash
import dash_html_components as html
import dash_core_components as dcc

import retrieve_from_PM as PM

# args = PM.args
# data_PM = PM.extract_ili_predmatrix_morethanoneframe(PM.pred_matrix_split, args)

# opts = []
# for frame in data_PM.keys():
# 	# print(frame)
# 	opt = {'label': frame, 'value':frame.lstrip()[3:]}
# 	opts.append(opt)
# print(opts)


pred_matrix = PM.pred_matrix_split
frames = []
LUs = []
SUMOs = []
for pred in pred_matrix:
	# print(pred)
	frame_col = pred.split("\t")[12]
	# print(frame_col)
	LU_col = pred.split("\t")[13]
	SUMO_col = pred.split("\t")[19]
	# ILI_col = pred.split("\t")[11]
	# arg_col = pred.split("\t")[14]
	if frame_col not in frames:
		frames.append(frame_col)
		frames.sort()
	if LU_col not in LUs:
		LUs.append(LU_col)
		LUs.sort()
	if SUMO_col not in SUMOs:
		SUMOs.append(SUMO_col)
		SUMOs.sort()

opts_frame=[]
for frame in frames:
	opt_frame = {'label': frame, 'value':frame.lstrip()[3:]}
	opts_frame.append(opt_frame)

opts_LU=[]
for lu in LUs:
	opt_lu = {'label': lu, 'value':lu.lstrip()[3:]}
	opts_LU.append(opt_lu)

opts_SUMO=[]
for sumo in SUMOs:
	opt_sumo = {'label': sumo, 'value':sumo.lstrip()[3:]}
	opts_SUMO.append(opt_sumo)


external_stylesheets = ['https://codepen.io/chriddyp/pen/bWLwgP.css']

app = dash.Dash(__name__, external_stylesheets=external_stylesheets)
app.layout = html.Div([
    dcc.Dropdown(
        id='frame_dropdown',
		options=opts_frame,
			
        
        # options=[
        #     {'label': 'New York City', 'value': 'NYC'},
        #     {'label': 'Montreal', 'value': 'MTL'},
        #     {'label': 'San Francisco', 'value': 'SF'}
        # ],
        # value=opts[0]["value"]
		multi=True,
		placeholder="Select the Frame(s)..."
    ),
    html.Div(id='frame-dd-output-container'),
	dcc.Dropdown(
        id='lu-dropdown',
		options=opts_LU,
			
        
        # options=[
        #     {'label': 'New York City', 'value': 'NYC'},
        #     {'label': 'Montreal', 'value': 'MTL'},
        #     {'label': 'San Francisco', 'value': 'SF'}
        # ],
        # value=opts[0]["value"]
		multi=True,
		placeholder="Select the Lexical Unit(s)..."
    ),
    html.Div(id='lu-dd-output-container'),
	dcc.Dropdown(
        id='sumo-dropdown2',
		options=opts_SUMO,
			
        
        # options=[
        #     {'label': 'New York City', 'value': 'NYC'},
        #     {'label': 'Montreal', 'value': 'MTL'},
        #     {'label': 'San Francisco', 'value': 'SF'}
        # ],
        # value=opts[0]["value"]
    	multi=True,
		placeholder="Select the SUMO(s)..."
	),
    html.Div(id='sumo-dd-output-container'),
])


@app.callback(
    dash.dependencies.Output('dd-output-container', 'children'),
    [dash.dependencies.Input('demo-dropdown', 'value')])
def update_output(value):
    return 'You have selected "{}"'.format(value)


if __name__ == '__main__':
    app.run_server(debug=True)