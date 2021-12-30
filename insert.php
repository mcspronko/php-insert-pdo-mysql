<?php

$data = $_POST;

// validate required fields
$errors = [];
foreach (['email', 'firstname', 'lastname', 'password'] as $field) {
    if (empty($data[$field])) {
        $errors[] = sprintf('The %s is a required field.', $field);
    }
}
if (!empty($errors)) {
    echo implode('<br />', $errors);
    exit;
}

//validate email
$email = $data['email'];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Invalid email format';
}

//database connect
$host = 'localhost';
$database = 'php_insert';
$user = 'root';
$pass = '';
$dsn = sprintf("mysql:host=%s;dbname=%s;", $host, $database);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
// check email
$statement = $pdo->prepare('SELECT * FROM user WHERE email = :email');
$statement->execute(['email' => $data['email']]);

if (!empty($statement->fetch())) {
    echo 'User with such email exists.';
    exit;
}
//insert new user
$statement = $pdo->prepare(
    'INSERT INTO user (email, firstname, lastname, password) VALUES (:email, :firstname, :lastname, :password)'
);
$statement->execute([
    'email' => $data['email'],
    'firstname' => $data['firstname'],
    'lastname' => $data['lastname'],
    'password' => password_hash($data['password'], PASSWORD_BCRYPT)
]);

echo 'The user has been successfully saved.';
