node default {
    Exec {
        path => '/usr/local/bin:/bin:/usr/bin:/home/vagrant/bin:/usr/sbin:/sbin'
    }

    file { "/etc/timezone":
        content => "Europe/London\n",
    }

    exec { "timezonesetup":
        command => "dpkg-reconfigure -f noninteractive tzdata",
        require => File["/etc/timezone"]
    }

    exec { "Generate UK locale Data":
        command => "sudo locale-gen en_GB",
    }

    exec { "Use UK locale Data":
        command => "sudo update-locale LANG=en_GB",
        require => Exec['Generate UK locale Data']
    }

    exec { "use apt-mirrors":
        unless => "grep 'mirror://' /etc/apt/sources.list",
        command => "sudo sed -i -e 's/http:\\/\\/us.archive.ubuntu.com\\/ubuntu\\//mirror:\\/\\/mirrors.ubuntu.com\\/mirrors.txt/g' /etc/apt/sources.list",
        require => Exec['timezonesetup']
    }

    # Pah, Grub-pc has been upgraded. We need to fix this!
    # Fix found here: http://serverfault.com/questions/310488/unattended-grub-pc-update
    exec { "bypass grub-pc request 1" :
        command => "echo grub-pc grub-pc/install_devices multiselect `find /dev/disk/by-id/ata-VBOX_HARDDISK* | grep -v part[0-9]` | sudo debconf-set-selections -",
        require => Exec['use apt-mirrors']
    }

    exec { "bypass grub-pc request 2" :
        command => "echo grub-pc grub-pc/install_devices_disks_changed multiselect `find /dev/disk/by-id/ata-VBOX_HARDDISK* | grep -v part[0-9]` | sudo debconf-set-selections -",
        require => Exec['bypass grub-pc request 1']
    }

    exec { "apt-get upgrade":
        command => "sudo apt-get update && sudo apt-get upgrade -y",
        require => Exec['use apt-mirrors', 'bypass grub-pc request 2'],
        timeout => 0
    }

    package { "python-software-properties":
        ensure => present,
	require => Exec['apt-get upgrade']
    }

    package { "mysql-server": 
        ensure => "installed",
	require => Exec['apt-get upgrade']
    }

    service { "mysql":
        enable => true,
        ensure => running,
        require => Package["mysql-server"],
    }

    $packages = [
        "curl",
        "apache2",
        "php5", 
        "php5-dev",
        "php5-cli",
        "php5-mysql",
        "libapache2-mod-php5",
        "php-pear",
        "php5-mcrypt",
        "php5-gmp",
        "php5-curl",
        "php5-imagick",
        "git",
        "unzip"
    ]

    package { $packages:
        ensure => "installed",
        require => Package["mysql-server"]
    }

    exec { "bypass phpmyadmin webserver" :
        command => "echo phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2 | sudo debconf-set-selections -",
        require => Package['php5']
    }

    package { 'phpmyadmin':
	ensure => "installed",
	require => Exec['bypass phpmyadmin webserver']
    }

    file { "/var/www/index.html":
	ensure => absent
    }

    exec { "enable rewrite":
        command => "sudo a2enmod rewrite",
        require => Package["apache2"]
    }

    exec { "Apache Allow Override":
        command => 'sudo cat /etc/apache2/sites-available/default | awk \'/<Directory \/var\/www\/>/,/AllowOverride None/{sub("None", "All", $0)}{print}\' | sudo tee /etc/apache2/sites-available/default',
        require => Package["apache2"]
    }

    exec { "PHPMyAdmin Allow Passwordless Login":
        unless => "grep \"\\['Servers'\\]\\[1\\]\\['Allow\" /etc/phpmyadmin/config.inc.php",
        command => "echo \"\\\$cfg['Servers'][1]['AllowNoPassword'] = TRUE;\"| sudo tee -a /etc/phpmyadmin/config.inc.php",
        require => Package["phpmyadmin"]
    }

    exec { "restart apache":
        command => "sudo service apache2 reload",
        require => Exec["enable rewrite", "install cfm2", "start cfm2 daemons", "Apache Allow Override"]
    }

    exec { "install cfm2":
        unless => "/usr/bin/mysql -ucfm2 -p\"cfm2\" cfm2",
        command => "php /var/www/SETUP/install.php -u=root -pw= -cu=cfm2 -cpw=cfm2 -cd=cfm2 -ge=0 -te=0 -y=1",
        require => Package["mysql-server", "php5-cli"]
    }

    exec { "start cfm2 daemons":
        onlyif => "/usr/bin/mysql -ucfm2 -p\"cfm2\" cfm2",
        command => "nohup php -q /var/www/cron.php &",
        require => Package["mysql-server", "php5-cli"]
    }
}
