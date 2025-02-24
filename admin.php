<?php
/*
    Страница администратора
*/
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});
use Lib\Template;
use Lib\User;
use Lib\DB;
session_start();
$user = new User();
$db = new DB();

if ($user->auth()) {
    header('Location: admin.php');
}

if ($user->getAuthorized() && $_GET['action'] == 'logout') {
    $user->logout();
    header('Location: admin.php');
}
if ($user->getAuthorized() && $_POST['action'] == 'form-data') {
    $id = intval($_POST['id']);
    $params = [
        $_POST['name'],
        $_POST['description'],
        intval($_POST['parent_id']),
    ];
    $sql = 'INSERT INTO `tree_data` (`name`, `description`, `parent_id`) VALUES (?, ?, ?)';
    $msg = 'Запись добавлена';
    if ($id > 0) {
        $params[] = $id;
        $sql = 'UPDATE `tree_data` SET `name` = ?, `description` = ?, `parent_id` = ? WHERE data_id = ?';
        $msg = 'Запись обновлена';
    }
    
    $r = $db->execute($sql, $params);
    header('Location: admin.php?msg='.$msg);
}

if ($user->getAuthorized() && $_GET['action'] == 'delete') {
    function deleteTree($id) {
        global $db;
        $res = $db->execute("select data_id from tree_data where parent_id = ?", [$id]);
        while ($row = $res->fetch()) {
            deleteTree($row['data_id']);
        }
        $db->execute("delete from tree_data where data_id = ?", [$id]);
    }
    $id = intval($_GET['id']);
    if ($id > 0) {
        deleteTree($id);
        header('Location: admin.php?msg=Ветка дерева удалена');
    }
}

$template = new Template('Страница администратора', 'Структура данных', $user);
$template->show_head();


if (!$user->getAuthorized()) {
    $template->auth_form();
} else {
    $template->message();
    $id = intval($_GET['id']);
    $params = $parents = $treeData = [];
    $res = $db->query("select * from tree_data order by parent_id, data_id");
    while ($row = $res->fetch()) {
        if ($id == $row['data_id']) {
            $params = $row;
        } else {
            $parents[$row['data_id']] = $row['name'];
        }
        if (isset($treeData[$row['parent_id']])) {
            $treeData[$row['parent_id']][] = $row['data_id'];
        } else {
            $treeData[$row['parent_id']] = [$row['data_id']];
        }
        
    }
    $template->data_form($id, $params, $parents);
    if ($id == 0) {
        $template->tree(0, $treeData, $parents);
    }
}

$template->show_footer();