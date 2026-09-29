import urllib.request, re, urllib.parse
query = urllib.parse.quote('Motul 7100 10W40 Synthetic Engine Oil')
req = urllib.request.Request('https://images.search.yahoo.com/search/images?p='+query, headers={'User-Agent':'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
try:
    html = urllib.request.urlopen(req, timeout=5).read().decode('utf-8', errors='ignore')
    matches = re.findall(r'<img src=\'(https://tse.*?)\'', html)
    print("Found images:", len(matches))
    for m in matches[:5]:
        print(m)
except Exception as e:
    print("ERROR:", e)
