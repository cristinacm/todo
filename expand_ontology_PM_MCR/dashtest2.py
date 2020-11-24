import dash
import dash_html_components as html
import dash_core_components as dcc
import pandas as pd

df= pd.read_csv(r"PredicateMatrix_v1_3.csv")

options = []
for col in df.columns:
    options.append({'label':'{}'.format(col, col), 'value':col})

external_stylesheets = ['https://codepen.io/chriddyp/pen/bWLwgP.css']

app = dash.Dash(__name__, external_stylesheets=external_stylesheets)

app.layout = html.Div([
        html.Label("Select a feature from drop-down to plot HISTOGRAM"),
        dcc.Dropdown(
            id = 'my_dropdown1',
            options= options,
            value='Choose columns'
        ),
        html.Label(id='my_label1'),

        html.Button(
            id='submit-button',
            n_clicks=0,
            children='Submit',
            style={'fontSize':24, 'marginLeft':'30px'}
        )
        ])

@app.callback(
    dash.dependencies.Output('my_label1', 'children'),
    [dash.dependencies.Input('my_dropdown1', 'value')]
    )
def update_dropdown(value):
    return 'You have chosen {} for histogram plot!'.format(value)

if __name__ == '__main__':
    app.run_server(debug=True)