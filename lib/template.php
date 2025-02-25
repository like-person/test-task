<?php

namespace Lib;
/*
    Template - класс для вывода HTML, CSS, JSS
*/
class Template {
    private $title;
    private $head;
    private $user;
    public function __construct($title, $head, $user) {
        $this->title = $title;
        $this->head = $head;
        $this->user = $user;
    }
    public function show_head() {
        $userText = '';
        if ($this->user && $this->user->getAuthorized()) {
            $userText = '<div class="head"><b>'.$this->user->getLogin().'</b> Добро пожаловать! <a href="?action=logout">Выйти</a></div>';
        }
        $out = <<<TXT
        <html>
            <head>
                <title>{$this->title}</title>
                <style>
                .head {text-align: right;}
                .tree-node .tree-node {margin-left: 30px;}
                .hidden {display: none;}
                a {text-decoration: none;}
                .row {display: flex;}
                .column {width: 50%;}
                </style>
                <script>
    function addEvents() {
        const deleteActions = document.querySelectorAll(".delete");

        if (deleteActions) {
            deleteActions.forEach((deleteAction) => {
                deleteAction.addEventListener("click", (event) => {
                    if (window.confirm("Вы уверены что хотите удалить эту запись?")) {
                        location.href = this.attributes.href;
                    }
                    event.preventDefault();
                });
            });
        }

        const expandActions = document.querySelectorAll(".expand");
        if (expandActions) {
            expandActions.forEach((expandAction) => {
                expandAction.addEventListener("click", (event) => {
                    event.preventDefault();
                    let id = expandAction.dataset.id;
                    document.querySelectorAll(".node-" + id).forEach(node => {
                        node.classList.remove("hidden");
                    });
                });
            });
        }
        
        const descActions = document.querySelectorAll(".show-desc");
        if (descActions) {
            descActions.forEach((descAction) => {
                descAction.addEventListener("click", (event) => {
                    event.preventDefault();
                    let id = descAction.dataset.id;
                    document.querySelectorAll(".description").forEach(node => {
                        if (!node.classList.contains("hidden")) {
                            node.classList.add("hidden");
                        }
                    });
                    document.querySelector(".desc-" + id).classList.remove("hidden");
                });
            });
        }
    }
                </script>
            </head>
            <body onload="addEvents()">
                {$userText}
                <h1>{$this->head}</h1>
                <div>
TXT;
        echo $out;
    }
    public function show_footer() {
        $out = <<<TXT
                </div>
            </body>
        </html>
TXT;
        echo $out;
    }
    public function auth_form() {
        $out = <<<TXT
        <form action="" method="post">
        <table border="0" cellpadding="4">
        <tr><td>Логин: </td><td><input type="text" name="login" value="{$_POST['login']}" /></td></tr>
        <tr><td>Пароль: </td><td><input type="password" name="password" /></td></tr>
        <tr><td colspan="2"><input type="submit" value="Войти" /></td></tr>
        </table>
        </form>
TXT;
        echo $out;
    }
    public function data_form($id = 0, $params = [], $parents = []) {
        $parentOptions = '';
        $buttonText = 'Добавить';
        $headText = 'Добавление записи';
        foreach ($parents as $key => $value) {
            $parentOptions .= '<option value="'.$key.'"'.($params['parent_id'] == $key ? ' selected' : '').'>'.$key.': '.$value.'</option>';
        }
        if ($id > 0) {
            $buttonText = 'Сохранить изменения';
            $headText = 'Изменение записи №'.$id;
        }
        $out = <<<TXT
        <h2>{$headText}</h2>
        <form action="" method="post">
        <input type="hidden" name="action" value="form-data" />
        <input type="hidden" name="id" value="{$id}" />
        <table border="0" cellpadding="4">
        <tr><td>Название: </td><td><input type="text" name="name" value="{$params['name']}" required /></td></tr>
        <tr><td>Описание: </td><td><input type="text" name="description" value="{$params['description']}" /></td></tr>
        <tr><td>Родитель: </td><td><select name="parent_id"><option value="0">Корень</option>{$parentOptions}</select></td></tr>
        <tr><td colspan="2"><input type="submit" value="{$buttonText}" /></td></tr>
        </table>
        </form>
TXT;
        echo $out;
    }
    public function message() {
        if (!empty($_GET['msg'])) {
            echo '<div class="message">'.$_GET['msg'].'</div>';
        }
    }
    public function tree($parent, $treeData, $data) {
        if (isset($treeData[$parent])) {
            foreach ($treeData[$parent] as $child) {
                echo '<div class="tree-node">'.$data[$child].' [<a href="?id='.$child.'">редактировать</a> | <a href="?action=delete&id='.$child.'" class="delete">удалить</a>]';
                $this->tree($child, $treeData, $data);
                echo '</div>';
            }
        }
    }
    public function showColums($left, $right) {
        echo '<div class="row">';
        echo '<div class="column">'.$left.'</div>';
        echo '<div class="column">'.$right.'</div>';
        echo '</div>';
    }
    public function freeTree($parent, $treeData, $data) {
        $return = '';
        if (isset($treeData[$parent])) {
            foreach ($treeData[$parent] as $child) {
                $return .= '<div class="tree-node'.($parent > 0 ? ' hidden node-'.$parent : '').'"><a href="#" data-id="'.$child.'" class="show-desc">'.$data[$child].'</a>'.(isset($treeData[$child]) ? ' [<a href="#" data-id="'.$child.'" class="expand">+</a>]' : '');
                $return .= $this->freeTree($child, $treeData, $data);
                $return .= '</div>';
            }
        }
        return $return;
    }
    public function descriptions($data) {
        $return = '';
        foreach ($data as $item) {
            $return .= '<div class="description hidden desc-'.$item['data_id'].'">'.$item['description'].'</div>';
        }
        return $return;
    }
}