Mautic Tweaks to run after install

1) install commands.php in home directory - Important change the secret phrase
2) upload htaccess files (enhancmeent to allow commands.php)
3) Install whitelabeler to home directory 'gh repo clone nickian/mautic-whitelabeler'
4) Copy down whitelabel assets to mautic-whitelabeler
5) Update whitelabel config.json "url" param to respect 
6) Run whitelabeler - 'sudo -u www-data php cli.php --whitelabel'
7) 
