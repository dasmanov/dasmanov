<?php if(!defined('ACCESS_CODE') && intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

$module_name = explode("/",str_replace("\\","/",dirname(__FILE__)));
$module_name = $module_name[sizeof($module_name)-1];

$module_cat_name = 'categories_'.$module_name;

$cur_module = &$this->module[$module_name];
$cur_fields = &$cur_module->fields;
$cur_titles = &$cur_fields->titles;

$cur_fields->table->title = $module_name;
$cur_fields->table->name = $module_name;
$cur_fields->table->multiline = 1;

$cur_module->title = 'Системные пользователи';

$cur_titles->view = 'Пользователи';
$cur_titles->add = 'Добавить пользователя';
$cur_titles->edit = 'Редактирование пользователя';
$cur_titles->delete = 'Удалить пользователя?';
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "id";
$cur_field->db->type = "int";
$cur_field->db->length = "11";

$cur_field->db->auto_increment = TRUE;
$cur_field->db->null = false;
$cur_field->db->value = "1";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "auto_increment";

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->params->label = "ID";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "parent";
$cur_field->db->type = "int";
$cur_field->db->length = "11";
$cur_field->db->null = TRUE;
$cur_field->db->value = "1";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "hidden";
if(!empty($_SESSION['user_id'])){
	$cur_field->form->value = $_SESSION['user_id'];
}

$cur_field->edit->edit = "1";

//$cur_field->params->label = "Родитель";
////////////////////////////////////////////////////////////////////////////////
/*$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "pid";
$cur_field->db->type = "int";
$cur_field->db->length = "11";
$cur_field->db->null = TRUE;
$cur_field->db->value = "1";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "select::".$module_cat_name."::id->name";

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";

$cur_field->params->label = "Группа";*/
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "groups_id";
$cur_field->db->type = "blob";
$cur_field->db->default = "NULL";
$cur_field->db->null = true;
$cur_field->db->value = '[1]';

$cur_field->form->name = $cur_field->db->field;
//$cur_field->form->type = "select::".$module_cat_name."::id->name";
$cur_field->form->type = '{"type":"list","from":"table","table":"'.$module_cat_name.'","field":{"value":"id","label":"name"},"tags":{"type":"checkbox"}}';
$cur_field->form->multiple = 'multiple';

$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->edit = "1";
$cur_field->edit->add = "1";

$cur_field->params->mandatory = "1";
$cur_field->params->label = "Группы";
$cur_field->params->filtering = 1;
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "name";
$cur_field->db->type = "varchar";
$cur_field->db->length = "200";
$cur_field->db->default = "NULL";
$cur_field->db->value = "Administrator";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "text";
$cur_field->form->size = 40;
$cur_field->form->autocomplete = 'off';

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";

$cur_field->params->label = "Имя";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "email";
$cur_field->db->type = "varchar";
$cur_field->db->length = "200";
$cur_field->db->default = "NULL";
$cur_field->db->value = "admin";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "text";
$cur_field->form->size = 40;
$cur_field->form->autocomplete = 'off';

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";

$cur_field->params->label = "E-mail";
$cur_field->params->mandatory = "1";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "md5_email";
$cur_field->db->type = "varchar";
$cur_field->db->length = "32";
$cur_field->db->default = "NULL";
$cur_field->db->value = "21232f297a57a5a743894a0e4a801fc3";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "md5::email";

$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";

$cur_field->params->label = "";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "md5_password";
$cur_field->db->type = "varchar";
$cur_field->db->length = "32";
$cur_field->db->default = "NULL";
$cur_field->db->value = "21232f297a57a5a743894a0e4a801fc3";

$cur_field->form->name = "password";
$cur_field->form->type = "password::md5";
$cur_field->form->size = 40;
$cur_field->form->autocomplete = 'off';


$cur_field->show->add = "1";
$cur_field->show->edit = "1";

$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";

$cur_field->params->label = "Пароль";
$cur_field->params->mandatory = "1";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "active";
$cur_field->db->type = "tinyint";
$cur_field->db->length = "1";
$cur_field->db->null = TRUE;
$cur_field->db->value = "1";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "checkbox";
$cur_field->form->value = "1";
$cur_field->form->checked = "1";

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->align = "center";

$cur_field->edit->view = "1";
$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";
$cur_field->edit->delete = "1";

$cur_field->params->label = "Активный";
$cur_field->params->filtering = 1;
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "auth_code";
$cur_field->db->type = "varchar";
$cur_field->db->length = 32;
$cur_field->db->null = TRUE;

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "text";
$cur_field->form->size = $cur_field->db->length;

//$cur_field->show->view = "1";
//$cur_field->show->add = "1";
//$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->view = "1";
//$cur_field->edit->add = "1";
//$cur_field->edit->edit = "1";
$cur_field->edit->delete = "1";

$cur_field->params->label = 'Код авторизации';
//$cur_field->params->filtering = true;
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "last_login";
$cur_field->db->type = "datetime";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "datetime::default::current";
$cur_field->form->size = "19";
$cur_field->form->class = "datetimepicker";

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->view = "1";
$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";
$cur_field->edit->delete = "1";

$cur_field->params->label = "Дата последней авторизации";
$cur_field->params->filtering = true;
//$cur_field->params->export = 1;
////////////////////////////////////////////////////////////////////////////////
// Категории
////////////////////////////////////////////////////////////////////////////////
$cur_fields = &$cur_module->categories;
$cur_titles = &$cur_fields->titles;

$cur_fields->table->title = $module_cat_name;
$cur_fields->table->name = $module_cat_name;
$cur_fields->table->multiline = 1;

$cur_fields->max_level = 1;

$cur_titles->view = 'Группы пользователей';
$cur_titles->add = 'Добавить группу';
$cur_titles->edit = 'Редактирование группы';
$cur_titles->delete = 'Удалить группу';

////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "id";
$cur_field->db->type = "int";
$cur_field->db->length = "11";
$cur_field->db->null = false;
$cur_field->db->auto_increment = TRUE;
$cur_field->db->value = "1";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "auto_increment";

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->params->label = "ID";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "pid";
$cur_field->db->type = "int";
$cur_field->db->length = "11";
$cur_field->db->null = TRUE;
$cur_field->db->value = "0";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "select::parent::id->name";
//$cur_field->form->type = "select::".$cat_name."::id->name";

$cur_field->show->view = "0";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";

$cur_field->params->label = "Родительская группа";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "name";
$cur_field->db->type = "varchar";
$cur_field->db->length = "200";
$cur_field->db->default = "NULL";
$cur_field->db->value = "Администраторы";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "text";

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";

$cur_field->params->label = "Название группы";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "access";
$cur_field->db->type = "blob";
$cur_field->db->default = "NULL";
$cur_field->db->null = "true";
$cur_field->db->value = '{"'.$module_name.'":{"view":1,"add":1,"edit":1,"delete":1}}';
//$cur_field->db->value = $module_name."::array(view=>1,add=>1,edit=>1,delete=>1);";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "access";

$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";

$cur_field->params->label = "Права";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "order";
$cur_field->db->type = "int";
$cur_field->db->length = "11";
$cur_field->db->default = "NULL";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "text";

$cur_field->params->label = "Позиция";
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "is_admin";
$cur_field->db->type = "tinyint";
$cur_field->db->length = "1";
$cur_field->db->null = TRUE;
$cur_field->db->value = "1";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "checkbox";
$cur_field->form->value = "1";
$cur_field->form->checked = "1";

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->align = "center";

$cur_field->edit->view = "1";
$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";
$cur_field->edit->delete = "1";

$cur_field->params->label = "Доступ к административной части";
$cur_field->params->filtering = 1;
////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "active";
$cur_field->db->type = "tinyint";
$cur_field->db->length = "1";
$cur_field->db->null = TRUE;
$cur_field->db->value = "1";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "checkbox";
$cur_field->form->value = "1";
$cur_field->form->checked = "1";

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->align = "center";

$cur_field->edit->view = "1";
$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";
$cur_field->edit->delete = "1";

$cur_field->params->label = "Активный";
$cur_field->params->filtering = 1;
////////////////////////////////////////////////////////////////////////////////

unset($cur_field);unset($cur_fields);
?>
