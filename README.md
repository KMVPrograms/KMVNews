KMVNews
=======

Вступление
----------
Достаточно простой сайт новостей на ZF2.
Позволяет просматривать, искать и комментировать новости.
В режиме администратора позволяет добавлять, редактировать и удалять новости,
а также модерировать коментарии.

Установка
---------

1)
==
Вариант 1. Используя Composer (рекомендуется)
---------------------------------------------
Это рекомендованный путь получения рабочей версии проекта (копирование из репозитория и
использование `composer` для установки дополнительных элементов используя комманду `create-project`

    curl -s https://getcomposer.org/installer | php --
    php composer.phar create-project --repository-url="https://github.com" KMVPrograms/KMVNews путь/для/установки

Вариант 2. Альтернативный
-------------------------
Копируем репозиторий и вручную вызываем скачанный `composer`.

    cd путь/для/установки
    git clone https://github.com/KMVPrograms/KMVNews.git
    php composer.phar self-update
    php composer.phar install

Для вариантов 1 и 2 у Вас должен быть установлен и прописан в путях Git (http://git-scm.com/downloads).

Вариант 3.
----------
Скачать репозиторий с https://github.com/KMVPrograms/KMVNews. Распаковать в нужную папку. И выполнить:

    php composer.phar self-update
    php composer.phar install

2)
==
Создание виртуального адреса
----------------------------
После установки KMVNews, задайте виртуальный адрес указывающий на директорию public/
проекта и Вы почти готовы к запуску.

3)
==
Подключение к базе данных (тестировалось на MySQL)
--------------------------------------------------
Создается новая база данных. Добавляется пользователь с полными правами к этой базе.
Прописывается в проекте имя базы (/config/autoload/global.php, по умолчанию kmvnews),
пользователь и пароль (/config/autoload/local.php).

После этого, необходимо создать таблицы в созданной базе:

    CREATE TABLE IF NOT EXISTS news (
        id INTEGER AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        dt DATETIME NOT NULL,
        text LONGTEXT
    ) CHARACTER SET utf8;

    CREATE TABLE IF NOT EXISTS comments (
        id INTEGER AUTO_INCREMENT PRIMARY KEY,
        dt DATETIME NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        text LONGTEXT,
        pict MEDIUMBLOB,
        nid INTEGER NOT NULL,
        status INTEGER(1),
        FOREIGN KEY(nid) REFERENCES news (id) ON DELETE CASCADE
    ) CHARACTER SET utf8;

4)
==
Авторизация администратора 
--------------------------
В файле /config/autoload/admin.local.php прописываются логин и пароль администратора сайта.

Всё. Можно приступать к запуску сайта.