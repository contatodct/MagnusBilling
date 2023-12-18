#!/bin/bash
clear
printf "\n"

  printf "${GREEN}";
  printf "===================================================\n";
  printf "##                                               ##\n";
  printf "##      ███████         █████████   █████████    ##\n";
  printf "##      ███    ██       ███            ███       ##\n";
  printf "##      ███    ███      ███            ███       ##\n";
  printf "##      ███    ███      ███            ███       ##\n";
  printf "##      ███    ██       ███            ███       ##\n";
  printf "##      ███████         █████████      ███       ##\n";
  printf "##                                               ##\n";
  printf "##    ESSE MATERIAL FAZ PARTE DA DCT SISTEMAS    ##\n";
  printf "##                                               ##\n";
  printf "##         PIRATEAR ESSA SOLUÇÃO É CRIME.        ##\n";
  printf "##                                               ##\n";
  printf "##    © DCT-SISTEMAS  -  www.dctsistemas.com     ##\n";
  printf "##                                               ##\n";
  printf "===================================================\n";
  printf "\n"; 


sleep 3


if [[ -f /var/www/html/mbilling/index.php ]]; then
  echo "This server already has MagnusBilling installed";
  exit;
fi
# Linux Distribution CentOS or Debian
get_linux_distribution ()
{ 
    if [ -f /etc/debian_version ]; then
        DIST="DEBIAN"
        HTTP_DIR="/etc/apache2/"
        HTTP_CONFIG=${HTTP_DIR}"apache2.conf"
        MYSQL_CONFIG="/etc/mysql/mariadb.conf.d/50-server.cnf"
    elif [ -f /etc/redhat-release ]; then
        DIST="CENTOS"
        HTTP_DIR="/etc/httpd/"
        HTTP_CONFIG=${HTTP_DIR}"conf/httpd.conf"
        MYSQL_CONFIG="/etc/my.cnf"
    else
        DIST="OTHER"
        echo 'Installation does not support your distribution'
        exit 1
    fi
}



get_linux_distribution

startup_services() 
{
    # Startup Services
    if [ ${DIST} = "DEBIAN" ]; then
        systemctl restart mysql
        systemctl restart apache2
        systemctl restart asterisk
    elif  [ ${DIST} = "CENTOS" ]; then
        systemctl restart mariadb
        systemctl restart httpd
        systemctl restart asterisk    
    fi
}


set_timezone ()
{ 
  #yum -y install ntp
  directory=/usr/share/zoneinfo
  for (( l = 0; l < 5; l++ )); do

    echo "entrar no diretorio $directory"
    cd $directory
    files=("")  

    i=0
    s=65    # decimal ASCII "A" 
    for f in *
    do

      if [[ "$i" = "0" && "$l" = "0" ]]; then
        files[i]="BRASIL Brasilia"
        files[i+1]=""
      else
        files[i]="$f"
          files[i+1]=""
      fi      
        ((i+=2))
        ((s++))
    done

    files[i+1]="MAIN MENU"
    files[i+2]="Back to main menu"

    zone=$(whiptail --title "Restore Files" --menu "Please select your timezone" 20 60 12 "${files[@]}" 3>&1 1>&2 2>&3)


    if [ "$zone" = "BRASIL Brasilia" ]; then
      echo "é um arquivo, setar timezone BRASIL"
      directory=$directory/America/Sao_Paulo  
      break
    fi

    directory=$directory/$zone


    if [ -f "$directory" ]; then
      #echo "é um arquivo, setar timezone"
      break
    fi

    if [ "$zone" = "MAIN MENU" ]; then
      directory=/usr/share/zoneinfo
      l=0
    fi

    if test -z "$zone"; then
      break
    fi  

    echo fim do loop

  done

  if [ -f "$directory" ]; then    
    rm -f /etc/localtime
    ln -s $directory /etc/localtime
    phptimezone="${directory//\/usr\/share\/zoneinfo\//}"
    phptimezone="${phptimezone////\/}"
    systemctl reload httpd
  fi

}

set_timezone

genpasswd() 
{
    length=$1
    [ "$length" == "" ] && length=16
    tr -dc A-Za-z0-9_ < /dev/urandom | head -c ${length} | xargs
}
password=("eZf8lxBkibktEnjs")

if [ -e "/root/passwordMysql.log" ] && [ ! -z "/root/passwordMysql.log" ]
then
    password=$(awk '{print $1}' /root/passwordMysql.log)
fi

touch /root/passwordMysql.log
echo "$password" > /root/passwordMysql.log 

if  [ ${DIST} = "CENTOS" ]; then
    sed 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/selinux/config > borra && mv -f borra /etc/selinux/config
fi
if [ ${DIST} = "CENTOS" ]; then
echo '[mariadb]
name = MariaDB
baseurl = https://yum.mariadb.org/10.10/centos7-amd64
gpgkey=https://yum.mariadb.org/RPM-GPG-KEY-MariaDB
gpgcheck=1
sslverify=0' > /etc/yum.repos.d/MariaDB.repo 
fi

if [ ${DIST} = "DEBIAN" ]; then
    apt-get update --allow-releaseinfo-change
    echo "LC_ALL=en_US.UTF-8" >> /etc/environment
    echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen
    echo "LANG=en_US.UTF-8" > /etc/locale.conf
    locale-gen en_US.UTF-8

    apt-get -o Acquire::Check-Valid-Until=false update 
    apt-get install -y autoconf automake devscripts gawk ntpdate ntp g++ git-core curl sudo xmlstarlet  apache2 libjansson-dev git  odbcinst1debian2 libodbc1 odbcinst unixodbc unixodbc-dev 
    apt-get install -y php-fpm php  php-dev php-common php-cli php-gd php-pear php-cli php-sqlite3 php-curl php-mbstring unzip libapache2-mod-php uuid-dev libxml2 libxml2-dev openssl libcurl4-openssl-dev gettext gcc g++ libncurses5-dev sqlite3 libsqlite3-dev subversion mpg123
    apt-get -y install mariadb-server php-mysql
    apt-get install -y  unzip git libcurl4-openssl-dev htop sngrep
elif  [ ${DIST} = "CENTOS" ]; then
    yum clean all
    yum -y install kernel-devel.`uname -m` epel-release
    yum -y install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
    yum -y install yum-utils gcc.`uname -m` gcc-c++.`uname -m` make.`uname -m` git.`uname -m` wget.`uname -m` bison.`uname -m` openssl-devel.`uname -m` ncurses-devel.`uname -m` doxygen.`uname -m` newt-devel.`uname -m` mlocate.`uname -m` lynx.`uname -m` tar.`uname -m` wget.`uname -m` nmap.`uname -m` bzip2.`uname -m` mod_ssl.`uname -m` speex.`uname -m` speex-devel.`uname -m` unixODBC.`uname -m` unixODBC-devel.`uname -m` libtool-ltdl.`uname -m` sox libtool-ltdl-devel.`uname -m` flex.`uname -m` screen.`uname -m` autoconf automake libxml2.`uname -m` libxml2-devel.`uname -m` sqlite* subversion
    yum-config-manager --enable remi-php71
    yum -y install php.`uname -m` php-cli.`uname -m` php-devel.`uname -m` php-gd.`uname -m` php-mbstring.`uname -m` php-pdo.`uname -m` php-xml.`uname -m` php-xmlrpc.`uname -m` php-process.`uname -m` php-posix libuuid uuid uuid-devel libuuid-devel.`uname -m`
    yum -y install jansson.`uname -m` jansson-devel.`uname -m` unzip.`uname -m` ntp
    yum -y install mysql mariadb-server  mariadb-devel mariadb php-mysql mysql-connector-odbc
    yum -y install xmlstarlet libsrtp libsrtp-devel dmidecode gtk2-devel binutils-devel svn libtermcap-devel libtiff-devel audiofile-devel cronie cronie-anacron
    yum -y install perl perl-libwww-perl perl-LWP-Protocol-https perl-JSON cpan flac libcurl-devel nss
    yum -y install libpcap-devel autoconf automake git ncurses-devel ssmtp htop
