#!/usr/bin/env python3

import socket
import time
import json

import pdfreport
import excelreport

import traceback
import sys

PORT = 9310


def tasks(string):

	try:
		data = json.loads(string)
	except:
		print('	ОШИБКА! Данные не переводятся в JSON')

	print('	JSON: ' + str(data))

	if  data['operation'] == 'create PDF':

		try:
			pdfreport.Created(data)
			return '200 OK'
		except Exception:
			print('	ОШИБКА! PDF не построен')
			e_t, e_v, e_tb = sys.exc_info()
			formated_error = traceback.format_exception(e_t, e_v, e_tb)
			print(str(formated_error))
			
			return '400 ERROR'

	elif  data['operation'] == 'create EXCEL':

		try:
			excelreport.Created(data)
			return '200 OK'
		except Exception:
			print('	ОШИБКА! EXCEL не построен')
			e_t, e_v, e_tb = sys.exc_info()
			formated_error = traceback.format_exception(e_t, e_v, e_tb)
			print(str(formated_error))
			
			return '400 ERROR'

	else:
		return '404 COMMAND NOT FOUND'


def listen(port):
	soc = socket.socket() # создаем объект "сокет"

	try:
		soc.bind(('', port)) # НАСТРОЙКИ! задаем ему параметры
	except:
		print('ОШИБКА! C прошлого раза порт остался закрыт. Поменяй PORT и выходи из программы ctl+C') # ошибка, бывает!

	soc.listen(1) # НАСТРОЙКИ! слушаем 1 клиента за раз

	print('Сервер запущен!')

	while True: # бесконечный цикл для прослушки порта
		print() # просто пустая строка, для читаемости

		conn, addr = soc.accept() # ждем пока на порт что-то придет

		print('Установленно соединение, ip: ' + str(addr[0]) + ', port: ' + str(addr[1])) # addr содержит ip и port клиента

		while True: # цикл для того что бы взять ВСЕ данные
			data = conn.recv(1024) # берем ответ пакетами по 1024 байта
			if not data:
				break # если данных нет - нничего не возвращаем

			# ПОЛУЧАЕМ ДАННЫЕ
			try:
				print('	Полученные данные: ' + data.decode('utf-8')) # данные из байт переводим в utf-8
			except:
				print('	ОШИБКА! Данные почему-то нельзя прочесть') # ошибка

			resp = tasks(data.decode('utf-8')) 

			# ОТПРАВЛЯЕМ ОТВЕТ
			conn.send(bytes(resp, 'utf-8')) # Отправляем ответ its OK
			print('	Ответ отправлен') # Говорим об этом

		conn.close() # закрываем соединение
		print('Cоединение закрыто') # И снова говорим об этом!

listen(PORT)