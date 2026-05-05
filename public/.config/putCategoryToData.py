import json
import os
import time
import signal
import sys
from urllib.parse import quote
import requests
import webbrowser

# launch : rm -rf /var/www/html/dev-wsl/Terraria-progress-tracker-\&-chat/img && mkdir /var/www/html/dev-wsl/Terraria-progress-tracker-\&-chat/img && /usr/bin/python3 /var/www/html/dev-wsl/Terraria-progress-tracker-\&-chat/public/putCategoryToData.py

ITEM_FILE = "public/item.json"
OUTPUT_JSON = "public/items_with_urls.json"
CATEGORY_TREE_FILE = "public/category_tree.json"
PROGRESS_FILE = "public/progress_temp.json"
IMG_ROOT = "img"

os.makedirs(IMG_ROOT, exist_ok=True)


def load_category_tree():
    if os.path.exists(CATEGORY_TREE_FILE):
        with open(CATEGORY_TREE_FILE, "r", encoding="utf-8") as f:
            return json.load(f)
    return {}

def save_category_tree(tree):
    with open(CATEGORY_TREE_FILE, "w", encoding="utf-8") as f:
        json.dump(tree, f, indent=4)

category_tree = load_category_tree()

def save_progress(current_index, items_data):
    progress_data = {
        "current_index": current_index,
        "category_tree": category_tree,
        "items_data": items_data
    }
    with open(PROGRESS_FILE, "w", encoding="utf-8") as f:
        json.dump(progress_data, f, indent=4)
    print("\n[Progress Saved]")

def load_progress():
    if os.path.exists(PROGRESS_FILE):
        with open(PROGRESS_FILE, "r", encoding="utf-8") as f:
            return json.load(f)
    return None

def populate_items_in_tree(items_data, category_tree):
    """
    For each item in items_data, traverse the category_path in the tree
    and add the item's name to the _items list of the final category.
    """
    for item in items_data: 
        current_level = category_tree
        for cat in item["categories"]:
            if cat not in current_level:
                current_level[cat] = {}
            current_level = current_level[cat]

        if "_items" not in current_level:
            current_level["_items"] = []

        # Avoid duplicates
        if item["name"] not in current_level["_items"]:
            current_level["_items"].append(item["name"])

def clear_progress():
    if os.path.exists(PROGRESS_FILE):
        os.remove(PROGRESS_FILE)


def handle_exit(signum, frame):
    print("\nDetected interruption! Saving progress...")
    save_progress(current_index, result_data)
    save_category_tree(category_tree)
    sys.exit(0)

signal.signal(signal.SIGINT, handle_exit)