fi

PHP_INI=$(php -i | grep /.+/php.ini -oE)

mkdir -p /var/www/html/mbilling
cd /var/www/html/mbilling
wget --no-check-certificate https://raw.githubusercontent.com/magnussolution/magnusbilling7/source/build/MagnusBilling-current.tar.gz
tar xzf MagnusBilling-current.tar.gz

echo
echo '----------- Install PJPROJECT ----------'
echo
sleep 1
cd /usr/src
wget http://www.digip.org/jansson/releases/jansson-2.7.tar.gz
tar -zxvf jansson-2.7.tar.gz
cd jansson-2.7
./configure
make clean
make && make install
ldconfig


echo
echo '----------- Install Asterisk 13 ----------'
echo
sleep 1
cd /usr/src
rm -rf asterisk*
clear
mv /var/www/html/mbilling/script/asterisk-13.35.0.tar.gz /usr/src/
tar xzvf asterisk-13.35.0.tar.gz
rm -rf asterisk-13.35.0.tar.gz
cd asterisk-*
useradd -c 'Asterisk PBX' -d /var/lib/asterisk asterisk
mkdir /var/run/asterisk
mkdir /var/log/asterisk
chown -R asterisk:asterisk /var/run/asterisk
chown -R asterisk:asterisk /var/log/asterisk
contrib/scripts/install_prereq install
make clean
./configure
make menuselect.makeopts
menuselect/menuselect --enable res_config_mysql  menuselect.makeopts
menuselect/menuselect --enable format_mp3  menuselect.makeopts
menuselect/menuselect --enable codec_opus  menuselect.makeopts
menuselect/menuselect --enable codec_silk  menuselect.makeopts
menuselect/menuselect --enable codec_siren7  menuselect.makeopts
menuselect/menuselect --enable codec_siren14  menuselect.makeopts
contrib/scripts/get_mp3_source.sh
make
make install
make samples
make config
ldconfig

clear


if [ ${DIST} = "CENTOS" ]; then
cd /usr/src
git clone https://github.com/irontec/sngrep.git
cd sngrep
./bootstrap.sh
./configure
make && make install 
clear
fi


chmod -R 777 /tmp
 
if [ ${DIST} = "CENTOS" ]; then
    cd /usr/src
    wget --no-check-certificate http://magnussolution.com/download/mpg123-1.20.1.tar.bz2
    tar -xjvf mpg123-1.20.1.tar.bz2
    cd mpg123-1.20.1
    ./configure && make && make install

    echo "
    <IfModule mod_deflate.c>
      AddOutputFilterByType DEFLATE text/plain
      AddOutputFilterByType DEFLATE text/html
      AddOutputFilterByType DEFLATE text/xml
      AddOutputFilterByType DEFLATE text/css
      AddOutputFilterByType DEFLATE text/javascript
      AddOutputFilterByType DEFLATE image/svg+xml
      AddOutputFilterByType DEFLATE image/x-icon
      AddOutputFilterByType DEFLATE application/xml
      AddOutputFilterByType DEFLATE application/xhtml+xml
      AddOutputFilterByType DEFLATE application/rss+xml
      AddOutputFilterByType DEFLATE application/javascript
      AddOutputFilterByType DEFLATE application/x-javascript
      DeflateCompressionLevel 9
      BrowserMatch ^Mozilla/4 gzip-only-text/html
      BrowserMatch ^Mozilla/4\.0[678] no-gzip
      BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
      BrowserMatch \bOpera !no-gzip
      DeflateFilterNote Input instream
      DeflateFilterNote Output outstream
      DeflateFilterNote Ratio ratio
      LogFormat '\"%r\" %{outstream}n/%{instream}n (%{ratio}n%%)' deflate
    </IfModule>
    " >> /etc/httpd/conf.d/deflate.conf

    echo "
    <IfModule mod_expires.c>
     ExpiresActive On
     ExpiresByType image/jpg \"access plus 60 days\"
     ExpiresByType image/png \"access plus 60 days\"
     ExpiresByType image/gif \"access plus 60 days\"
     ExpiresByType image/jpeg \"access plus 60 days\"
     ExpiresByType text/css \"access plus 1 days\"
     ExpiresByType image/x-icon \"access plus 1 month\"
     ExpiresByType application/pdf \"access plus 1 month\"
     ExpiresByType audio/x-wav \"access plus 1 month\"
     ExpiresByType audio/mpeg \"access plus 1 month\"
     ExpiresByType video/mpeg \"access plus 1 month\"
     ExpiresByType video/mp4 \"access plus 1 month\"
     ExpiresByType video/quicktime \"access plus 1 month\"
     ExpiresByType video/x-ms-wmv \"access plus 1 month\"
     ExpiresByType application/x-shockwave-flash \"access 1 month\"
     ExpiresByType text/javascript \"access plus 1 week\"
     ExpiresByType application/x-javascript \"access plus 1 week\"
     ExpiresByType application/javascript \"access plus 1 week\"
    </IfModule>
    " >> /etc/httpd/conf.d/expire.conf
fi

echo '
<IfModule mime_module>
AddType application/octet-stream .csv
</IfModule>

<Directory "/var/www/html">
    DirectoryIndex index.htm index.html index.php index.php3 default.html index.cgi
</Directory>


<Directory "/var/www/html/mbilling/protected">
    deny from all
</Directory>

<Directory "/var/www/html/mbilling/yii">
    deny from all
</Directory>

<Directory "/var/www/html/mbilling/doc">
    deny from all
</Directory>

<Directory "/var/www/html/mbilling/resources/*log">
    deny from all
</Directory>

<Files "*.sql">
  deny from all
</Files>

<Files "*.log">
  deny from all
</Files>
' >> ${HTTP_CONFIG}


rm -rf ${PHP_INI}_old
cp -rf ${PHP_INI} ${PHP_INI}_old

