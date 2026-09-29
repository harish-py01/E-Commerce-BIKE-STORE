import asyncio
import mysql.connector
import os
import urllib.request
import re
from playwright.async_api import async_playwright

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'bike_store'
}

IMG_DIR = 'assets/images/products'

def sanitize_filename(name):
    name = re.sub(r'[^a-zA-Z0-9 ]', '', name)
    return name.strip().replace(' ', '_').lower()

async def fetch_image_with_playwright(query, page):
    try:
        # We will use duckduckgo image search
        print(f"Searching DuckDuckGo for: {query}")
        search_url = f"https://duckduckgo.com/?q={urllib.parse.quote(query)}&iax=images&ia=images"
        await page.goto(search_url, wait_until="domcontentloaded", timeout=15000)
        
        # Wait for the image tile to appear
        await page.wait_for_selector("div.tile--img", timeout=10000)
        
        # Get the first image src
        img_src = await page.evaluate('''() => {
            const img = document.querySelector('div.tile--img img.tile--img__img');
            return img ? img.src : null;
        }''')
        
        if img_src and img_src.startswith('//'):
            img_src = 'https:' + img_src
            
        return img_src
    except Exception as e:
        print(f"Error fetching {query}: {e}")
        return None

def download_image(url, save_path):
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=10) as response:
            with open(save_path, 'wb') as f:
                f.write(response.read())
        return True
    except Exception as e:
        print(f"Error downloading {url}: {e}")
    return False

async def main():
    if not os.path.exists(IMG_DIR):
        os.makedirs(IMG_DIR)

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)

    cursor.execute("SELECT id, name FROM products")
    products = cursor.fetchall()

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36")
        page = await context.new_page()
        
        for product in products:
            p_id = product['id']
            p_name = product['name']
            
            print(f"Processing: {p_name}")
            query = f"{p_name} motorcycle spare part white background"
            
            img_url = await fetch_image_with_playwright(query, page)
            if img_url:
                print(f"Found image: {img_url}")
                ext = '.jpg'
                if '.png' in img_url.lower(): ext = '.png'
                
                filename = sanitize_filename(p_name) + ext
                save_path = os.path.join(IMG_DIR, filename)
                
                if download_image(img_url, save_path):
                    db_path = f"assets/images/products/{filename}"
                    cursor.execute("UPDATE products SET image = %s WHERE id = %s", (db_path, p_id))
                    conn.commit()
                    print(f"Saved and updated DB for {p_name}")
                else:
                    print(f"Failed to download image for {p_name}")
            else:
                print(f"No image found for {p_name}")
                
            await asyncio.sleep(2) # be nice to the server
            
        await browser.close()
        
    cursor.close()
    conn.close()

if __name__ == "__main__":
    asyncio.run(main())
