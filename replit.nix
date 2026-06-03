{pkgs}: {
  deps = [
    pkgs.unzip
    pkgs.wget
    pkgs.curl
    pkgs.mariadb
    pkgs.php83Extensions.intl
    pkgs.php83Extensions.gd
    pkgs.php83Extensions.zip
    pkgs.php83Extensions.curl
    pkgs.php83Extensions.xml
    pkgs.php83Extensions.mbstring
    pkgs.php83Extensions.pdo_mysql
    pkgs.php83Extensions.mysqli
    pkgs.php83
  ];
}
