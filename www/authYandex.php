<?php
// Идентификатор приложения
$client_id = 'f05ba99bcbf441a69565e75c36547574';
// Пароль приложения
$client_secret = '07e9facd26484877a2403affc7068a77';

// Если скрипт был вызван с указанием параметра "code" в URL,
// то выполняется запрос на получение токена
if (isset($_GET['code'])) {
    // Формирование параметров (тела) POST-запроса с указанием кода подтверждения
    $query = array(
        'grant_type'    => 'authorization_code',
        'code'          => $_GET['code'],
        'client_id'     => $client_id,
        'client_secret' => $client_secret
    );
    $query = http_build_query($query);

    // Формирование заголовков POST-запроса
    $header = "Content-type: application/x-www-form-urlencoded";

    // Выполнение POST-запроса и вывод результата
    $opts = array('http' =>
                      array(
                          'method'  => 'POST',
                          'header'  => $header,
                          'content' => $query
                      )
    );
    $context = stream_context_create($opts);
    $result = file_get_contents('https://oauth.yandex.ru/token', false, $context);
    $result = json_decode($result);

    // Токен необходимо сохранить для использования в запросах к API Директа
    echo $result->access_token;
} else {
    header('Location: https://oauth.yandex.ru/authorize?response_type=token&client_id=' . $client_id);
    exit;
}
?>