Добрый день!

---

По заданию имеется система настроек пользователя и необходимо реализовать систему подтверждения смены конкретной настройки пользователя по коду из смс / email / telegram с возможностью выбора пользователем другого метода

---

По итогу написан некий абстрактный код, который, в теории, можно прикрутить к любой системе. Фреймворк здесь используется только для удобства, чтобы было знакомое окружение и можно было показать связь роутингов и контроллеров. Основной код находится в `App\Entity`; `App\Http\Controllers`; `App\TfaProvider`. У каждого класса в PHPDoc находятся как описание класса, так и описание конкретных методов. Комментарии в коде на русском языке в рамках задания, обычно, естественно, комментирую на английском языке. Роутинги прописаны в `routes/web.php`.
Так как не используются реальная база данных и ORM фреймворк, то в `App\Entity` находятся абстрактные сущности. В классе есть метод `getStructure` - для наглядности он описывает структуру таблицы в базе данных. Так же, будем считать, что параметры запроса предварительно валидированы.

Список сущностей:
- User - основная информация о пользователе
- Option - список возможных настроек, которые могут присутствовать в системе и их параметры. Например, дефолтные способы подтверждения, разрешенные способы, и так далее.
- TfaProvider - список возможных 2fa провайдеров и их настройки
- UserTfa - настроенные пользователем 2fa провайдеры
- UserOptionTfa - опции настроек. Выбранные пользователем методы 2fa для настроек

Контроллеры:
- AccountTfa - страницы и экшны связанные с настройкой 2fa. Добавление/удаление/редактирование. При изменении так же может быть запрошено 2fa подтверждение
- AccountOptions - страница и экшны связанные со списком опций настроек (`Entity\UserOption`). Здесь юзер для каждой настройки выбирает доступные методы подтверждения

Хендлеры 2fa провайдеров находятся в `App\TfaProvider`. Здесь находятся классы для каждого из существующих `Tfa\Provider`.