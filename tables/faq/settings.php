<?php if(!defined('ACCESS_CODE') && intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

	$module_name = explode("/",str_replace("\\","/",dirname(__FILE__)));
	$module_name = $module_name[sizeof($module_name)-1];

	//$module_cat_name = 'categories_'.$module_name;

	$cur_module = &$this->module[$module_name];
	$cur_fields = &$cur_module->fields;
	$cur_titles = &$cur_fields->titles;

	$cur_fields->table->title = $module_name;
	$cur_fields->table->name = $module_name;
	$cur_fields->table->multiline = 1;

	$cur_module->title = 'Часто задаваемые вопросы';

	$cur_titles->view = 'Список частых вопросов';
	$cur_titles->add = 'Добавление вопроса/ответа';
	$cur_titles->edit = 'Редактирование вопроса/ответа';
	$cur_titles->delete = 'Удаление вопроса/ответа';
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "id";
	$cur_field->db->type = "int";
	$cur_field->db->length = "11";
	$cur_field->db->auto_increment = TRUE;
	$cur_field->db->null = false;

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "auto_increment";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";
	$cur_field->show->align = "right";

	$cur_field->params->label = "ID";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "question";
	$cur_field->db->type = "text";
	$cur_field->db->null = TRUE;

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "text";
	$cur_field->form->size = "100";

	$cur_field->show->align = "left";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->view = "1";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";
	$cur_field->edit->delete = "1";

	$cur_field->params->label = 'Вопрос';
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "answer";
	$cur_field->db->type = "text";
	$cur_field->db->null = TRUE;

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "textarea";
	$cur_field->form->rows = "10";
	$cur_field->form->cols = "100";
	$cur_field->form->class = "editor";

	$cur_field->show->align = "justify";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->view = "1";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";
	$cur_field->edit->delete = "1";

	$cur_field->params->label = 'Ответ';
	////////////////////////////////////////////////////////////////////////////////
$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

$cur_field->db->field = "order";
$cur_field->db->type = "int";
$cur_field->db->length = "11";
$cur_field->db->default = "NULL";

$cur_field->form->name = $cur_field->db->field;
$cur_field->form->type = "text";
$cur_field->form->value = 500;

$cur_field->show->view = "1";
$cur_field->show->add = "1";
$cur_field->show->edit = "1";
$cur_field->show->delete = "1";

$cur_field->edit->view = "1";
$cur_field->edit->add = "1";
$cur_field->edit->edit = "1";
$cur_field->edit->delete = "1";

$cur_field->params->label = "Позиция";
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
