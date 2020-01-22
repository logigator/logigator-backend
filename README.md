# logigator-backend

## Getting Started
### Prerequisites
You need PHP 7.2 or newer and a MariaDB or MySQL database installed on your machine.

### Installation
Download the contents of the repository, open the command line in that directory and install it's dependencies.
```
git clone git@github.com:logigator/logigator-backend.git 
composer install
```

Copy the contents of `logigator-backend/config.php.example` to `logigator-backend/config.php` and fill in the missing information. 
If you leave out the Google and Twitter Keys, Google and Twitter Login will not work. If the email accounts are not field in it won't be possible to register at all. 

### Setting up the database

Run `logigator-backend/db_create.sql` in your database. It will create a user 'logigator' and a database 'logigator', you can change this to whatever you configured in config.php.

`logigator-backend/inserts.sql` will create a system user that is used to store demo projects and insert demo projects in the database.

`logigator-backend/inserts_dev.sql` will create a test user, that you can use to log in.
