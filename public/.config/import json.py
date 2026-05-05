import json
import os
import requests
import urllib.parse

# Paths
db_path = 'database.json'
icons_dir = './icons'

# Ensure icons directory exists
os.makedirs(icons_dir, exist_ok=True)

# Load database.json
with open(db_path, 'r', encoding='utf-8') as f:
    data = json.load(f)
    
# empty icons directory
for filename in os.listdir(icons_dir):
    file_path = os.path.join(icons_dir, filename)
    if os.path.isfile(file_path):
        os.remove(file_path)

# Download images
for item in data:
    name = item.get('itemUrl')
    id = item.get('id')
    if not name:
        continue
    # url_name = urllib.parse.quote(name)
    url = f'https://nicaos.com.br/icons/{name}'
    dest_path = os.path.join(icons_dir, f'{name}')
    try:
        resp = requests.get(url, timeout=10)
        if resp.status_code == 200:
            with open(dest_path, 'wb') as img_file:
                img_file.write(resp.content)
            print(f'Downloaded: {id} {name}')
        else:
            print(f'Not found: {id} {name} ({url})')
            break
    except Exception as e:
        print(f'Error downloading {id} {name}: {e}')
        break
    # input('Press Enter to continue...')
    