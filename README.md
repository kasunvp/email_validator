Email Bulk Validator (and parser) using mailgun/mailgun-php
===================================

# Initial setup 
* Install Composer (if you have not already installed composer)
> curl -sS https://getcomposer.org/installer | php

* Install dependency with composer using existing composer.json (currently using mailgun/mailgun-php:1.7)
>php composer.phar install

* Settings
> change $mailgunPubkey to your own in validator.php file.

*  Important directories and files.

    >Be sure that contacts.csv file exists in a directory called 'input' in the project's root directory.
    
    >Create output directory if it does not exist in the root of the project's directory.

# How to use?
*  Issue the following command to validate and filter out valid emails and separate the output in to .csv files (default count is 50000 mails per file).

    > php validator.php
    
*   The output will be created in the output directory.
