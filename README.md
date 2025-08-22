# Laravel File Watcher

![Laravel](https://img.shields.io/badge/Laravel-11.x-red)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)
![License](https://img.shields.io/badge/License-MIT-green)

A Laravel-based file system watcher that reacts to file events:

* JSON → POST contents to webhook
* TXT → Append Bacon Ipsum text
* JPG → Optimize for web
* ZIP → Extract & delete
* Any deletion → Replace with a meme

---

## 1) Clone & Install

```bash
git clone https://github.com/milendenev0912/laravel-file-watcher.git
cd laravel-file-watcher

composer install
composer require intervention/image:^3
```

---

## 2) Setup

Install required PHP extensions (Ubuntu example):

```bash
sudo apt update
sudo apt install -y php-xml php-gd php-zip php-curl
```

Create the watch directory:

```bash
mkdir -p storage/app/watch
```

Clear config & rebuild autoload:

```bash
php artisan config:clear
composer dump-autoload
```

---

## 3) Run

Start the watcher:

```bash
php artisan fs:watch
```

---

## 4) Quick Test

With the watcher running, try:

```bash
# JSON → triggers HTTP POST
echo '{"hello":"world"}' > storage/app/watch/test.json

# TXT → appends Bacon Ipsum
echo 'start' > storage/app/watch/notes.txt

# JPG → optimized
cp /path/to/image.jpg storage/app/watch/photo.jpg

# ZIP → extracted & deleted
zip -j storage/app/watch/archive.zip storage/app/watch/test.json

# Delete → replaced with meme
rm storage/app/watch/notes.txt
```