class Item:
    def __init__(self, internalName, id, neededForResearch, name):
        self.internalName = internalName
        self.id = id
        self.neededForResearch = neededForResearch
        self.name = name
        self.url = self.create_url()
        self.category_path = self.get_category_path()
        self.local_path = None

    def create_url(self):
        base_url = "https://terraria.wiki.gg/images/5/5b/"
        item_name = quote(self.name.replace(" ", "_"), safe="_")
        return f"{base_url}{item_name}.png"

    def get_category_path(self):
        global category_tree

        current_level = category_tree
        path = []
        
        self.open_wiki_page()  # Open the wiki page for the item to help the user decide on categories

        while True:
            os.system('cls' if os.name == 'nt' else 'clear')

            keys = [k for k in current_level.keys() if k != "_items"]
            
            length = 0 
            
            if len(keys) < 5: 
                length = len(keys) 
            else: 
                length = 5


            print(f"Current category level: {' > '.join(path) if path else 'Root'}")
            print(f"Subcategories at this level:")
            if keys:
                for _ in range(length):
                    print("+" + "-" * 30, end="")
                print("+")
                print("|", end="")
                for idx, cat in enumerate(keys, start=1):
                    print(f"{idx:3}. {cat:25}", end="|")
                    if idx % 5 == 0:
                        print()
                        for _ in range(5):
                            print("+" + "-" * 30, end="")
                        print("+")
                        if idx != len(keys):
                            print("|", end="")
                print()
                for _ in range(len(keys) % 5):
                    print("+" + "-" * 30, end="")
                if len(keys) % 5 != 0:
                    print("+")  
            else:
                print("No subcategories at this level.")

            print()
            current_items = current_level.get("_items")
            if current_items:
                print(f"Items in this category:")
                length = min(len(current_items), 5)
                for _ in range(length):
                    print("+" + "-" * 30, end="")
                print("+")
                print("|", end="")
                for idx, cat in enumerate(current_items, start=1):
                    print(f"{idx:3}. {cat:25}", end="|")
                    if idx % 5 == 0:
                        print()
                        for _ in range(5):
                            print("+" + "-" * 30, end="")
                        print("+")
                        if idx != len(current_items):
                            print("|", end="")
                print()
                for _ in range(len(current_items) % 5):
                    print("+" + "-" * 30, end="")
                if len(current_items) % 5 != 0:
                    print("+")
            print()

            print(f"Assign categories for {self.name} (ID: {self.id})")
            print(f"Current categories: {' > '.join(path) if path else '-'}")
            print("ENTER = finish | '..' = go back one level | Number = choose category | Type = create new category\n")
            choice = input(" > ").strip()

            if choice == "":
                break

            if choice == "..":
                if path:
                    path.pop()
                    current_level = category_tree
                    for p in path:
                        current_level = current_level[p]
                continue

            if choice.isdigit():
                choice_num = int(choice)
                if 1 <= choice_num <= len(keys):
                    selected = keys[choice_num - 1]
                    path.append(selected)
                    current_level = current_level[selected]
                continue

            matches = [k for k in current_level.keys() if k.lower().startswith(choice.lower()) and k != "_items"]
            if len(matches) == 1:
                path.append(matches[0])
                current_level = current_level[matches[0]]
                continue

            current_level[choice] = {}
            path.append(choice)
            current_level = current_level[choice]

        if "_items" not in current_level:
            current_level["_items"] = []
        current_level["_items"].append(self.name)

        return path
    
    def get_wiki_url(self):
        base_url = "https://terraria.wiki.gg/wiki/"
        item_name = quote(self.name.replace(" ", "_"), safe="_")
        return f"{base_url}{item_name}"

    def open_wiki_page(self):
        url = self.get_wiki_url()
        # Detect WSL and open in Windows browser if needed
        if "WSL" in os.uname().release:
            os.system(f'powershell.exe start "{url}"')
        else:
            webbrowser.open(url)


with open(ITEM_FILE, "r", encoding="utf-8") as f:
    raw_items = json.load(f)
    
with open(PROGRESS_FILE, "r", encoding="utf-8") as f:
    items_data = json.load(f)

if items_data:
    populate_items_in_tree(list(items_data.items())[2][1], category_tree)

save_category_tree(category_tree)

print("All items have been added to _items in the category tree!")

progress = load_progress()

if progress:
    # choice = input("Previous progress found. Resume? (y/n): ").lower()
    choice = "y"
    if choice == "y":
        category_tree = load_category_tree()
        result_data = progress["items_data"]
        start_index = progress["current_index"]
    else:
        clear_progress()
        result_data = []
        start_index = 0
else:
    result_data = []
    start_index = 0

items = raw_items
current_index = start_index
item_objects = []


for idx in range(start_index, len(items)):
    current_index = idx
    os.system('cls' if os.name == 'nt' else 'clear')

    item = Item(**items[idx])
    item_objects.append(item)

    result_data.append({
        "internalName": item.internalName,
        "id": item.id,
        "neededForResearch": item.neededForResearch,
        "name": item.name,
        "categories": item.category_path,
        "url": item.url,
        "localPath": None
    })

    save_progress(current_index + 1, result_data)

print("\nAll items categorized. Creating folders and downloading images...")

for idx, item in enumerate(item_objects):
    folder = IMG_ROOT
    for level in item.category_path:
        folder = os.path.join(folder, level)
    os.makedirs(folder, exist_ok=True)
    local_path = os.path.join(folder, f"{item.name.replace(' ', '_')}.png")
    item.local_path = local_path

    result_data[idx]["localPath"] = local_path

    headers = {
        "User-Agent": (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/120.0.0.0 Safari/537.36"
        )
    }

    for attempt in range(3):
        try:
            resp = requests.get(item.url, headers=headers, timeout=10)
            resp.raise_for_status()
            with open(local_path, "wb") as f:
                f.write(resp.content)
            print(f"Downloaded {item.name}")
            # little delay to not kill the requests
            time.sleep(1)
            break
        except Exception as e:
            print(f"Attempt {attempt+1} failed for {item.name}: {e}")
            time.sleep(1)

with open(OUTPUT_JSON, "w", encoding="utf-8") as out:
    json.dump(result_data, out, indent=4)

save_category_tree(category_tree)
# clear_progress()

print(f"\nAll done! JSON saved to {OUTPUT_JSON}")
print("Category tree saved. Progress file cleared.")
