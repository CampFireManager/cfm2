This is the code I'm developing for the new version of CampFireManager.

It is being developed on GitHub, as I would like to make it easy for people
to contribute to the project. Please feel free to have a dig about in here
and ask me any questions.

[Email Jon Spriggs](mailto:jon@sprig.gs?subject=CFM2)

Installation Guide
==================

Requirements
------------

Install:
* `Apache` (recommended - other HTTPd's not tested)
* `PHP` >= 5.3
* `PHP5-curl`, `PHP5-gmp` and `PHP5-mcrypt` (Required for OpenID Logins)
* `PHP5-mysql` or `PHP5-sqlite` (either will work, mysql is faster)
* `PHP5-imagick`
* `MySQL-Server` (if you're using the MySQL plugin)
* `git`

If you want to use or test the SMS integration, ensure you have the following:
* `gammu-smsd`
* `gammu-doc`

Actions
-------

Running the demo
----------------

* Ensure you have a recent version of Vagrant (2.0 or later)
* Clone the git repository into a known directory (e.g. /home/user/cfm2)
* Change directory into the "Vagrant" directory and run:
        `vagrant up`
* You can then access http://localhost:8080/cfm2 or http://localhost:8080/cfm2/main\_screen.php or http://localhost:8080/cfm2/direction\_screen

Using the installer
-------------------

* Clone the git repository into a known directory (e.g. `/var/www/`)
* Ensure you have the following packages installed in addition to the above requirements:
        `curl`
        `unzip`
* Run the following command (changing the upper-case-text for switches `-pw`, `-cpw`, `-gpw`, and `-w`):
        `php /var/www/cfm2/SETUP/install.php -u=root -pw=ROOT_DATABASE_PASSWORD -cu=cfm2 -cpw=CFM2_DATABASE_PASSWORD -gu=gammu -gpw=GAMMU_DATABASE_PASSWORD -w=YOUR_SITE_NAME -cd=cfm2 -ge=1 -te=0 -y=1 -gf=/usr/share/doc/gammu-smsd/examples/mysql.sql.gz -gt=mysql -gh=localhost -gp=3306`
* The installer assumes apache2 with .htaccess files enabled. If this is not the case (and you've not implemented a workaround in your web server of choice), the installer will complain.

Please note that the switches for the install command can be obtained by running:
        `php /var/www/cfm2/SETUP/install.php --help`
