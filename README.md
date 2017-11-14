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

1) Install plugin:

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

2) Check your instance's mode (by default it is slave)

3) Cron job installed automatically during plugin's installation. Please check it

4) If mode is ***master*** then check access to ssh's ***keys*** files

5) If mode is ***slave*** then check access to ssh's ***authorized_keys*** file (matched to master's ***keys*** files)

6) If mode is ***master*** then add remote hosts which have to be synchronized


Usage
---------------------------------

Plugin's configuration page is accessed at admin's dashboard:
`Site administration > Server > Replication`

Plugin's cron job configuration page is accessed at admin's dashboard:
`Site administration > Server > Scheduled tasks`
