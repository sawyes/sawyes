# Some libary for dev in laravel 5.*

## Installation

```
composer require sawyes/sawyes
```

## Basic Usage


#### 1.LoggerHelper

* import namespace in your program

```
use Sawyes\Log\LoggerHelper;
```

* usage
```
// write('message', array(), 'log-file-name');
LoggerHelper::write('Login info:', ['username'=>'Admin', 'password'], 'logger');
```

then you can find log file

> path/to/project/storage/log/logger-2017-10-12.log


```
[2017-10-12 14:22:54] local.DEBUG: file: web.php line: 16 message: Login info: {"username":"Admin","0":"password"}  
```


#### 2.Log SQL Service Provider

* add provider services in config/app.php 
> laravel >=5.5 skip this step .

```
'providers' => [
    ...
    Sawyes\LogSqlServiceProvider::class,
    ...
    ]
```

* publishes config to enable log

<= 5.4
```
php artisan vendor:publish --tag='Sawyes\LogSqlServiceProvider'
```

>5.4
check package php artisan package:discover , then run below command choice Sawyes\LogSqlServiceProvider
```
php artisan vendor:publish
```

* configure env file
write APP_DEBUG_LOG=true in your .env file


now your can find log file in your storage path!


#### 3. schedule run lists

* add provider services in config/app.php 
> laravel >=5.5 skip this step .

```
'providers' => [
    ...
    Sawyes\SchedulelistServiceProvider::class,
    ...
    ]
```

* show schedule list
```
php artisan schedule:detail
```

* publishes config to enable log
> you can skip thie step, it's save schedule:detail in database using config file

<= 5.4
```
php artisan vendor:publish --tag='Sawyes\SchedulelistServiceProvider'
```

>5.4
check package php artisan package:discover , then run below command choice Sawyes\LogSqlServiceProvider
```
php artisan vendor:publish
```

you need to determine connection name, schedule list table, schedule detail list table

run below command save schedule list when end of day.

```
schedule:detail --database=true --start=tomorrow
```


happy hacking!