UNIT TESTS SYMFONY3
==
Example of a unit test of a complex class with multiple dependencies.

Installation
-
* First, clone this repository: 
```
git clone https://github.com/oumarkonate/unit_tests_symfony3.git
```

* Next, install composer into root directory ```unit_tests_symfony3```: 
```
cd unit_tests_symfony3
```
```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```
See the following link for manual download: ```https://getcomposer.org/download/```

* Next, generate project dependencies: 
```
composer update
```

Covered files
-
* Covered files are located in direcory: ```src/AppBundle/Security```
* Unit tests are located in directory : ```tests/AppBundle/Security```

Run unit tests
-
Run the following command into root directory:
```
./vendor/bin/phpunit
```