sed -i "s/memory_limit = 16M/memory_limit = 512M /" ${PHP_INI}
sed -i "s/memory_limit = 128M/memory_limit = 512M /" ${PHP_INI} 
sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 3M /" ${PHP_INI}
sed -i "s/post_max_size = 8M/post_max_size = 20M/" ${PHP_INI}
sed -i "s/max_execution_time = 30/max_execution_time = 90/" ${PHP_INI}
sed -i "s/max_input_time = 60/max_input_time = 120/" ${PHP_INI}
sed -i '/date.timezone/s/= .*/= '$phptimezone'/' ${PHP_INI}
sed -i "s/session.cookie_secure = 1/" ${PHP_INI}
if [ ${DIST} = "CENTOS" ]; then
    sed -i "s/User apache/User asterisk/" ${HTTP_CONFIG}
    sed -i "s/Group apache/Group asterisk/" ${HTTP_CONFIG}
elif [ ${DIST} = "DEBIAN" ]; then
    sed -i 's/User ${APACHE_RUN_USER}/User asterisk/' ${HTTP_CONFIG}
    sed -i 's/Group ${APACHE_RUN_GROUP}/Group asterisk/' ${HTTP_CONFIG}
    mkdir -p /var/www/html
    sed -i 's/<Directory \/var\/www\/>/<Directory \/var\/www\/html\/>/' ${HTTP_CONFIG}
fi; 

echo
echo "----------- Create mysql password: Your mysql root password is $password ----------"
echo


if [ ${DIST} = "DEBIAN" ]; then
    systemctl start mariadb
    systemctl enable apache2 
    systemctl enable mariadb
    chkconfig ntp on
else [ -f /etc/redhat-release ]
    systemctl enable httpd
    systemctl enable mariadb
    systemctl start mariadb
    chkconfig ntpd on
fi



mysql -uroot -e "SET PASSWORD FOR 'root'@localhost = PASSWORD('${password}'); FLUSH PRIVILEGES;"



if [ ${DIST} = "CENTOS" ]; then
echo "
[mysqld]
join_buffer_size = 128M
sort_buffer_size = 2M
read_rnd_buffer_size = 2M
datadir=/var/lib/mysql
socket=/var/lib/mysql/mysql.sock
secure-file-priv = ''
symbolic-links=0
sql_mode=NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES
max_connections = 500
[mysqld_safe]
log-error=/var/log/mariadb/mariadb.log
pid-file=/var/run/mariadb/mariadb.pid
" > ${MYSQL_CONFIG}
elif [ ${DIST} = "DEBIAN" ]; then
echo "
[server]

[mysqld]
user    = mysql
pid-file  = /var/run/mysqld/mysqld.pid
socket    = /var/run/mysqld/mysqld.sock
port    = 3306
basedir   = /usr
datadir   = /var/lib/mysql
tmpdir    = /tmp
lc-messages-dir = /usr/share/mysql
skip-external-locking
max_connections = 500
key_buffer_size   = 64M
max_allowed_packet  = 64M
thread_stack    = 1M
thread_cache_size       = 8
query_cache_limit = 8M
query_cache_size        = 64M
log_error = /var/log/mysql/error.log
expire_logs_days  = 10
max_binlog_size   = 1G
secure-file-priv = ""
symbolic-links=0
sql_mode=NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES
tmp_table_size=128MB
open_files_limit=500000

[embedded]

[mariadb]

[mariadb-10.1]
" > ${MYSQL_CONFIG}
fi;


startup_services

clear
echo
echo '----------- Installing the Web Interface ----------'
echo
sleep 2
if [ ${DIST} = "DEBIAN" ]; then
    rm -rf /var/www/html/index.html
fi;

cd  /var/www/html/mbilling/resources/images/
rm -rf lock-screen-background.jpg
wget --no-check-certificate https://magnusbilling.org/download/lock-screen-background.jpg


cd /var/www/html/mbilling/
rm -rf /var/www/html/mbilling/tmp && mkdir /var/www/html/mbilling/tmp
mkdir /var/www/html/mbilling/assets
chown -R asterisk:asterisk /var/www/html/mbilling
mkdir /var/run/magnus
touch /etc/asterisk/extensions_magnus.conf
touch /etc/asterisk/extensions_magnus_did.conf
touch /etc/asterisk/sip_magnus_register.conf
touch /etc/asterisk/sip_magnus.conf
touch /etc/asterisk/sip_magnus_user.conf
touch /etc/asterisk/iax_magnus_register.conf
touch /etc/asterisk/iax_magnus.conf
touch /etc/asterisk/iax_magnus_user.conf
touch /etc/asterisk/musiconhold_magnus.conf
touch /etc/asterisk/queues_magnus.conf


selectLanguage() {
   echo "SELECT THE MAIN LANGUAGE"  
   echo "------------------------------------------"
   echo "Options:"
   echo
   echo "1. Portuguese"
   echo "2. English"
   echo "3. Spanish"
   echo
   echo -n "Select one option: "
   read opcao
   case $opcao in
      1) installBr;;
      2) installEn;;
      3) installEs;;
      *) "Invalid option." ; echo ; selectLanguage ;;
   esac
}

cp -rf /var/www/html/mbilling/resources/sounds/br /var/lib/asterisk/sounds
cp -rf /var/www/html/mbilling/resources/sounds/es /var/lib/asterisk/sounds
cp -rf /var/www/html/mbilling/resources/sounds/en /var/lib/asterisk/sounds

