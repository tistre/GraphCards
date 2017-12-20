# How to install GraphCards

## Clone from GitHub

First, get the current source code from GitHub:

```
$ git clone https://github.com/tistre/GraphCards.git
```

## Run Composer

Run `composer install`. It'll prompt you for some Symfony configuration stuff which you can simply skip with `Enter`:

```
$ cd /path/to/GraphCards
$ composer install
```

## Configure the Apache Web server

Quick instructions here; see the [Symfony “Configuring a Web Server” docs](https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html) docs for more info.

Per the [Symfony “Checking Symfony Application Configuration and Setup - Setting up Permissions” docs](http://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup),
you need to make sure the `var` directory is writable by both command line and Web server user.

On CentOS 7, these commands should do the job:

```
$ cd /path/to/GraphCards
$ HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
$ sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var
$ sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var
```

Now copy the example Apache configuration include file `httpd.inc.conf.dist`:

```
$ cd /path/to/GraphCards
$ cp httpd.inc.conf.dist httpd.inc.conf
```

Include your copy in the Apache configuration. On CentOS 7, add a symbolic link in /etc/httpd/conf.d:

```
$ sudo ln -s /path/to/GraphCards/httpd.inc.conf /etc/httpd/conf.d/graphcards.conf
```

Now restart Apache. On CentOS 7:

```
$ sudo systemctl reload httpd.service
```
