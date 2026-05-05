import json
import os
import requests
import urllib.parse
from concurrent.futures import ThreadPoolExecutor, as_completed

# Paths
db_path = 'database.json'
icons_dir = './icons'
log_file = 'download_log.txt'

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

def download_image(item):
    url_name = item.get('imageUrl')  # still encoded
    id = item.get('id')

    if not url_name:
        print(f'No imageUrl for {id}')
        return True

    # ✅ Use encoded name for URL
    url = f'https://nicaos.com.br/icons/{url_name}'

    # ✅ Decode for local file
    local_name = urllib.parse.unquote(url_name)
    dest_path = os.path.join(icons_dir, local_name)

    try:
        resp = requests.get(url, timeout=10)
        if resp.status_code == 200:
            with open(dest_path, 'wb') as img_file:
                img_file.write(resp.content)
            return False
        else:
            print(f'Not found: {id} {url_name} ({url})')
            return True
    except Exception as e:
        print(f'Error downloading {id} {url_name}: {e}')
        return True

# Use ThreadPoolExecutor for concurrent downloads
max_workers = 16  # Adjust based on your system/network
with ThreadPoolExecutor(max_workers=max_workers) as executor:
    futures = [executor.submit(download_image, item) for item in data]
    for future in as_completed(futures):
        # only print if there was an error or not found
        if future.result():
            print(future.result())