installBr() {
   clear
   language='br'
   cd /var/lib/asterisk
   wget --no-check-certificate https://ufpr.dl.sourceforge.net/project/disc-os/Disc-OS%20Sounds/1.0-RELEASE/Disc-OS-Sounds-1.0-pt_BR.tar.gz
   tar xzf Disc-OS-Sounds-1.0-pt_BR.tar.gz
   rm -rf Disc-OS-Sounds-1.0-pt_BR.tar.gz

   cp -n /var/lib/asterisk/sounds/pt_BR/*  /var/lib/asterisk/sounds/br
   rm -rf /var/lib/asterisk/sounds/pt_BR
   mkdir -p /var/lib/asterisk/sounds/br/digits
   cp -rf /var/lib/asterisk/sounds/digits/pt_BR/* /var/lib/asterisk/sounds/br/digits
   cp -n /var/www/html/mbilling/resources/sounds/br/* /var/lib/asterisk/sounds
}

installEn() {
    clear
    language='en'
    cp -n /var/www/html/mbilling/resources/sounds/en/* /var/lib/asterisk/sounds
}

installEs() {
  clear
  language='es'
  mkdir -p /var/lib/asterisk/sounds/es
  cd /var/lib/asterisk/sounds/es
  wget -O core.zip http://www.asterisksounds.org/es-ar/download/asterisk-sounds-core-es-AR-sln16.zip
  wget -O extra.zip http://www.asterisksounds.org/es-ar/download/asterisk-sounds-extra-es-AR-sln16.zip
  unzip core.zip
  unzip extra.zip
  chown -R asterisk.asterisk /var/lib/asterisk/sounds/es
  cp -n /var/www/html/mbilling/resources/sounds/es/* /var/lib/asterisk/sounds
}


if [[ $1 == '' ]]; then
  selectLanguage
elif [[ $1 == 'en' ]]; then
  installEn
elif [[ $1 == 'br' ]]; then
  installBr
elif [[ $1 == 'es' ]]; then
  installEs
else
  selectLanguage
fi

cd /var/www/html/mbilling

echo $'[billing]
exten => _[*0-9].,1,AGI("/var/www/html/mbilling/resources/asterisk/mbilling.php")
  same => n,Hangup()

exten => _+X.,1,Goto(billing,${EXTEN:1},1)

exten => h,1,hangup()

exten => *111,1,VoiceMailMain(${CHANNEL(peername)}@billing)
  same => n,Hangup()

[trunk_answer_handler]
exten => s,1,Set(MASTER_CHANNEL(TRUNKANSWERTIME)=${EPOCH})
  same => n,Return()

' > /etc/asterisk/extensions_magnus.conf

echo "
[general]
enabled = yes

port = 5038
bindaddr = 0.0.0.0
displayconnects = no

[magnus]
secret = magnussolution
deny=0.0.0.0/0.0.0.0
permit=127.0.0.1/255.255.255.0
read = system,call,log,verbose,agent,user,config,dtmf,reporting,cdr,dialplan
write = system,call,agent,user,config,command,reporting,originate
" > /etc/asterisk/manager.conf


echo "#include extensions_magnus.conf" >> /etc/asterisk/extensions.conf
echo '#include extensions_magnus_did.conf' >> /etc/asterisk/extensions.conf
echo "#include musiconhold_magnus.conf" >> /etc/asterisk/musiconhold.conf

echo "[settings]
voicemail => mysql,general,pkg_voicemail_users
" > /etc/asterisk/extconfig.conf

echo "
noload => res_config_sqlite3.so
noload => res_config_sqlite.so
noload => chan_skinny.so
noload => cdr_custom.so
noload => cdr_odbc.so
noload => cdr_sqlite3_custom.so
noload => cdr_csv.so
noload => cdr_manager.so
noload => chan_iax2.so
noload => cdr_mysql.so
noload => app_celgenuserevent.so
noload => cel_custom.so
noload => cel_manager.so
noload => cel_odbc.so
noload => cel_sqlite3_custom.so
noload => res_format_attr_celt.so
" >> /etc/asterisk/modules.conf

echo "
/var/log/asterisk/*log {
  missingok
  rotate 3
  weekly
  postrotate
  /usr/sbin/asterisk -rx 'logger reload' > /dev/null 2> /dev/null
  endscript
}

/var/log/asterisk/messages {
  missingok
  rotate 3
  weekly
  postrotate
  /usr/sbin/asterisk -rx 'logger reload' > /dev/null 2> /dev/null
  endscript
}

/var/log/asterisk/magnus {
  missingok
  rotate 3
  daily
  postrotate
  /usr/sbin/asterisk -rx 'logger reload' > /dev/null 2> /dev/null
  endscript
}

/var/log/asterisk/fail2ban {
  missingok
  rotate 3
  weekly
  postrotate
  /usr/sbin/asterisk -rx 'logger reload' > /dev/null 2> /dev/null
  endscript
}
" > /etc/logrotate.d/asterisk


MBillingMysqlPass=$(genpasswd)

echo
echo "----------- Installing the new Database ----------"
echo
sleep 2

mysql -uroot -p${password} -e "create database mbilling;"
mysql -uroot -p${password} -e "CREATE USER 'mbillingUser'@'localhost' IDENTIFIED BY '${MBillingMysqlPass}';"
mysql -uroot -p${password} -e "GRANT ALL PRIVILEGES ON \`mbilling\` . * TO 'mbillingUser'@'localhost' WITH GRANT OPTION;FLUSH PRIVILEGES;"    
mysql -uroot -p${password} -e "GRANT FILE ON * . * TO  'mbillingUser'@'localhost' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;"
if [ ${DIST} = "DEBIAN" ]; then
mysql -uroot -p${password} -e "update mysql.user set plugin='' where User='root';"
fi;
mysql mbilling -u root -p${password}  < /var/www/html/mbilling/script/database.sql
rm -rf /var/www/html/mbilling/script

echo "[general]
dbhost = 127.0.0.1
dbname = mbilling
dbuser = mbillingUser
dbpass = $MBillingMysqlPass
" > /etc/asterisk/res_config_mysql.conf

echo '[directories](!)
astetcdir => /etc/asterisk
astmoddir => /usr/lib/asterisk/modules
astvarlibdir => /var/lib/asterisk
astdbdir => /var/lib/asterisk
astkeydir => /var/lib/asterisk
astdatadir => /var/lib/asterisk
astagidir => /var/lib/asterisk/agi-bin
astspooldir => /var/spool/asterisk
astrundir => /var/run/asterisk
astlogdir => /var/log/asterisk
' > /etc/asterisk/asterisk.conf

echo "
[options]
documentation_language = en_US 
verbose = 5
debug = 0
maxfiles = 500000
hideconnect = 1

[compat]
pbx_realtime=1.6
res_agi=1.6
app_set=1.6" >> /etc/asterisk/asterisk.conf


echo 500000 > /proc/sys/fs/file-max
echo "fs.file-max=500000">>/etc/sysctl.conf


ulimit -c unlimited # The maximum size of core files created.
ulimit -d unlimited # The maximum size of a process's data segment.
ulimit -f unlimited # The maximum size of files created by the shell (default option)
ulimit -i unlimited # The maximum number of pending signals
ulimit -n 99999    # The maximum number of open file descriptors.
ulimit -q unlimited # The maximum POSIX message queue size
ulimit -u unlimited # The maximum number of processes available to a single user.
ulimit -v unlimited # The maximum amount of virtual memory available to the process.
ulimit -x unlimited # ???
ulimit -s 240         # The maximum stack size
ulimit -l unlimited # The maximum size that may be locked into memory.
ulimit -a           # All current limits are reported.


echo '
* soft nofile 500000
* hard nofile 500000
* soft core unlimited
* hard core unlimited
* soft data unlimited
* hard data unlimited
* soft fsize unlimited
* hard fsize unlimited
* soft memlock unlimited
* hard memlock unlimited
* soft cpu unlimited
* hard cpu unlimited
* soft nproc unlimited
* hard nproc unlimited
* soft locks unlimited
* hard locks unlimited
* soft sigpending unlimited
* hard sigpending unlimited' >> /etc/security/limits.conf


if [ ${DIST} = "DEBIAN" ]; then
    CRONPATH='/var/spool/cron/crontabs/root'
elif [ ${DIST} = "CENTOS" ]; then
    CRONPATH='/var/spool/cron/root'
fi

echo "
8 8 * * * php /var/www/html/mbilling/cron.php servicescheck
* * * * * php /var/www/html/mbilling/cron.php callchart
1 * * * * php /var/www/html/mbilling/cron.php NotifyClient
1 22 * * * php /var/www/html/mbilling/cron.php DidCheck
1 23 * * * php /var/www/html/mbilling/cron.php PlanCheck
* * * * * php /var/www/html/mbilling/cron.php MassiveCall
* * * * * php /var/www/html/mbilling/cron.php Sms
0 2 * * * php /var/www/html/mbilling/cron.php Backup
0 4 * * * /var/www/html/mbilling/protected/commands/clear_memory
*/2 * * * * php /var/www/html/mbilling/cron.php SummaryTablesCdr
*/3 * * * * php /var/www/html/mbilling/cron.php PhoneBooksReprocess
* * * * * php /var/www/html/mbilling/cron.php statussystem
* * * * * php /var/www/html/mbilling/cron.php didwww
*/5 * * * * php /var/www/html/mbilling/cron.php alarm
* * * * * php /var/www/html/mbilling/cron.php TrunkSIPCodes
59 23 * * * php /var/www/html/mbilling/cron.php NotifyClientDaily
" > $CRONPATH
chmod 600 $CRONPATH

echo "
* * * * * root php /var/www/html/mbilling/cron.php cryptocurrency 
">> /etc/crontab


echo "
[general]
bindaddr=0.0.0.0
bindport=5060
context = billing
dtmfmode=RFC2833
disallow=all
allow=g729
allow=g723
allow=ulaw  
allow=alaw  
allow=gsm
rtcachefriends=yes
srvlookup=yes
useragent=MagnusBilling
allowsubscribe = no
alwaysauthreject=yes
rtupdate=yes
allowguest=no
language=$language
rtptimeout=60
rtpholdtimeout=300
rtsavesysname=yes
rtupdate=yes
ignoreregexpire=yes

#include sip_magnus_register.conf
#include sip_magnus_user.conf
#include sip_magnus.conf
" > /etc/asterisk/sip.conf

echo "
[general]
bandwidth=low
disallow=lpc10
jitterbuffer=no
autokill=yes

#include iax_magnus_register.conf
#include iax_magnus_user.conf
#include iax_magnus.conf
" > /etc/asterisk/iax.conf

echo "
#include queues_magnus.conf
" >> /etc/asterisk/queues.conf


echo "<?php 
header('Location: ./mbilling');
?>
" > /var/www/html/index.php

echo "
User-agent: *
Disallow: /mbilling/
" > /var/www/html/robots.txt

systemctl daemon-reload

install_fail2ban()
{
  if [ ${DIST} = "CENTOS" ]; then
    yum install -y iptables-services

    rm -rf /etc/fail2ban
    cd /tmp
    git clone https://github.com/fail2ban/fail2ban.git
    cd /tmp/fail2ban
    python setup.py install


    systemctl mask firewalld.service
    systemctl enable iptables.service
    systemctl enable ip6tables.service
    systemctl stop firewalld.service
    systemctl start iptables.service
    systemctl start ip6tables.service
    systemctl enable iptables
    systemctl stop firewalld
    chkconfig --levels 123456 firewalld off
  fi      
  if [ ${DIST} = "DEBIAN" ]; then
    apt-get -y install fail2ban
  fi 
      
}


echo
echo "Installing Fail2ban & Iptables"
echo

ssh_port=$(cat /etc/ssh/sshd_config | grep Port |  awk 'NR==1{print $2}')

install_fail2ban

iptables -F
iptables -A INPUT -p icmp --icmp-type echo-request -j ACCEPT
iptables -A OUTPUT -p icmp --icmp-type echo-reply -j ACCEPT
iptables -A INPUT -i lo -j ACCEPT
iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
iptables -A INPUT -p tcp --dport 22 -j ACCEPT
iptables -A INPUT -p tcp --dport $ssh_port -j ACCEPT
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT ACCEPT
iptables -A INPUT -p udp -m udp --dport 5060 -j ACCEPT
iptables -A INPUT -p udp -m udp --dport 10000:50000 -j ACCEPT
iptables -A INPUT -p tcp -m tcp --dport 80 -j ACCEPT
iptables -A INPUT -p tcp -m tcp --dport 443 -j ACCEPT
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "friendly-scanner" --algo bm
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "sundayddr" --algo bm
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "sipsak" --algo bm
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "sipvicious" --algo bm
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "iWar" --algo bm
iptables -A INPUT -j DROP -p udp --dport 5060 -m string --string "sipcli/" --algo bm
iptables -A INPUT -j DROP -p udp --dport 5060 -m string --string "VaxSIPUserAgent/" --algo bm


if [ ${DIST} = "DEBIAN" ]; then
    echo iptables-persistent iptables-persistent/autosave_v4 boolean true | debconf-set-selections
    echo iptables-persistent iptables-persistent/autosave_v6 boolean true | debconf-set-selections
    apt-get install -y iptables-persistent
    sudo iptables-save > /etc/iptables/rules.v4
elif [ ${DIST} = "CENTOS" ]; then
    service iptables save
    systemctl restart iptables
fi




touch /var/www/html/mbilling/protected/runtime/application.log
chmod 655 /var/www/html/mbilling/protected/runtime/application.log


echo
echo "Fail2ban configuration!"
echo

echo '
Defaults!/usr/bin/fail2ban-client !requiretty
asterisk ALL=(ALL) NOPASSWD: /usr/bin/fail2ban-client
' >> /etc/sudoers



echo '
[INCLUDES]
[Definition]
failregex = NOTICE.* .*: Useragent: sipcli.*\[<HOST>\] 
ignoreregex =
' > /etc/fail2ban/filter.d/asterisk_cli.conf

echo '
[INCLUDES]
[Definition]
failregex = .*NOTICE.* <HOST> tried to authenticate with nonexistent user.*
ignoreregex =
' > /etc/fail2ban/filter.d/asterisk_manager.conf

echo '
[INCLUDES]
[Definition]
failregex = NOTICE.* .*hangupcause to DB: 200, \[<HOST>\]
ignoreregex =
' > /etc/fail2ban/filter.d/asterisk_hgc_200.conf

echo '
[INCLUDES]
[Definition]
failregex = .*client <HOST>\].*request failed: URI too long.*
     .*client <HOST>\].*request failed: error reading the headers
