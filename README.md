# SPIS

Hello fellow developers!

### Building for development
First you must clone the repository.
-       git clone https://github.com/Miyunecadz/slsu_spis.git

Change directory to **slsu_spis** folder.
Create a copy of **.env.example** and rename it to **.env**

Run the composer command (be sure that composer is installed in your machine)
-       composer install
This command will install the dependencies for development and production.

Run generate APP key
-       php artisan key:generate

Add JWT secret
-       php artisan jwt:secret

### Migrating the database
First configure the database. You can find the credentials in **.env** file.
-     DB_CONNECTION=mysql
-     DB_HOST=127.0.0.1
-     DB_PORT=3306
-     DB_DATABASE=slsu_spis
-     DB_USERNAME=root
-     DB_PASSWORD=

Create database in your mysql server, named it **slsu_spis**. (Database name should be the same as the DB_DATABASE value of your .env)

Run command
-       php artisan migrate --seed
This will also migrate dummy data.

Note: Make sure the **slsu_spis** app is inside the htdocs or www of your apache server.
--------------------

### Route Lists

| Need Token  | Method      | URL                    | Description                            |
| ----------- | ----------- | ---------------------- | -------------------------------------- |
| NO          | POST        | /api/auth/login        | Authenticate user                      |
| YES         | POST        | /api/auth/logout       | Logout user                            |
| YES         | GET         | /api/scholars          | Return list of scholars                |
| YES         | POST        | /api/scholars          | Register scholars                      |
| YES         | GET         | /api/scholars/{id}     | Return specific of scholars            |
| YES         | PUT         | /api/scholars/{id}     | Update scholar information             |
| YES         | DELETE      | /api/scholars/{id}     | Delete specific scholar information    |
| NO          | POST        | /api/auth/password-request| Return code and send sms            |


For SMS, need to register in Twilio (free trial is okay) and set it to .env file the following

TWILIO_SID={SID provided by Twilio}

TWILIO_TOKEN={Token provided by Twilio}

TWILIO_FROM={Number provided by Twilio}
