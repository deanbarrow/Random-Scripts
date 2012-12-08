#!/usr/bin/python
"""count.py: get the number of social likes/shares from all the pages on your sitemap and export to a CSV file."""
__author__ = "Dean Barrow, http://deanbarrow.co.uk"
import urllib2
from BeautifulSoup import BeautifulSoup
from sys import argv
import json

if len(argv) == 2:
    script, sitemap = argv
else:
    print 'Usage: count.py [http://yoursite.com/sitemap.xml]'
    exit(0)

def Lookup(sitemap):
    req = urllib2.Request(sitemap)
    response = urllib2.urlopen(req)
    soup = BeautifulSoup(response.read())
    for url in soup.findAll('url'):
        loc = url.find('loc').string
        likes = getLikes(loc)
        tweets = getTweets(loc)
        plusones = getPlusOnes(loc)

        print("url:%s l:%s t:%s +:%s") % (loc, likes, tweets, plusones)
        global file
        file.write("%s, %s, %s, %s\n" % (loc, likes, tweets, plusones))

def getLikes(url):
    req = urllib2.Request("http://graph.facebook.com/?ids="+url)
    response = urllib2.urlopen(req)
    response = json.loads(response.read())
    try:
        return response[url]['shares']
    except:
        return 0

def getTweets(url):
    req = urllib2.Request("http://urls.api.twitter.com/1/urls/count.json?url="+url)
    response = urllib2.urlopen(req)
    response = json.loads(response.read())
    return int(response['count'])

def getPlusOnes(url):
    data = "[{'method':'pos.plusones.get','id':'p','params':{'nolog':true,'id':'%s','source':'widget','userId':'@viewer','groupId':'@self'},'jsonrpc':'2.0','key':'p','apiVersion':'v1'}]" % url
    content = {'Content-Type': 'application/json'}
    req = urllib2.Request('https://clients6.google.com/rpc', data, content)
    response = urllib2.urlopen(req)
    response = json.loads(response.read())
    return int(response[0]['result']['metadata']['globalCounts']['count'])

file = open('count.csv', 'a')
file.write("Page, Facebook Likes, Tweets, Plus Ones\n")
Lookup(sitemap)
file.close()