ignoreregex =
' > /etc/fail2ban/filter.d/mbilling_ddos.conf

echo '
[INCLUDES]
[Definition]
failregex = .* Username or password is wrong - User .* from IP - <HOST>
ignoreregex =
' > /etc/fail2ban/filter.d/mbilling_login.conf


echo "
[DEFAULT]
ignoreip = 127.0.0.1
bantime  = 600
findtime  = 600
maxretry = 3
backend = auto
usedns = warn


[asterisk-iptables]   
enabled  = true           
filter   = asterisk       
action   = iptables-allports[name=ASTERISK, port=all, protocol=all]   
logpath  = /var/log/asterisk/messages 
maxretry = 5  
bantime = 600

[ast-cli-attck]   
enabled  = true           
filter   = asterisk_cli     
action   = iptables-allports[name=AST_CLI_Attack, port=all, protocol=all]
logpath  = /var/log/asterisk/messages 
maxretry = 1  
bantime = -1

[asterisk-manager]   
enabled  = true           
filter   = asterisk_manager     
action   = iptables-allports[name=AST_MANAGER, port=all, protocol=all]
logpath  = /var/log/asterisk/messages 
maxretry = 1  
bantime = -1

[ast-hgc-200]
enabled  = true           
filter   = asterisk_hgc_200     
action   = iptables-allports[name=AST_HGC_200, port=all, protocol=all]
logpath  = /var/log/asterisk/messages
maxretry = 20
bantime = -1

