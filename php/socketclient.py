#!/usr/bin/env python3

import socket
import time

PORT = 9310

#string = '{"operation":"create PDF","params":[{"timerange":{"starttime":"2019/04/16 00:00:00","endtime":"2020/04/16 11:40:00"},"filter":{},"grouping":{"name":"по объектам","argument":"device"},"type":"Отчет с 16.04.2019 00:00 по 16.04.2020 11:40","indexName":"acs_castle_ep2_event"}]}'

string = '{"operation":"create EXCEL","params":{"paramstable":[{"timerange":{"starttime":"2019/04/16 00:00:00","endtime":"2020/04/16 11:40:00"},"filter":{},"field":["object","pass_number","time","route","person","significance","event","ip_device","device"],"grouping":{"field":"time", "trend":"asc"},"pagename":"Отчет с 16.04.2019","indexname":"acs_castle_ep2_event"}],"filename":"Оdas с 20.01.2020"}}'


def listen(port, string):

	soc = socket.socket() # создаем объект "сокет"

	try:
		soc.connect(('localhost', port)) # НАСТРОЙКИ! конектимся к сокету сервера по его порту
	except:
		print('Сервер не найден, проверте ip и PORT')

	# ОТПРАВЛЯЕМ ДАННЫЕ
	soc.send(bytes(string, 'utf-8')) # отправляем наше сообщение

	# ПОЛУЧАЕМ ОТВЕТ
	data = soc.recv(1024) # берем ответ пакетами по 1024 байта
	
	try:
		print('Ответ сервера: ' + data.decode('utf-8')) # печатаем ответ
	except:
		print('ОШИБКА! Ответ почему-то нельзя прочесть') # ошибка

	soc.close() # закрываем соединение

listen(PORT, string)