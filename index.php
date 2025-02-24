<?php
/*
    Страница пользователя
*/
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});
use Lib\Template;
use Lib\DB;
session_start();
$db = new DB();

$template = new Template('Страница пользователя', 'Структура данных', null);
$template->show_head();

$params = $parents = $treeData = $rows = [];
$res = $db->query("select * from tree_data order by parent_id, data_id");
while ($row = $res->fetch()) {
    $parents[$row['data_id']] = $row['name'];
    if (isset($treeData[$row['parent_id']])) {
        $treeData[$row['parent_id']][] = $row['data_id'];
    } else {
        $treeData[$row['parent_id']] = [$row['data_id']];
    }
    $rows[] = $row;
}
$template->showColums($template->freeTree(0, $treeData, $parents), $template->descriptions($rows));

$template->show_footer();