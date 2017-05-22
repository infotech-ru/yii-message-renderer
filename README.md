Шаблонизация сообщений
======================

Практически в каждом продукте требуется функционал отправки сообщений (email, sms и т.п.)
на основании пользовательских шаблонов.

Эта библиотека предоставляет концептуальное решение задач, связанных с шаблонизацией сообщений,
таких как:
- определение контекста отрисовки сообщения (определяющего набор подстановок и порядок их
  извлечения из моделей или результата запроса к БД);
- собственно отрисовку сообщения (наполнение шаблона данными);
- вывод помощи по подстановкам;
- потоковую отрисовку большого количества сообщений (с использованием `CDataProviderIterator`).

Использование
-------------

Чтобы подключить библиотеку к продукту запросим его через composer

```bash
php composer.phar require infotech/yii-message-renderer
```

Ключевым элементом является компонент приложения `MessageRendererComponent`.

Для использования компонента необходимо подключить его в конфигурации приложения:

```php
    ...
    'components' => array(
        'messageRenderer' => array(
            'class' => 'Infotech\MessageRenderer\MessageRendererComponent',
            'contexts' => [
                'SomeMessageContext',
                'AnotherMessageContext',
            ]
        ),
        ...
    ),
    ...
```

где `SomeMessageContext` и `AnotherMessageContext` имена классов контекстов отрисовки сообщений,
о которых расскажем чуть ниже.

Теперь для отрисовки одного сообщения достаточно написать

```php
    Yii::app()->messageRenderer->render($contextType, $templateStringOrArray, $data);
```

а для отрисовки множества сообщений нужно использовать

```php
    foreach (Yii::app()->messageRenderer->renderBatch($contextType, $templateStringOrArray, $dataProvider) as $message) {
        // тип данных $message зависит от реализации метода `renderTemplate()` контекста
    }
```

Контекст отрисовки сообщений
-----------------------------

Каждый класс контекста отрисовки сообщений является производным от абстрактного
`Infotech\MessageRenderer\MessageContext` и определяет состав подстановок и способ извлечения
данных.

В интерфейсе класса контекста есть метод `renderTemplate($templateStringOrArray, $data)`, который
принимает строку шаблона (или массив шаблонных строк) и данные для наполнения шаблона, 
а возвращяет отрисованную строку (или такой же массив отрисованных строк с теми же ключами).

Приведем пример класса контекста. Предположим у нас есть модель `User`, а также `Task` с
отношениями "assignee" (*BELONGS_TO* к `User`) и "reporter" (*BELONGS_TO* к `User`).

```php
class TaskMessageContext extends \Infotech\MessageRenderer\MessageContext
{

    public function placeholdersConfig()
    {
        return array(
            '_НОМЕР_ЗАДАЧИ_' => array(
                'title' => 'Номер задачи',
                'description' => 'Номер задачи. Например, "#142"',
                'fetcher' => function (Task $task) { return '#' . $task->id; },
            ),
            '_СТАТУС_ЗАДАЧИ_' => array(
                'title' => 'Статус задачи',
                'description' => 'Статус задачи. Например, "выполняется"',
                'fetcher' => 'statusName',
            ),
            '_ТЕМА_ЗАДАЧИ_' => array(
                'title' => 'Тема задачи',
                'description' => 'Тема задачи. Например, "Увеличить логотип на главной странице"',
                'fetcher' => 'subject',
            ),
            '_ИМЯ_ИСПОЛНИТЕЛЯ_' => array(
                'title' => 'Имя исполнителя',
                'description' => 'Имя сотрудника, на которого назначена задача (в именительном падеже). Например, "Василий Кузнецов"',
                'fetcher' => 'assignee.full_name',
                'empty' => '(не назначен)',
            ),
            '_ИМЯ_ПОЛЬЗОВАТЕЛЯ_' => array(
                'title' => 'Имя пользователя',
                'description' => 'Имя сотрудника, выполняющего действие над задачей (в именительном падеже). Например, "Константин Отрубов"',
                'fetcher' => function () { return Yii::app()->getUser()->getModel()->fullName; },
            ),
            '_ПОЧТА_ПОЛЬЗОВАТЕЛЯ_' => array(
                'title' => 'E-mail пользователя',
                'description' => 'E-mail сотрудника, выполняющего действие над задачей. Например, "otrubov@example.com"',
                'fetcher' => function () { return Yii::app()->getUser()->getModel()->email; },
            ),
        );
    }

    public function getType()
    {
        return 'task';
    }

    public function getName()
    {
        return 'Задачи';
    }
}
```

Ну забудем зарегистрировать контекст в конфигурации компонента.

Теперь, чтобы отправить email уведомление об изменении статуса задачи напишем

```php
$task = ...; // задача, изменившая статус
// достали шаблон из БД или иного источника
$template = array( 
    'message' => '_ИМЯ_ПОЛЬЗОВАТЕЛЯ_ перевел задачу в статус "_СТАТУС_ЗАДАЧИ_"',
    'subject' => 'Изменение статуса задачи №_НОМЕР_ЗАДАЧИ_',
    'from' => 'notifier@example.com',
    'to' => '_ПОЧТА_ПОЛЬЗОВАТЕЛЯ_',
);

$emailData = Yii::app()->messageRenderer->render('task_issue', $template, $task);

Yii::app()->mailer->send($emailData['message'], $emailData['subject'], $emailData['to'], $emailData['from']);
```

а для групповой отправки, пишем

```php
$tasks = ...; // Traversable с задачами, изменившими статус
// достали шаблон из БД или иного источника
$template = array( 
    'message' => '_ИМЯ_ПОЛЬЗОВАТЕЛЯ_ перевел задачу в статус "_СТАТУС_ЗАДАЧИ_"',
    'subject' => 'Изменение статуса задачи №_НОМЕР_ЗАДАЧИ_',
    'from' => 'notifier@example.com',
    'to' => '_ПОЧТА_ПОЛЬЗОВАТЕЛЯ_',
);

$messagesIterator = Yii::app()->messageRenderer->renderBatch('task_issue', $template, $tasks);

foreach ($messagesIterator as $emailData) {
    if ($emailData['to']) {
        Yii::app()->mailer->send($emailData['message'], $emailData['subject'], $emailData['to'], $emailData['from']);
    }
}
```
