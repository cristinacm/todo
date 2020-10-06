#!/usr/bin/python3

import mysql.connector
from mysql.connector import Error

# mysql.connector.connect(host='10.0.0.192:3306',database='mcr-2016',user='root',password='Sii206744+')
# ilis_list = ["ili-30-00465762-v", "ili-30-01219544-v", "ili-30-01418536-v"]

def connect(ilis_list):
    """ Connect to MySQL database """
    conn = None
    try:
        conn = mysql.connector.connect(host='10.0.0.192',
										port='3306',
										database='mcr30-2016',
										user='ler',
										password='Sii206744+',
										time_zone="UTC")
        # if conn.is_connected():
        #     print('Connected to MySQL database')


        ilis_tuple = str(tuple(ilis_list))
        if ilis_tuple[-2] == ',':
        	ilis_tuple= ilis_tuple.replace(ilis_tuple[-2],"")
		# query = "select name from students where id IN {}".format(t)

        cursor = conn.cursor()
        cursor.execute("SELECT DISTINCT word FROM `wei_spa-30_variant` WHERE offset in (SELECT offset FROM `wei_spa-30_to_ili` where iliOffset in {})".format(ilis_tuple))
        
		# iterate over result
        out = []
        for row in cursor:
            out.append(row[0])

    except Error as e:
        print(e)
		
    return(out)
	


if __name__ == '__main__':
    connect(ilis_list)

# db = mysql.connector.connect(
#     host='10.0.0.192',
#     database='mcr30-2016',
#     user='ler',
#     password='Sii206744+',
# 	time_zone="UTC"
# )

# print("Connection ID:", db.connection_id)

# print(db)