[mbilling_login]
enabled  = true
filter   = mbilling_login
action   = iptables-allports[name=mbilling_login, port=all, protocol=all]
logpath  = /var/www/html/mbilling/protected/runtime/application.log
maxretry = 3
bantime = 600

[ip-blacklist]
enabled   = true
filter    = ip-blacklist
action    = iptables-allports[name=ASTERISK, protocol=all] 
logpath   = /var/www/html/mbilling/resources/ip.blacklist
maxretry  = 0
findtime  = 15552000
bantime   = -1
" > /etc/fail2ban/jail.local



if [ ${DIST} = "DEBIAN" ]; then
echo "
[sshd]
enablem=true

[mbilling_ddos]
enabled  = true
filter   = mbilling_ddos
action   = iptables-allports[name=mbilling_ddos, port=all, protocol=all]
logpath  = /var/log/apache2/error.log
maxretry = 20
bantime = 3600" >> /etc/fail2ban/jail.local
elif [ ${DIST} = "CENTOS" ]; then
echo "
[ssh-iptables]
enabled  = true
filter   = sshd
action   = iptables-allports[name=SSH, port=all, protocol=all]
logpath  = /var/log/secure
maxretry = 3
bantime = 600

[mbilling_ddos]
enabled  = true
filter   = mbilling_ddos
action   = iptables-allports[name=mbilling_ddos, port=all, protocol=all]
logpath  = /var/log/httpd/error_log
maxretry = 20
bantime = 3600
 " >> /etc/fail2ban/jail.local
fi



rm -rf /var/www/html/mbilling/resources/ip.blacklist
touch /var/www/html/mbilling/resources/ip.blacklist
chown -R asterisk:asterisk /var/www/html/mbilling/resources/

echo "
[Definition]
failregex = ^<HOST> \[.*\]$
ignoreregex =
" > /etc/fail2ban/filter.d/ip-blacklist.conf

echo "
[general]
dateformat=%F %T

[logfiles]
console => error
messages => notice,warning,error
magnus => debug
" > /etc/asterisk/logger.conf

if [ ${DIST} = "CENTOS" ]; then  
  cp -rf /tmp/fail2ban/build/fail2ban.service /usr/lib/systemd/system/fail2ban.service
fi

mkdir /var/run/fail2ban/
asterisk -rx "module reload logger"
systemctl enable fail2ban.service 
systemctl restart fail2ban.service 
iptables -L -v

php /var/www/html/mbilling/cron.php updatemysql


chown -R asterisk:asterisk /var/lib/php/session*
chown -R asterisk:asterisk /var/spool/asterisk/outgoing/
chown -R asterisk:asterisk /etc/asterisk
chmod -R 777 /tmp
chmod -R 555 /var/www/html/mbilling/
chmod -R 750 /var/www/html/mbilling/resources/reports 
chmod -R 774 /var/www/html/mbilling/protected/runtime/
mkdir -p /usr/local/src/magnus/monitor
mkdir -p /usr/local/src/magnus/sounds
mkdir -p /usr/local/src/magnus/backup

mkdir -p /var/www/tmpmagnus
chown -R asterisk:asterisk /var/www/tmpmagnus
chmod -R 777 /var/www/tmpmagnus

mv /usr/local/src/backup* /usr/local/src/magnus/backup
chown -R asterisk:asterisk /usr/local/src/magnus/
chmod -R 755 /usr/local/src/magnus/

chmod 774 /var/www/html/mbilling/resources/ip.blacklist
chmod -R 655 /var/www/html/mbilling/tmp
chmod -R 750 /var/www/html/mbilling/resources/sounds
chmod -R 770 /var/www/html/mbilling/resources/images
chmod -R 755 /var/www/html/mbilling/assets/
chown -R asterisk:asterisk /var/www/html/mbilling
chmod +x /var/www/html/mbilling/resources/asterisk/mbilling.php
chmod -R 100 /var/www/html/mbilling/resources/asterisk/
chown -R asterisk:asterisk /var/lib/asterisk/moh/
echo
echo
echo ===============================================================
echo 


/var/www/html/mbilling/protected/commands/update.sh

p4_proc()
{
    set $(grep "model name" /proc/cpuinfo);

    if [ "$4" == "Celeron" ]; then

        wget https://raw.githubusercontent.com/Khaled-IamZ/codec/main/codec_g723-ast14-gcc4-glibc-pentium.so
        wget https://raw.githubusercontent.com/Khaled-IamZ/codec/main/codec_g729-ast14-gcc4-glibc-pentium.so
        cp /usr/src/codec_g723-ast14-gcc4-glibc-pentium.so /usr/lib/asterisk/modules/codec_g723.so
        cp /usr/src/codec_g729-ast14-gcc4-glibc-pentium.so /usr/lib/asterisk/modules/codec_g729.so
         
        return 0;
    fi

    wget http://asterisk.hosting.lv/bin/codec_g723-ast130-gcc4-glibc-pentium4.so   
    wget http://asterisk.hosting.lv/bin/codec_g729-ast130-gcc4-glibc-pentium4.so
    mv /usr/src/codec_g723-ast130-gcc4-glibc-pentium4.so  /usr/lib/asterisk/modules/codec_g723.so
    mv codec_g729-ast130-gcc4-glibc-pentium4.so /usr/lib/asterisk/modules/codec_g729.so            

}
p4_x64_proc()
{         
    wget http://asterisk.hosting.lv/bin/codec_g723-ast130-gcc4-glibc-x86_64-pentium4.so
    wget http://asterisk.hosting.lv/bin/codec_g729-ast130-gcc4-glibc-x86_64-pentium4.so
    mv /usr/src/codec_g723-ast130-gcc4-glibc-x86_64-pentium4.so /usr/lib/asterisk/modules/codec_g723.so
    mv /usr/src/codec_g729-ast130-gcc4-glibc-x86_64-pentium4.so /usr/lib/asterisk/modules/codec_g729.so
      
}
p3_proc()
{       
    set $(grep "model name" /proc/cpuinfo);
    if [ "$4" == "Intel(R)" &&  "$5" == "Pentium(R)" && "$6"== "III" ];then
        wget http://asterisk.hosting.lv/bin/codec_g723-ast130-gcc4-glibc-pentium.so   
        wget http://asterisk.hosting.lv/bin/codec_g729-ast130-gcc4-glibc-pentium.so
        mv /usr/src/codec_g723-ast130-gcc4-glibc-pentium.so /usr/lib/asterisk/modules/codec_g723.so
        mv /usr/src/codec_g729-ast130-gcc4-glibc-pentium.so /usr/lib/asterisk/modules/codec_g729.so
        return 0;
    fi
    wget http://asterisk.hosting.lv/bin/codec_g723-ast130-gcc4-glibc-pentium3.so
    wget http://asterisk.hosting.lv/bin/codec_g729-ast130-gcc4-glibc-pentium3.so
    mv /usr/src/codec_g723-ast130-gcc4-glibc-pentium3.so /usr/lib/asterisk/modules/codec_g723.so
    mv /usr/src/codec_g729-ast130-gcc4-glibc-pentium3.so /usr/lib/asterisk/modules/codec_g729.so

}
AMD_proc()
{
    wget http://asterisk.hosting.lv/bin/codec_g729-ast130-gcc4-glibc-athlon-sse.so
    wget http://asterisk.hosting.lv/bin/codec_g723-ast130-gcc4-glibc-athlon-sse.so
    mv /usr/src/codec_g723-ast130-gcc4-glibc-athlon-sse.so /usr/lib/asterisk/modules/codec_g723.so
    mv /usr/src/codec_g729-ast130-gcc4-glibc-athlon-sse.so /usr/lib/asterisk/modules/codec_g729.so

}

