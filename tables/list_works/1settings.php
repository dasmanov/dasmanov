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

	$cur_module->title = 'Список выполненных заданий';

	$cur_titles->view = 'Список выполненных заданий';
	$cur_titles->add = 'Добавить запись';
	$cur_titles->edit = 'Редактирование записи';
	$cur_titles->delete = 'Удалить запись';
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "id";
	$cur_field->db->type = "int";
	$cur_field->db->length = "11";
	$cur_field->db->auto_increment = TRUE;

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "auto_increment";

	//$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";
	$cur_field->show->align = "right";

	$cur_field->params->label = "ID";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "pid";
	$cur_field->db->type = "int";
	$cur_field->db->length = "11";
	$cur_field->db->null = TRUE;

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "select::".$module_cat_name."::id->name";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->view = "";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Проект";
	$cur_field->params->filtering = "1";
	$cur_field->params->export = 1;
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "date";
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

	$cur_field->params->label = "Дата";
	$cur_field->params->filtering = true;
	$cur_field->params->export = 1;
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "name";
	$cur_field->db->type = "varchar";
	$cur_field->db->length = "255";
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

	$cur_field->params->label = "Название тз";
	$cur_field->params->mandatory = "1";
	$cur_field->params->export = 1;
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "detail";
	$cur_field->db->type = "text";
	$cur_field->db->default = "NULL";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "textarea";
	$cur_field->form->rows = "10";
	$cur_field->form->cols = "100";
	$cur_field->form->class = "editor";

	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Комментарии";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "time";
	$cur_field->db->type = "time";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "time::HH:MM";
	$cur_field->form->size = "19";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";
	$cur_field->show->align = "right";

	$cur_field->edit->view = "1";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";
	$cur_field->edit->delete = "1";

	$cur_field->params->label = "Время";
	$cur_field->params->description = "(ЧЧ:ММ)";
	$cur_field->params->export = 1;
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "price";
	$cur_field->db->type = "int";
	$cur_field->db->length = "7";
	$cur_field->db->default = "NULL";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "text";
	$cur_field->form->size = $cur_field->db->length;
	$cur_field->form->maxlength = $cur_field->db->length;
	$cur_field->form->value = 699.32;

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";
	$cur_field->show->align = "right";

	$cur_field->edit->view = "1";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Цена";
	$cur_field->params->description = "руб.";
	$cur_field->params->export = 1;
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "cost";
	$cur_field->db->type = "int";
	$cur_field->db->length = "7";
	$cur_field->db->default = "NULL";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "calculate::[price]*[time]";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";
	$cur_field->show->align = "right";

	$cur_field->edit->view = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Стоимость";
	$cur_field->params->description = "руб.";
	$cur_field->params->export = 1;
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "paid";
	$cur_field->db->type = "int";
	$cur_field->db->length = "1";
	$cur_field->db->default = "1";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "checkbox";
	$cur_field->form->value = 0;
	$cur_field->form->checked = false;

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";
	$cur_field->show->align = "center";

	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Оплачено";
	$cur_field->params->filtering = 1;
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// Категории
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields = &$cur_module->categories;
	$cur_titles = &$cur_fields->titles;

	$cur_fields->table->title = $module_cat_name;
	$cur_fields->table->name = $module_cat_name;
	$cur_fields->table->multiline = 1;

	$cur_titles->view = 'Проекты';
	$cur_titles->add = 'Добавить проект';
	$cur_titles->edit = 'Редактирование проекта';
	$cur_titles->delete = 'Удалить проект';

	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "id";
	$cur_field->db->type = "int";
	$cur_field->db->length = "11";
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

	$cur_field->db->field = "pid";
	$cur_field->db->type = "int";
	$cur_field->db->length = "11";
	$cur_field->db->null = TRUE;

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "select::".$cur_fields->table->name."::id->name";

	//$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->view = "";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Родительский";
	//$cur_field->params->filtering = "1";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "company_id";
	$cur_field->db->type = "int";
	$cur_field->db->length = "11";
	$cur_field->db->null = TRUE;
	$cur_field->db->value = "";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "select::company::id->name";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->edit = "1";
	$cur_field->edit->add = "1";

	$cur_field->params->mandatory = "1";
	$cur_field->params->label = "Компания";
	$cur_field->params->filtering = "1";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "name";
	$cur_field->db->type = "varchar";
	$cur_field->db->length = "255";
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

	$cur_field->params->label = "Название проекта";
	$cur_field->params->mandatory = "1";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "order";
	$cur_field->db->type = "int";
	$cur_field->db->length = "3";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "text";
	$cur_field->form->size = "3";
	$cur_field->form->value = "500";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Порядок";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "show";
	$cur_field->db->type = "int";
	$cur_field->db->length = "1";
	$cur_field->db->default = "1";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "checkbox";
	$cur_field->form->value = 1;
	$cur_field->form->checked = true;

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";
	$cur_field->show->align = "center";

	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Активный";
	////////////////////////////////////////////////////////////////////////////////

	unset($cur_field);unset($cur_fields);
?>
