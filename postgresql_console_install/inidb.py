#!/usr/bin/env python3
import psycopg2
#import psql

def connector(name, user, pas):
	connect = psycopg2.connect(     # попытка коннекта
	database = name,                # имя бд
	user = user,                    # пользователь бд
	password = pas,                 # пароль от бд
	host = "127.0.0.1",             # хост на котором лежит бд
	port = "5432"                   # порт от бд
	)
	connect.autocommit = True
	return connect


def command(i):


	if (line[i].rstrip() == "create user"):

		param = line[i+1].rstrip().split(' / ')
		connect = connector("postgres", "postgres", "Arinteg123!")
		cursor = connect.cursor()

		print("СОЗДАНИЕ ЮЗЕРА " + param[0] + "...")

		try:
			cursor.execute("CREATE USER " + param[0] + " WITH password '" + param[1] + "'")
			print("  OK  /  CREATE USER " + param[0])
		except:
			print("  ОШИБКА  /  Юзер уже создан")

		connect.close()
		return (i+2)

	elif (line[i].rstrip() == "create db"):

		param = line[i+1].rstrip().split(' / ')
		connect = connector("postgres", "postgres", "Arinteg123!")
		cursor = connect.cursor()

		print("СОЗДАНИЕ БАЗЫ " + param[0] + "...")

		try:
			cursor.execute("CREATE DATABASE " + param[0])
			#cursor.execute("GRANT ALL privileges ON DATABASE " + param[0] + " TO " + param[1])
			print("  OK  /  CREATE DATABASE " + param[0])

		except:
			print("  ОШИБКА  /  База уже создана")

		connect.close()
		return (i+2)

	elif (line[i].rstrip() == "create table"):

		param = line[i+1].rstrip().split(' / ')
		connect = connector(param[1], "postgres", "Arinteg123!")
		cursor = connect.cursor()

		s = i+2
		string = ""
		while line[s] != "":
			string = string + line[s]
			s = s+1

		print("СОЗДАНИЕ ТАБЛИЦЫ " + param[0] + " в базе " + param[1] + "...")

		try:
			cursor.execute("CREATE TABLE " + param[0] + " (" + string + ");")
			connect.commit()
			cursor.execute("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO " + param[2])
			connect.commit()
			print("  OK  /  CREATE TABLE " + param[0])
		except:
			print("  ОШИБКА  /  Таблица уже создана")

		connect.close()
		return (s+1)

	elif (line[i].rstrip() == "insert db"):

		param = line[i+1].rstrip().split(' / ')
		connect = connector(param[1], "postgres", "Arinteg123!")
		cursor = connect.cursor()

		print("СОЗДАНИЕ ЗАПИСИ в таблице " + param[0] + "...")

		try:
			cursor.execute("INSERT INTO " + param[0] + " (" + line[i+2] + ") VALUES (" + line[i+3] + ")")
			connect.commit()
			print("  OK  /  INSERT INTO " + param[0])
		except:
			print("  ОШИБКА")

		connect.close()
		return (i+4)
	elif (line[i].rstrip() == "drop table"):
		# drop table
		# dashboards / sava_core / sava_user
		param = line[i+1].rstrip().split(' / ')
		connect = connector(param[1], "postgres", "Arinteg123!")
		cursor = connect.cursor()

		print("Удаление таблицы " + param[0] + "...")

		try:
			cursor.execute("DROP TABLE " + param[0])
			connect.commit()
			print("  OK  /  DROP TABLE " + param[0])
		except:
			print("  ОШИБКА")

		connect.close()
		return (i+4)
	else:
		return (i+1)

f = open('db_create.txt', 'r')
line = [strline.rstrip() for strline in f]

i = 0
while i < len(line):
	i = command(i)