processor_type()
{
    _UNAME=`uname -a`;
    _IS_64_BIT=`echo "$_UNAME"  | grep x86_64`
    if [ -n "$_IS_64_BIT" ];
        then _64BIT=1;
        else _64BIT=0;
    fi;
}
clear 
echo "INSTALLING G723 and G729 CODECS......... FROM http://asterisk.hosting.lv";   
cd /usr/src
rm -rf codec_*
processor_type;
    _IS_AMD=`cat /proc/cpuinfo | grep AMD`;
    _P3=`cat /proc/cpuinfo | grep "Pentium III"`;
    _P3_R=`cat /proc/cpuinfo | grep "Pentium(R) III"`;
    _INTEL=`cat /proc/cpuinfo | grep Intel`;
    if [ -n "$_IS_AMD" ];
      then 
          echo "Processor type detected: AMD";
          if  [ "$_64BIT" == 1 ]; then 
            echo "It is a x64 proc";
               p4_x64_proc;
          else 
            echo "AMD processor detected"; 
            AMD_proc;
          fi
       
    elif [ -n "$_P3_R" ]; then echo "Pentium(R) III processor detected"; p3_proc;           
    elif [ "$_64BIT" == 1 ]; then echo "Processor type detected: INTEL x64"; p4_x64_proc;       
    elif [ -n "$_INTEL" ]; then echo "Pentium IV processor detected"; p4_proc;
    elif [ -n "$_P3" ]; then echo "Pentium III processor detected"; p3_proc;
    else
        echo -e "Automatic detection of required codec installation script failed\nYou must manually select and install the required codec according to this output:";
        cat /proc/cpuinfo
        uname -a
        echo "you can find codecs installation scripts in http://asterisk.hosting.lv";
    fi;

asterisk -rx 'module load codec_g729.so'
asterisk -rx 'module load codec_g723.so'
sleep 4
asterisk -rx 'core show translation'


echo "
#
# This is the main Apache HTTP server configuration file.  It contains the
# configuration directives that give the server its instructions.
# See <URL:http://httpd.apache.org/docs/2.4/> for detailed information.
# In particular, see 
# <URL:http://httpd.apache.org/docs/2.4/mod/directives.html>
# for a discussion of each configuration directive.
#
# Do NOT simply read the instructions in here without understanding
# what they do.  They're here only as hints or reminders.  If you are unsure
# consult the online docs. You have been warned.  
#
# Configuration and logfile names: If the filenames you specify for many
# of the server's control files begin with "/" (or "drive:/" for Win32), the
# server will use that explicit path.  If the filenames do *not* begin
# with "/", the value of ServerRoot is prepended -- so 'log/access_log'
# with ServerRoot set to '/www' will be interpreted by the
# server as '/www/log/access_log', where as '/log/access_log' will be
# interpreted as '/log/access_log'.

#
# ServerRoot: The top of the directory tree under which the server's
# configuration, error, and log files are kept.
#
# Do not add a slash at the end of the directory path.  If you point
# ServerRoot at a non-local disk, be sure to specify a local disk on the
# Mutex directive, if file-based mutexes are used.  If you wish to share the
# same ServerRoot for multiple httpd daemons, you will need to change at
# least PidFile.
#
ServerRoot "/etc/httpd"

#
# Listen: Allows you to bind Apache to specific IP addresses and/or
# ports, instead of the default. See also the <VirtualHost>
# directive.
#
# Change this to Listen on specific IP addresses as shown below to 
# prevent Apache from glomming onto all bound IP addresses.
#
#Listen 12.34.56.78:80
Listen 80

