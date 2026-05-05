# Terraria's journey tracker

This project is ment do make easy to trace the progression of a `Journey Mode Player` and also serve as a chat platform to discuss abot some strategy, trivia, jokes, etc... abot the game.

---

## Built With

- PHP (>= 8.5)
- Slim Framework
- Composer
- PSR-7 / PSR-15 Middleware

---

![Demo](https://img.shields.io/badge/Status-Ready-green) 
![Demo](https://img.shields.io/badge/PHP8.4+-Requiere-yellow)
![Demo](https://img.shields.io/badge/Composer-Requiere-yellow)
![Demo](https://img.shields.io/badge/Mariadb-Requiere-yellow)
![Demo](https://img.shields.io/badge/Apach2-Requiere-yellow)
![Demo](https://img.shields.io/badge/Motivation-Optional-blue)
![Demo](https://img.shields.io/badge/Test-Missing-red)
![Demo](https://img.shields.io/badge/Git-Back-darkgreen)

## Installation

Clone the repository:

```bash
git clone https://github.com/your-username/your-repo.git
cd your-repo
```

or use the .zip file

Install dependencies:

```bash
composer install
```

**Important note**: Make sure to unzip the public/img.zip file in the public/img/, but make sure to not have a img directory in the path to not have duplicate like public/img/img/.
This step is required to have all the necessary images for the application.

---

## Configuration

### Put the database in `sql/chat_V2.sql` in your mariadb database server

### Copy `database.sample.php` to `database.php` and update values:

```bash
cp config/database.sample.php config/database.php
```

### Set your environment variables (DB, API keys, etc).

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'chat_V2');
define('DB_USER', ''); // <--- here, your mariadb user
define('DB_PASSWORD', ''); // <--- here, your mariadb password
define('DB_CHARSET', 'utf8mb4');
```

---

## Run the Application

Using PHP built-in server:

```bash
composer launch-server
```

**Skip this part because there isn't a docker munted for now**

Or with Docker (if applicable):

```bash
docker-compose up -d
```

---

## Routes

### Main entry point 

| Method | Endpoint | Description                                         |
| ------ | -------- | --------------------------------------------------- |
| GET    | /        | Display the home page where the progress tracker is |

### Profile routes

| Method | Endpoint            | Description                                                       |
| ------ | ------------------- | ----------------------------------------------------------------- |
| GET    | /profile            | Display the profile page of the user                              |
| POST   | /profile/pfp/add    | Change the profile picture of the user                            |
| POST   | /profile/pfp/delete | Remove the profile picture of the user and pur the google default |
| POST   | /profile/delete     | Delete the user acount                                            |

### Authentication routes 

| Method | Endpoint  | Description                                            |
| ------ | --------- | ------------------------------------------------------ |
| GET    | /login    | Display the login form                                 |
| POST   | /login    | Handle the connexion from the data form the login form |
| GET    | /register | Display the register form                              |
| POST   | /register | Handle the creation on a new user                      |
| GET    | /logout   | Disconnect the current connected user                  |

### Chat routes 

| Method | Endpoint                           | Description                                                    |
| ------ | ---------------------------------- | -------------------------------------------------------------- |
| GET    | /chats                             | Display the list of all chats                                  |
| GET    | /chats/json                        | Return the list of all chat in json form                       |
| GET    | /chats/new                         | Display the form to create a new chat                          |
| POST   | /chats/new                         | Handle the creation of a new chat                              |
| GET    | /chat/{id}                         | Display the chat thread                                        |
| GET    | /chat/{id}/exists                  | Return if the chat with the parameter id exist or not          |
| POST   | /chats/{id}/delete                 | Delete the chat from the list                                  |
| GET    | /chat/{id}/messages                | Return all the messages from the chat with the id in parameter |
| POST   | /chat/{id}/messages                | Handle the sending of messages                                 |
| POST   | /chat/{id}/messages/{msgId}/delete | Delete a message from is id                                    |
| POST   | /chat/{id}/messages/{msgId}/edit   | change the content of a message from is id                     |

### User file routes 

| Method | Endpoint           | Description                          |
| ------ | ------------------ | ------------------------------------ |
| GET    | /user/files        | Display the .plr files of the user   |
| POST   | /user/files/upload | Handle the upload of a new .plr file |
| POST   | /user/files/delete | Handle the delete of a .plr file     |


---

## Project Structure

```
Terraria-progress-tracker-&-chat/
┣━╸config/
┃  ┣━╸database.sample.php
┃  ┗━╸database.php
┣━╸img/*
┣━╸public/
┃  ┣━╸upload/*
┃  ┣━╸users/*
┃  ┣━╸.htaccess
┃  ┣━╸category_tree.json
┃  ┣━╸index.php
┃  ┣━╸item.json
┃  ┣━╸items_with_urls.json
┃  ┣━╸progress_temp.json
┃  ┗━╸putCategoryToData.py
┣━╸routes/
┃  ┗━╸web.php
┣━╸sql/
┃  ┣━╸chat_V1.sql
┃  ┗━╸chat_V2.sql
┣━╸src/
┃  ┣━╸Controller/
┃  ┃  ┣━╸AuthController.php
┃  ┃  ┣━╸BaseController.php
┃  ┃  ┣━╸ChatController.php
┃  ┃  ┣━╸HomeController.php
┃  ┃  ┗━╸UserController.php
┃  ┣━╸Core/
┃  ┃  ┗━╸Database.php
┃  ┗━╸Model
┃     ┣━╸Chat.php
┃     ┣━╸Message.php
┃     ┗━╸User.php
┣━╸vendor/*
┣━╸views/
┃  ┣━╸chat/
┃  ┃  ┣━╸chat.php
┃  ┃  ┣━╸list.php
┃  ┃  ┗━╸new.php
┃  ┣━╸errors/
┃  ┃  ┣━╸400.php
┃  ┃  ┣━╸404.php
┃  ┃  ┗━╸500.php
┃  ┣━╸home/
┃  ┃  ┗━╸home.php
┃  ┣━╸login/
┃  ┃  ┣━╸login.php
┃  ┃  ┗━╸register.php
┃  ┣━╸user/
┃  ┃  ┣━╸playerFiles.php
┃  ┃  ┗━╸profile.php
┃  ┣━╸layout.php
┃  ┗━╸menu.php
┣━╸composer.json
┣━╸composer.lock
┣━╸Licence
┣━╸README
┗━╸Terraria Research Tracker 1.4.5.html
```

---

## Running Tests

**Skip this part because there aren't tests for now**

```bash
vendor/bin/phpunit
```

---

## Security

If you discover a security vulnerability, please send an email instead of opening an issue.

---

## Contributing

1. Fork the repo
2. Create your feature branch (`git checkout -b feature/foo`)
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

---

## License

This project is licensed under the MIT License.

---

## Acknowledgements

* Slim Framework
* PHP Community

---

## Author

**Name:** Mommy-Of-Light
**E-mail:** empress.mommy.of.light@gmail.com
