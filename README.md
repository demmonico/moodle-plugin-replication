Replication plug-in for Moodle LMS
=================================

This plugin will transfer this database contents and Moodle files to instance's server. It is could used for deploy and synchronization multi-instances Moodle applications.


Requirements
---------------------------------

Minimum requirements:

* Moodle version >= 3.3.2 (2017051502)
* PHP version >= 5.6.5
* RDBMS: MariaDB / MySQL >= 5.5.31


Installation
---------------------------------

You can install plugin:

* Using git
```
// using git
cd PROJECT_DIR/admin/tool
git clone https://repo.name replication
```
* Using zip-archive with plugin code

```
// using zip
cd PROJECT_DIR/admin/tool
mkdir replication
wget https://file.name.zip
unzip file.name.zip -d replication
```
To learn how to install plugins as zip-archive follow 
the link https://docs.moodle.org/33/en/Installing_plugins#Installing_via_uploaded_ZIP_file


Usage
---------------------------------

Configuration page is accessed at admin's dashboard:
`Site administration > Server > Replication`
