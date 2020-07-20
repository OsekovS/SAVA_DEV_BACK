#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from sys import argv
import os.path
import fpdf
import json
import requests
from datetime import datetime

from sys import stdout

import matplotlib
from pylab import title, figure, xlabel, ylabel, xticks, bar, legend, axis, savefig

import matplotlib as mpl
import matplotlib.pyplot as plt
import matplotlib.dates as mdates



def elastic(direction, data):
	url = 'http://127.0.0.1:9200/' + direction + '/main/_search'
	headers = {'Content-Type': 'application/json'}
	r = requests.post(url, headers = headers, data = data.encode('utf-8'))
	return r.json()



def selectiontable(pdf, data):

	imagedir = './LogoAR.png'

	pdf.image(imagedir,11,10,0,10, 'PNG');

	pdf.set_font('Calibri', 'B', 14)
	pdf.set_text_color(128,128,128)
	text = '            SAVA СКУД'
	text = text + '                                                                                             '
	text = text + datetime.now().strftime("%d/%m/%Yг. %H:%M:%S") + '\n            '
	pdf.write(5, text)

	pdf.set_font('Calibri', '', 12)
	text = "Отчет о событиях устройств"
	pdf.write(5, text)

	countparam = len(data['params'])
	for i in range (countparam):

		pdf.set_font('Calibri', '', 12)
		pdf.set_text_color(0,0,0)
		text = '_________________________________________________________________________________________\n\n'
		pdf.write(5, text)

		pdf.set_font('Calibri', 'B', 14)
		pdf.set_text_color(128,128,128)

		text = data['params'][i]['type'] + '\n\n'

		pdf.write(5, text)

		pdf.set_font('Calibri', '', 12)
		pdf.set_text_color(0,0,0)
		text = 'Период:     ' + data['params'][i]['timerange']['starttime'] + ' — ' + data['params'][i]['timerange']['endtime'] + '\n'
		pdf.write(5, text)

		text = 'Фильтры:   '
		for f in data['params'][i]['filter']:
			pdf.set_font('Calibri', '', 12)
			pdf.set_text_color(0,0,0)
			text = text + '— ' + f + ': '
			pdf.write(5, text)
			text = ''

			countlistfilter = len(data['params'][i]['filter'][f])
			pdf.set_font('Calibri', 'I', 12)
			for arrf in range(countlistfilter):
				text = text + '"' + data['params'][i]['filter'][f][arrf]
				if arrf < countlistfilter - 1: text = text + '", '
				else: text = text + '"' + '\n' + '                     '
			pdf.write(5, text)
			text = ''

		text = text[0:-21]

		pdf.set_font('Calibri', '', 12)
		pdf.set_text_color(0,0,0)
		text = text + 'Сортировка: ' + data['params'][i]['grouping']['name']

		text = text + '\n'

		test = '{ "from":0,"size":1000, "query": { "bool": { "must": [ '

		for j in data['params'][i]['filter']:
			test = test + '{ "bool" : { "should": [ '
			for k in data['params'][i]['filter'][j]:
				#print(j + ' ' + k)
				test = test + '{ "match_phrase": { "' + j + '.keyword": "' + k + '" } }, '
			test = test[0:-2]
			test = test + ' ] } }, '
		test = test + '{ "range" : {  "time" : { "from": "2020/02/12 00:00:00",  "to": "2020/02/12 13:00:00" } } } ] } } , "sort": { "time": "asc" } }'

		print(test)

		datalog = elastic('acs_castle_ep2_event', test)

		#print(datalog)

		text = text + 'Найдено событий: ' + str(datalog['hits']['total']['value']) + '\n'
		pdf.write(5, text)

		for o in data['params'][i]['filter']['object']:

			pdf.set_font('Calibri', '', 12)
			text = '\nСобытия на объекте "' + o + '":\n'
			pdf.write(5, text)

			pdf.set_text_color(128,128,128)
			pdf.set_font('Calibri', 'B', 8)
			title = ['Дата', 'Объект', 'Конечная точка', 'Событие', 'Направление', 'Владелец']

			pdf.cell(8, pdf.font_size + 2, txt='№', border=1, align='C')
			pdf.cell(28, pdf.font_size + 2, txt='Дата', border=1, align='C')
			#pdf.cell(32, pdf.font_size + 2, txt='Объект', border=0)
			pdf.cell(30, pdf.font_size + 2, txt='Конечная точка', border=1, align='C')
			pdf.cell(75, pdf.font_size + 2, txt='Событие', border=1, align='C')
			pdf.cell(18, pdf.font_size + 2, txt='Направление', border=1, align='C')
			pdf.cell(30, pdf.font_size + 2, txt='Владелец', border=1, align='C')

			pdf.ln()
			pdf.set_font('Calibri', '', 8)
			pdf.set_text_color(0,0,0)
			num = 1
			for row in datalog['hits']['hits']:

				if row['_source']['object'] == o:
					pdf.set_font('Calibri', 'B', 8)
					pdf.set_text_color(128,128,128)
					pdf.cell(8, pdf.font_size + 2, txt=str(num), border=1, align='R')
					pdf.set_font('Calibri', '', 8)
					pdf.set_text_color(0,0,0)
					pdf.cell(28, pdf.font_size + 2, txt=row['_source']['time'], border=1,)
					#pdf.cell(32, pdf.font_size + 2, txt=row['_source']['object'], border=1)
					pdf.cell(30, pdf.font_size + 2, txt=row['_source']['device'], border=1)
					pdf.cell(75, pdf.font_size + 2, txt=row['_source']['event'], border=1)
					pdf.cell(18, pdf.font_size + 2, txt=row['_source']['route'], border=1)
					pdf.cell(30, pdf.font_size + 2, txt=row['_source']['person'], border=1)
					pdf.ln()
					num = num + 1

	return pdf




# json_string = argv[1]




def Created(json_string):

	# print(json_string)
	# data = json.loads(json_string)

	print('1 этап (начало) -> ок')

	pdf = fpdf.FPDF()
	dir = os.path.dirname(__file__)
	font = os.path.join(dir, 'Fronts', 'CALIBRI.TTF')
	pdf.add_font('Calibri', '', font, uni=True)
	font = os.path.join(dir, 'Fronts', 'CALIBRIB.TTF')
	pdf.add_font('Calibri', 'B', font, uni=True)
	font = os.path.join(dir, 'Fronts', 'CALIBRII.TTF')
	pdf.add_font('Calibri', 'I', font, uni=True)

	print('2 этап (шрифры) -> ок')

	pdf.add_page()
	pdf = selectiontable(pdf, json_string)
	
	print('3 этап (заполнение) -> ок')

	pdf.output('report.pdf', 'F')

	print('4 этап (save) -> ок')