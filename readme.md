<p align="center"><img src="https://www.engency.com/assets/img/logo.png" height="50px"></p>

## Engency toolkit

### Docker webserver images

All images include;
- php 7.4 with APCu, pdo, mysql, sqlite, gd, tidy
- apache 2.4 with mod_rewrite
- ssh client
- git client

### Non-debug images include;
- opcache

### Debug images include;
- xdebug

### Environment variables

|variable|images|description|default|
|---|---|---|---|
|TZ|*|Timezone|Europe/Amsterdam|
|PHP_OPCACHE_VALIDATE_TIMESTAMPS|^(?!debug).*|Whether opcache should validate timestamps.|0|
|XDEBUG_TRIGGER|^debug.*|Setting the xdebug.profiler_enable_trigger_value value.|salkdn9e4s8thasd3uslf|
|XDEBUG_CONFIG|^debug.*|Add values to xdebug configuration.|remote_host=172.17.0.1|
|PHP_IDE_CONFIG|^debug.*|Custom configuration for use with IDEs. For instance, name the service so that phpstorm will recognise it in a debugging session.|serverName=webserver|