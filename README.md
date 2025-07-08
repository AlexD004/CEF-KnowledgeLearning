# Knowledge Learning

E-Learning website built with Symfony and Stripe.

## :magic_wand: Features

- :sparkles: Symfony
- :sparkles: Stripe
- :sparkles: fosckeditor

## :building_construction: Getting Started

### :page_facing_up: Prerequisites

You'll need :
- A local web server like [WampServer](https://wampserver.aviatechno.net/) or an equivalent stack (Apache, MySQL, PHP)
- **PHP** version **>= 8.1**
- **Composer** (dependency manager for PHP)
- **Symfony CLI** (optional but recommended): [https://symfony.com/download](https://symfony.com/download)
- **MySQL** or compatible database system
- **Stripe CLI** (for testing payment and webhook integration): version **>= 1.10.0** recommended
- A [Mailtrap](https://mailtrap.io/) account (for email testing)
- **mpdf** (for PDF certificate generation)

### Stripe CLI Setup

After installing the Stripe CLI, run the following command to forward webhooks to your local environment:

```sh
stripe listen --forward-to localhost:8000/webhook/stripe
```

### :hammer: Installation

1. First download all files or, from your command line,  clone the project :

```sh
# Clone repository
$ git clone https://github.com/AlexD004/CEF-KnowledgeLearning.git
```

2. After that, you have to save all files in Wamp folders.

a. Locate the good folder
📂 C: --> 📂 wamp64 --> 📂 www 

b. Create a new site
In 📂 www, create a new folder named Knowledge-Learning
The new path is 📂 C: --> 📂 wamp64 --> 📂 www --> 📂 Knowledge-Learning

c. Place files
You can now place all content of 'CEF_Knowledge-Learning' folder that you just downloaded into the folder 📂 Knowledge-Learning

3. Upload the database

a. Start wampserver
b. In your browser, visit the URL : localhost/
c. Open 'PhpMyAdmin' (link below 'Your Aliases')
d. Click 'Import' tab and upload the file "knowledgelearning.sql" to create the database

4. Open the project on your favorite IDE

a. Create a file named '.env.local' and copy / paste info from the pdf received with the url (only for CEF homework, you can config the project as you want...)
b. Send command on terminal  to launch the app :
```sh
symfony server:start
```

### :test_tube: Setup test database

Before running PHPUnit tests, create an empty test database:

```sh
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
# or use schema:create if you're not using migrations bin/console doctrine:migrations:migrate --env=test
```

php bin/phpunit
