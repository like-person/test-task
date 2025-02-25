<?php
namespace Lib;
/*
    User - класс для авторизации администратора
*/
class User {
    private $login;
    private $autorized;

    //Если таблица пользователей пустая можно константу FIRST поставить true, чтобы при вводе логина/пароля регистрировался администратор
    const FIRST = false;
    public function __construct() {
        if ($_SESSION['AUTHORIZED']) {
            $this->autorized = true;
            $this->login = $_SESSION['LOGIN'];
        }
    }
    public function getAuthorized() {
        return $this->autorized;
    }
    public function getLogin() {
        return $this->login;
    }
    public function auth() {
        global $db;
        $result = false;
        if ($_POST['login'] && $_POST['password']) {
            $pwd = MD5($_POST['login'].$_POST['password']); 
            if (self::FIRST) {
                $db->execute("INSERT INTO user (login, password, name) VALUES (?, ?, ?)", [$_POST['login'], $pwd, 'Admin']);
                $result = true;
            } else {
                $res = $db->execute("SELECT * FROM user WHERE login=? && password=?", [$_POST['login'], $pwd]);
                $data = $res->fetchAll();
                if (count($data) > 0) {
                    $result = true;
                }
            }
        }
        if ($result) {
            $_SESSION['AUTHORIZED'] = true;
            $_SESSION['LOGIN'] = $_POST['login'];
        }
        return $result;
    }
    public function logout() {
        $_SESSION['AUTHORIZED'] = false;
        unset($_SESSION['AUTHORIZED']);
    }
}