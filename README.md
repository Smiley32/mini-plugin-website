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
 
 - **Framework + Website**: Installing the website is a bit harder since there isn't a stable version yet. You must download/clone the repository, change the `core/database.php` file to set your login and password for your database (mysql/mariadb). Then you can go in `plugins/database_plugin/export/Export.php` and get the SQL script to create the tables. Your database must be `utf8 unicode`. (If you have some reference errors, you can just remove the references). Next, you must add at least a default category, and a tag `tagme` in this category. Finally, you must create these folders: `data`, `data/thumbnails`, `data/posts`, `uploads` next to the index file. You must ensure that php can write in these folders.
 
 Feel free to add an issue, if you find a bug, or if you think of a new functionality to implement.
