# Readme

This repository contains a mini-framework, and some plugins for it.

## Documentation:
https://github.com/Smiley32/mini-plugin-website/wiki

---

This framework is based on **plugins**: the goal is to only use plugins to create a website. Then, it's easy to reuse each plugins for different websites.

Each plugins has an **MVC architecture**, that makes even simpler the fact to reuse the plugin by modifying only the view.

For now, some files are not in plugins (layout files, global css or global scripts). It will probably soon be in a plugin that will be the entry point / layout of the website.

---

The *example* website is a content browser, but the framework can be used to create any website.

## Installation:

 - **Framework**: The framework is very easy to install: just download/clone the repository in a folder and you have it. The framework doesn't require anything (only tested on php7 and apache). You may use any database you want. If you don't use apache server, you may need to rewrite the .htaccess to have nice urls.
 
 - **Framework + Website**: To install the website, you must have a mysql/mariaDB database. First, you'll need to create a new database (you choose the name you prefer), in utf8_unicode_ci. Then you must edit the file `core/Database.php` and change the database connection line with your informations. Finally, you just have to exec the file `install.php` (`localhost/your-website/install.php` or something similar).
 
Warning: **in every case, you must delete the `install.php` file**. *You may also want to delete the line `RewriteCond %{REQUEST_URI} !install\.php$` from `.htaccess`*.
 
 Feel free to add an issue, if you find a bug, or if you think of a new functionality to implement.