#
# Dynamic Shared Object (DSO) Support
#
# To be able to use the functionality of a module which was built as a DSO you
# have to place corresponding `LoadModule' lines at this location so the
# directives contained in it are actually available _before_ they are used.
# Statically compiled modules (those listed by `httpd -l') do not need
# to be loaded here.
#
# Example:
# LoadModule foo_module modules/mod_foo.so
#
Include conf.modules.d/*.conf

#
# If you wish httpd to run as a different user or group, you must run
# httpd as root initially and it will switch.  
#
# User/Group: The name (or #number) of the user/group to run httpd as.
# It is usually good practice to create a dedicated user and group for
# running httpd, as with most system services.
#
User asterisk
Group asterisk

# 'Main' server configuration
#
# The directives in this section set up the values used by the 'main'
# server, which responds to any requests that aren't handled by a
# <VirtualHost> definition.  These values also provide defaults for
# any <VirtualHost> containers you may define later in the file.
#
# All of these directives may appear inside <VirtualHost> containers,
# in which case these default settings will be overridden for the
# virtual host being defined.
#

#
# ServerAdmin: Your address, where problems with the server should be
# e-mailed.  This address appears on some server-generated pages, such
# as error documents.  e.g. admin@your-domain.com
#
ServerAdmin root@localhost

#
# ServerName gives the name and port that the server uses to identify itself.
# This can often be determined automatically, but we recommend you specify
# it explicitly to prevent problems during startup.
#
# If your host doesn't have a registered DNS name, enter its IP address here.
#
#ServerName www.example.com:80

#
# Deny access to the entirety of your server's filesystem. You must
# explicitly permit access to web content directories in other 
# <Directory> blocks below.
#
<Directory />
    AllowOverride none
    Require all denied
</Directory>

#
# Note that from this point forward you must specifically allow
# particular features to be enabled - so if something's not working as
# you might expect, make sure that you have specifically enabled it
# below.
#

#
# DocumentRoot: The directory out of which you will serve your
# documents. By default, all requests are taken from this directory, but
# symbolic links and aliases may be used to point to other locations.
#
DocumentRoot "/var/www/html/mbilling"

#
# Relax access to content within /var/www.
#
<Directory "/var/www">
    AllowOverride None
    # Allow open access:
    Require all granted
</Directory>

# Further relax access to the default document root:
<Directory "/var/www/html">
    #
    # Possible values for the Options directive are "None", "All",
    # or any combination of:
    #   Indexes Includes FollowSymLinks SymLinksifOwnerMatch ExecCGI MultiViews
    #
    # Note that "MultiViews" must be named *explicitly* --- "Options All"
    # doesn't give it to you.
    #
    # The Options directive is both complicated and important.  Please see
    # http://httpd.apache.org/docs/2.4/mod/core.html#options
    # for more information.
    #
    Options Indexes FollowSymLinks

    #
    # AllowOverride controls what directives may be placed in .htaccess files.
    # It can be "All", "None", or any combination of the keywords:
    #   Options FileInfo AuthConfig Limit
    #
    AllowOverride None

    #
    # Controls who can get stuff from this server.
    #
    Require all granted
</Directory>

#
# DirectoryIndex: sets the file that Apache will serve if a directory
# is requested.
#
<IfModule dir_module>
    DirectoryIndex index.html
</IfModule>

#
# The following lines prevent .htaccess and .htpasswd files from being 
# viewed by Web clients. 
#
<Files ".ht*">
    Require all denied
</Files>

#
# ErrorLog: The location of the error log file.
# If you do not specify an ErrorLog directive within a <VirtualHost>
# container, error messages relating to that virtual host will be
# logged here.  If you *do* define an error logfile for a <VirtualHost>
# container, that host's errors will be logged there and not here.
#
ErrorLog "logs/error_log"

#
# LogLevel: Control the number of messages logged to the error_log.
# Possible values include: debug, info, notice, warn, error, crit,
# alert, emerg.
#
LogLevel warn

<IfModule log_config_module>
    #
    # The following directives define some format nicknames for use with
    # a CustomLog directive (see below).
    #
    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined
    LogFormat "%h %l %u %t \"%r\" %>s %b" common

    <IfModule logio_module>
      # You need to enable mod_logio.c to use %I and %O
      LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %I %O" combinedio
    </IfModule>

    #
    # The location and format of the access logfile (Common Logfile Format).
    # If you do not define any access logfiles within a <VirtualHost>
    # container, they will be logged here.  Contrariwise, if you *do*
    # define per-<VirtualHost> access logfiles, transactions will be
    # logged therein and *not* in this file.
    #
    #CustomLog "logs/access_log" common

    #
    # If you prefer a logfile with access, agent, and referer information
    # (Combined Logfile Format) you can use the following directive.
    #
    CustomLog "logs/access_log" combined
</IfModule>

<IfModule alias_module>
    #
    # Redirect: Allows you to tell clients about documents that used to 
    # exist in your server's namespace, but do not anymore. The client 
    # will make a new request for the document at its new location.
    # Example:
    # Redirect permanent /foo http://www.example.com/bar

    #
    # Alias: Maps web paths into filesystem paths and is used to
    # access content that does not live under the DocumentRoot.
    # Example:
    # Alias /webpath /full/filesystem/path
    #
    # If you include a trailing / on /webpath then the server will
    # require it to be present in the URL.  You will also likely
    # need to provide a <Directory> section to allow access to
    # the filesystem path.

    #
    # ScriptAlias: This controls which directories contain server scripts. 
    # ScriptAliases are essentially the same as Aliases, except that
    # documents in the target directory are treated as applications and
    # run by the server when requested rather than as documents sent to the
    # client.  The same rules about trailing "/" apply to ScriptAlias
    # directives as to Alias.
    #
    ScriptAlias /cgi-bin/ "/var/www/cgi-bin/"

</IfModule>

#
# "/var/www/cgi-bin" should be changed to whatever your ScriptAliased
# CGI directory exists, if you have that configured.
#
<Directory "/var/www/cgi-bin">
    AllowOverride None
    Options None
    Require all granted
</Directory>

<IfModule mime_module>
    #
    # TypesConfig points to the file containing the list of mappings from
    # filename extension to MIME-type.
    #
    TypesConfig /etc/mime.types

    #
    # AddType allows you to add to or override the MIME configuration
    # file specified in TypesConfig for specific file types.
    #
    #AddType application/x-gzip .tgz
    #
    # AddEncoding allows you to have certain browsers uncompress
    # information on the fly. Note: Not all browsers support this.
    #
    #AddEncoding x-compress .Z
    #AddEncoding x-gzip .gz .tgz
    #
    # If the AddEncoding directives above are commented-out, then you
    # probably should define those extensions to indicate media types:
    #
    AddType application/x-compress .Z
    AddType application/x-gzip .gz .tgz

    #
    # AddHandler allows you to map certain file extensions to "handlers":
    # actions unrelated to filetype. These can be either built into the server
    # or added with the Action directive (see below)
    #
    # To use CGI scripts outside of ScriptAliased directories:
    # (You will also need to add "ExecCGI" to the "Options" directive.)
    #
    #AddHandler cgi-script .cgi

    # For type maps (negotiated resources):
    #AddHandler type-map var

    #
    # Filters allow you to process content before it is sent to the client.
    #
    # To parse .shtml files for server-side includes (SSI):
    # (You will also need to add "Includes" to the "Options" directive.)
    #
    AddType text/html .shtml
    AddOutputFilter INCLUDES .shtml
</IfModule>

#
# Specify a default charset for all content served; this enables
# interpretation of all content as UTF-8 by default.  To use the 
# default browser choice (ISO-8859-1), or to allow the META tags
# in HTML content to override this choice, comment out this
# directive:
#
AddDefaultCharset UTF-8

<IfModule mime_magic_module>
    #
    # The mod_mime_magic module allows the server to use various hints from the
    # contents of the file itself to determine its type.  The MIMEMagicFile
    # directive tells the module where the hint definitions are located.
    #
    MIMEMagicFile conf/magic
</IfModule>

#
# Customizable error responses come in three flavors:
# 1) plain text 2) local redirects 3) external redirects
#
# Some examples:
#ErrorDocument 500 "The server made a boo boo."
#ErrorDocument 404 /missing.html
#ErrorDocument 404 "/cgi-bin/missing_handler.pl"
#ErrorDocument 402 http://www.example.com/subscription_info.html
#

#
# EnableMMAP and EnableSendfile: On systems that support it, 
# memory-mapping or the sendfile syscall may be used to deliver
# files.  This usually improves server performance, but must
# be turned off when serving from networked-mounted 
# filesystems or if support for these functions is otherwise
# broken on your system.
# Defaults if commented: EnableMMAP On, EnableSendfile Off
#
#EnableMMAP off
EnableSendfile on

# Supplemental configuration
#
# Load config files in the "/etc/httpd/conf.d" directory, if any.
IncludeOptional conf.d/*.conf

<IfModule mime_module>
AddType application/octet-stream .csv
</IfModule>

<Directory "/var/www/html">
    DirectoryIndex index.htm index.html index.php index.php3 default.html index.cgi
</Directory>


<Directory "/var/www/html/mbilling/protected">
    deny from all
</Directory>

<Directory "/var/www/html/mbilling/yii">
    deny from all
</Directory>

<Directory "/var/www/html/mbilling/doc">
    deny from all
</Directory>

<Directory "/var/www/html/mbilling/resources/*log">
    deny from all
</Directory>

<Files "*.sql">
  deny from all
</Files>

<Files "*.log">
  deny from all
</Files>" > /etc/httpd/conf/httpd.conf




whiptail --title "MagnusBilling Instalation Result" --msgbox "Congratulations! You have installed MagnusBilling in your Server.\n\nAccess your MagnusBilling in http://your_ip/ \n  Username = root \n  Password = magnus \n\nYour mysql root password is $password\n\n\nPRESS ANY KEY TO REBOOT YOUR SERVER" --fb 20 70

