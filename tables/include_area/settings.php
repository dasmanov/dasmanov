<?php if(!defined('ACCESS_CODE') && intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

	$module_name = explode("/",str_replace("\\","/",dirname(__FILE__)));
	$module_name = $module_name[sizeof($module_name)-1];

	$cur_module = &$this->module[$module_name];
	$cur_fields = &$cur_module->fields;
	$cur_titles = &$cur_fields->titles;

	$cur_fields->table->title = $module_name;
	$cur_fields->table->name = $module_name;
	$cur_fields->table->multiline = 1;

	$cur_module->title = 'Вкл. области';

	$cur_titles->view = 'Области';
	$cur_titles->add = 'Добавить область';
	$cur_titles->edit = 'Редактирование область';
	$cur_titles->delete = 'Удалить область';
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "id";
	$cur_field->db->type = "int";
	$cur_field->db->length = "11";
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

	$cur_field->edit->view = "1";
	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";
	$cur_field->edit->delete = "1";

	$cur_field->params->label = "Название";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "position";
	$cur_field->db->type = "varchar";
	$cur_field->db->length = "255";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "select::areas::code->name";

	$cur_field->show->align = "center";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Позиция";
	$cur_field->params->filtering = 1;
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "nl2br";
	$cur_field->db->type = "int";
	$cur_field->db->length = "1";
	$cur_field->db->default = "0";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "checkbox";
	$cur_field->form->value = "1";
	$cur_field->form->checked = "false";

	$cur_field->show->align = "center";

	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Заменять 'переносы на новую строку' на '< br  / >'";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "text";
	$cur_field->db->type = "text";
	$cur_field->db->default = "NULL";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "textarea";
	$cur_field->form->rows = "20";
	$cur_field->form->cols = "100";

	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "text";
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "html";
	$cur_field->db->type = "text";
	$cur_field->db->default = "NULL";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "textarea";
	$cur_field->form->class = "editor";
	$cur_field->form->rows = "10";
	$cur_field->form->cols = "100";

	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "html";
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
	$cur_field->params->filtering = 1;
	////////////////////////////////////////////////////////////////////////////////
	$cur_fields->NewField(); $cur_field = &$cur_fields->field[$cur_fields->current];

	$cur_field->db->field = "order";
	$cur_field->db->type = "int";
	$cur_field->db->length = "3";

	$cur_field->form->name = $cur_field->db->field;
	$cur_field->form->type = "text";
	$cur_field->form->size = "3";
	$cur_field->form->value = 500;

	$cur_field->show->align = "right";

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
	$cur_field->form->type = "select::array::(0=>'Не показывать',1=>'Показывать',2=>'Показывать если не авторизован',3=>'Показывать если авторизован',4=>'Показывать администраторам',5=>'Показывать не администраторам')";

	$cur_field->show->align = "center";

	$cur_field->show->view = "1";
	$cur_field->show->add = "1";
	$cur_field->show->edit = "1";
	$cur_field->show->delete = "1";

	$cur_field->edit->add = "1";
	$cur_field->edit->edit = "1";

	$cur_field->params->label = "Показывать";
	$cur_field->params->filtering = 1;
	////////////////////////////////////////////////////////////////////////////////
	
	unset($cur_field);unset($cur_fields);
?>
