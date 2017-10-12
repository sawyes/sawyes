# Some libary for dev in laravel 5.*

## Installation

```
composer require sawyes/sawyes
```

## Basic Usage


```
// write('message', array(), 'log-file-name');
LoggerHelper::write('Login info:', ['username'=>'Admin', 'password'], 'logger');
```

then you can find log file

path/to/project/storage/log/logger-2017-10-12.log

```
[2017-10-12 14:22:54] local.DEBUG: file: web.php line: 16 message: Login info: {"username":"Admin","0":"password"}  
```

happy hacking!