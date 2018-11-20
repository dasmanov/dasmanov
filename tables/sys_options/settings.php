<?php if(!defined('ACCESS_CODE') && intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

	$module_name = explode("/",str_replace("\\","/",dirname(__FILE__)));
	$module_name = $module_name[sizeof($module_name)-1];

	$cur_module = &$this->module[$module_name];
	$cur_fields = &$cur_module->fields;
	$cur_titles = &$cur_fields->titles;

	$cur_fields->table->title = $module_name;
	$cur_fields->table->name = $module_name;
	$cur_fields->table->multiline = 1;

	$cur_module->title = 'Опции';

	$cur_titles->view = 'Опции';
	$cur_titles->add = 'Добавить опцию';
	$cur_titles->edit = 'Редактирование опции';
	$cur_titles->delete = 'Удалить опцию';
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "id";
	$cur_field->db->type = "int";
	$cur_field->db->length = 2;
	$cur_field->db->null = false;
	$cur_field->db->auto_increment = TRUE;

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "auto_increment";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->params->label = "ID";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "name";
	$cur_field->db->type = "varchar";
	$cur_field->db->length = "200";
	$cur_field->db->default = "NULL";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "text";
	$cur_field->form->size = "100";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	//$cur_field->edit->view = "1";
	$cur_field->edit->add = "1";
	//$cur_field->edit->edit = "1";
	$cur_field->edit->delete = "1";

	$cur_field->params->label = "Переменная";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "value";
	$cur_field->db->type = "varchar";
	$cur_field->db->length = "200";
	$cur_field->db->default = "NULL";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "text";
	$cur_field->form->size = "100";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->view = "1";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";
	$cur_field->edit->delete = "1";

	$cur_field->params->label = "Значение";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "domain_id";
	$cur_field->db->type = "int";
	$cur_field->db->length = 2;

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "select::domains::id->name";

	$cur_field->show->align = "center";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->view = "1";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";
	$cur_field->edit->delete = "1";

	$cur_field->params->label = "для домена";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "comment";
	$cur_field->db->type = "varchar";
	$cur_field->db->length = "200";
	$cur_field->db->default = "NULL";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "text";
	$cur_field->form->size = "100";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->view = "1";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";
	$cur_field->edit->delete = "1";

	$cur_field->params->label = "Комментарий";
	////////////////////////////////////////////////////////////////////////////////

	unset($cur_field);unset($cur_fields);
?>
