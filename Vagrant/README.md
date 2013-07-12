Just to note, you don't need Vagrant and Puppet to test or run CampFireManager, this is just
something for those of us making frequent changes.

If you want to just do it all without, make sure you've got the following packages:
php5, php5-cli, php5-mysql, php5-mcrypt, php5-gmp, php5-curl, php5-imagick, git, mysql-server

Then, run:
php /path/to/cfm2/SETUP/install.php --user=root --pass= --coreuser=cfm2 -corepass=cfm2 --gammu=0 --twitter=0

(If you omit the --gammu=0, ensure you have both the gammu and gammu-smsd packages installed)

Job should be a good'n.