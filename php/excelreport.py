#!/usr/bin/env python3

import os, sys
from openpyxl import Workbook
from datetime import datetime


import json
import requests



def cellname(row, column):
	letter = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V']
	return letter[column] + str(row+1)



class Excel(object):


	def __init__(self):
		self.wb = Workbook()


	def addpage(self, name):
		sheet = self.wb.active
		sheet.title = name
		return sheet


	def selectiontable(self, sheet, title, data, startrow=0, startcolumn=0):

		# Добавить заголовки в рабочую книгу Excel:
		for i in range(len(title)):
			sheet[cellname(startrow, startcolumn+i)] = title[i]

		#print(data[0])
		for j in range(len(data)):
			for k in range(len(data[0])):
				sheet[cellname(startrow+j+1,startcolumn+k)] = data[j][k]

	def save(self, filename):
		self.wb.save(filename + '.xlsx')



def elastic(direction, data):
	url = 'http://127.0.0.1:9200/' + direction + '/main/_search'
	headers = {'Content-Type': 'application/json'}
	r = requests.post(url, headers = headers, data = data.encode('utf-8'))

	return r.json()



def generatedjson(data):
	starttime = data['timerange']['starttime']
	endtime = data['timerange']['endtime']
	field = data['grouping']['field']
	trend = data['grouping']['trend']

	# НАЧАЛО СТРОКИ
	text = '{ "from":0, "size":9999, "query": { "bool": { "must": [ '

	# ФИЛЬТРЫ
	for j in data['filter']:
		text = text + '{ "bool" : { "should": [ '
		for k in data['filter'][j]:
			text = text + '{ "match_phrase": { "' + j + '.keyword": "' + k + '" } }, '
		text = text[0:-2]
		text = text + ' ] } }, '

	# ВРЕМЯ
	text += '{ "range" : {  "time" : { "from": "' + starttime + '",  "to": "' + endtime + '" } } } ] } } , '
	text += '"sort": { "' + field + '": "' + trend + '" } }'
	
	return text



def Created(jsonstring):

	# title = [{'name':'object', 'key':'object'}, {'key':'pass_number'}, {'key':'time'}, {'key':'route'}, {'key':'person'}, {'key':'significance'}, {'key':'event'}, {'key':'ip_device'}, {'key':'device'}]
	# title = ['object', 'pass_number', 'time', 'route', 'person', 'significance', 'event', 'ip_device', 'device']
	# string = '{"operation":"create EXCEL","params":[{"timerange":{"starttime":"2019/04/16 00:00:00","endtime":"2020/04/16 11:40:00"},"filter":{"person":["Артем Артишев"]},"field":["object","pass_number","time","route","person","significance","event","ip_device","device"],"grouping":{"name":"по объектам","argument":"device"},"pagename":"Отчет с 16.04.2019","indexname":"acs_castle_ep2_event"}]}'

# {"operation":"create EXCEL","params":{"paramstable":[{"timerange":{"starttime":"2019/01/29 00:00:00","endtime":"2020/05/18 16:34:38"},
# "filter":{"significance":["Критическая ситуация"]},"field":["significance","hostname","etdn","et","hdn","hip","tdn","p1","p2",
# "p3","p4","p5","p6","p7","p8","time"],"grouping":{"field":"hip","trend":"asc"},"pagename":"1","indexname":"ksc"}],"filename":"1"}}
#         JSON: {'operation': 'create EXCEL', 'params': {'paramstable': [{'field': ['significance', 'hostname', 'etdn', 'et', 'hdn', 'hip', 'tdn', 'p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'time'], 'pagename': '1', 'timerange': {'endtime': '2020/05/18 16:34:38', 'starttime': '2019/01/29 00:00:00'}, 'filter': {'significance': ['Критическая ситуация']}, 'indexname': 'ksc', 'grouping': {'field': 'hip', 'trend': 'asc'}}], 'filename': '1'}}


	# string = '{"operation":"create EXCEL","params":{"paramstable":[{"timerange":{"starttime":"2019/04/16 00:00:00",
	# "endtime":"2020/04/16 11:40:00"},"filter":{"person":["Артем Артишев"]},"field":["object","pass_number","time","route",
	# "person","significance","event","ip_device","device"],"grouping":{"field":"time", "trend":"asc"},
	# "pagename":"Отчет с 16.04.2019","indexname":"acs_castle_ep2_event"}],"filename":"Отчет с 16.04.2019"}}'
	# jsonstring = json.loads(string)

	newfile = Excel();

	for m in jsonstring['params']['paramstable']:
		text = generatedjson(m)
		title = m['field']

		datalog = elastic(m['indexname'], text)
		data = []

		for row in datalog['hits']['hits']:
			datacolumn = []
			for n in range(len(title)):
				datacolumn.append(row['_source'][title[n]])
			data.append(datacolumn)

		sheet = newfile.addpage(m['pagename'])
		newfile.selectiontable(sheet, title, data)

	newfile.save(jsonstring['params']['filename'])