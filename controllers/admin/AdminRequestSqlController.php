<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 8897 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminRequestSqlControllerCore extends AdminController
{
	public function __construct()
	{
		$this->table = 'request_sql';
		$this->className = 'RequestSql';
	 	$this->lang = false;
		$this->export = true;
		$this->requiredDatabase = true;

		$this->context = Context::getContext();

		$this->fieldsDisplay = array(
			'id_request_sql' => array('title' => $this->l('ID'), 'width' => 25),
			'name' => array('title' => $this->l('Name'), 'width' => 300),
			'sql' => array('title' => $this->l('Request'), 'width' => 500)
		);

		parent::__construct();
	}

	public function initList()
	{
		$this->displayWarning($this->l('When saving the query, only the request type "SELECT" are allowed.'));
		$this->displayInformation('
			<strong>'.$this->l('How to create a new sql query?').'</strong>
			<br />
			<ul>
				<li>'.$this->l('Click "Add new".').'<br /></li>
				<li>'.$this->l('Fill in the fields and click "Save".').'</li>
				<li>'.$this->l('You can then view the query results by clicking on the tab: ').' <img src="../img/admin/details.gif"></li>
				<li>'.$this->l('You can then export the query results as a file. Csv file by clicking on the tab: ').' <img src="../img/admin/export.gif"></li>
			</ul>
		');

		$this->addRowAction('view');
		$this->addRowAction('edit');
		$this->addRowAction('delete');
	 	$this->bulk_actions = array(
	 		'delete' => array(
	 			'text' => $this->l('Delete selected'),
	 			'confirm' => $this->l('Delete selected items?')
	 		),
	 		'export' => array(
	 			'text' => $this->l('Export selected')
	 		)
	 	);

	 	return parent::initList();
	}

	public function initForm()
	{
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Request')
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Name:'),
					'name' => 'name',
					'size' => 103,
					'required' => true
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Request:'),
					'name' => 'sql',
					'cols' => 100,
					'rows' => 10,
					'required' => true
				)
			),
			'submit' => array(
				'title' => $this->l('   Save   '),
				'class' => 'button'
			)
		);

		return parent::initForm();
	}

	public function initView()
	{
		if (!($obj = $this->loadObject(true)))
			return;

		$view = array();

		if ($results = Db::getInstance()->executeS($obj->sql))
		{
			foreach (array_keys($results[0]) as $key)
				$tab_key[] = $key;

			$view['name'] = $obj->name;
			$view['key'] = $tab_key;
			$view['results'] = $results;

			$request_sql = new RequestSql();
			$view['attributes'] = $request_sql->attributes;
		}
		else
			$view['error'] = true;

		$this->tpl_view_vars = array(
			'view' => $view
		);
		return parent::initView();
	}

	public function _childValidation()
	{
		if (Tools::getValue('submitAdd'.$this->table) && $sql = Tools::getValue('sql'))
		{
			$request_sql = new RequestSql();
			$parser = $request_sql->parsingSql($sql);
			$validate = $request_sql->validateParser($parser, false, $sql);

			if (!$validate || !empty($request_sql->error_sql))
				$this->displayError($request_sql->error_sql);
		}
	}

	public function bulkexport($boxes)
	{
		if (!$boxes || count($boxes) > 1)
			$this->_errors[] = Tools::DisplayError('You must select a query to export the results.');

		$id = (int)$boxes[0];

		$file = 'request_sql_'.$id.'.csv';
		if ($csv = fopen(_PS_ADMIN_DIR_.'/export/'.$file, 'w'))
		{
			$sql = RequestSql::getRequestSqlById($id);

			if ($sql)
			{
				$results = Db::getInstance()->executeS($sql[0]['sql']);
				foreach (array_keys($results[0]) as $key)
				{
					$tab_key[] = $key;
					fputs($csv, $key.';');
				}
				foreach ($results as $result)
				{
					fputs($csv, "\n");
					foreach ($tab_key as $name)
						fputs($csv, '"'.Tools::safeOutput($result[$name]).'";');
				}
				if (file_exists(_PS_ADMIN_DIR_.'/export/'.$file))
				{
					$filesize = filesize(_PS_ADMIN_DIR_.'/export/'.$file);
					$upload_max_filesize = $this->returnBytes(ini_get('upload_max_filesize'));
					if ($filesize < $upload_max_filesize)
					{
						header('Content-type: text/csv');
						header('Cache-Control: no-store, no-cache');
						header('Content-Disposition: attachment; filename="$file"');
						header('Content-Length: '.$filesize);
						readfile(_PS_ADMIN_DIR_.'/export/'.$file);
						die();
					}
					else
						$this->_errors[] = Tools::DisplayError('The file is too large and can not be downloaded. Please use the clause "LIMIT" in this query.');
				}
			}
		}
	}

	public function returnBytes($val)
	{
	    $val = trim($val);
	    $last = strtolower($val[strlen($val) - 1]);
	    switch ($last)
	    {
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }
	    return $val;
	}

	public function displayError($e)
	{
		foreach (array_keys($e) as $key)
		{
			switch ($key)
			{
				case 'checkedFrom':
					if (isset($e[$key]['table']))
						$this->_errors[] = Tools::DisplayError($this->l('The Table ').' "'.$e[$key]['table'].'" '.$this->l(' doesn\'t exist.'));
					else if (isset($e[$key]['attribut']))
						$this->_errors[] = Tools::DisplayError($this->l('The attribute ').' "'.
						$e[$key]['attribut'][0].'" '.$this->l(' does not exist in the table: ').$e[$key]['attribut'][1].'.');
					else
						$this->_errors[] = Tools::DisplayError($this->l('Error'));
				break;

				case 'checkedSelect':
					if (isset($e[$key]['table']))
						$this->_errors[] = Tools::DisplayError($this->l('The Table ').' "'.$e[$key]['table'].'" '.$this->l(' doesn\'t exist.'));
					else if (isset($e[$key]['attribut']))
						$this->_errors[] = Tools::DisplayError($this->l('The attribute ').' "'.
						$e[$key]['attribut'][0].'" '.$this->l(' does not exist in the table: ').$e[$key]['attribut'][1].'.');
					else if (isset($e[$key]['*']))
						$this->_errors[] = Tools::DisplayError($this->l('The operand "*" can be used in a nested query.'));
					else
						$this->_errors[] = Tools::DisplayError($this->l('Error'));
				break;

				case 'checkedWhere':
					if (isset($e[$key]['operator']))
						$this->_errors[] = Tools::DisplayError($this->l('The operator ').' "'.$e[$key]['operator'].'" '.$this->l(' used is incorrect.'));
					else if (isset($e[$key]['attribut']))
						$this->_errors[] = Tools::DisplayError($this->l('The attribute ').' "'.
						$e[$key]['attribut'][0].'" '.$this->l(' does not exist in the table: ').$e[$key]['attribut'][1].'.');
					else
						$this->_errors[] = Tools::DisplayError($this->l('Error'));
				break;

				case 'checkedHaving':
					if (isset($e[$key]['operator']))
						$this->_errors[] = Tools::DisplayError($this->l('The operator ').' "'.$e[$key]['operator'].'" '.$this->l(' used is incorrect.'));
					else if (isset($e[$key]['attribut']))
						$this->_errors[] = Tools::DisplayError($this->l('The attribute ').' "'.
						$e[$key]['attribut'][0].'" '.$this->l(' does not exist in the table: ').$e[$key]['attribut'][1].'.');
					else
						$this->_errors[] = Tools::DisplayError($this->l('Error'));
				break;

				case 'checkedOrder':
					if (isset($e[$key]['attribut']))
						$this->_errors[] = Tools::DisplayError($this->l('The attribute ').' "'.
						$e[$key]['attribut'][0].'" '.$this->l(' does not exist in the table: ').$e[$key]['attribut'][1].'.');
					else
						$this->_errors[] = Tools::DisplayError($this->l('Error'));
				break;

				case 'checkedGroupBy':
					if (isset($e[$key]['attribut']))
						$this->_errors[] = Tools::DisplayError($this->l('The attribute ').' "'.
						$e[$key]['attribut'][0].'" '.$this->l(' does not exist in the table: ').$e[$key]['attribut'][1].'.');
					else
						$this->_errors[] = Tools::DisplayError($this->l('Error'));
				break;

				case 'checkedLimit':
						$this->_errors[] = Tools::DisplayError($this->l('The LIMIT clause must contain numeric arguments.'));
				break;

				case 'returnNameTable':
						if (isset($e[$key]['reference']))
							$this->_errors[] = Tools::DisplayError($this->l('The reference ').'"'.
							$e[$key]['reference'][0].'"'.$this->l(' doesn\'t exist in : ').$e[$key]['reference'][1]);
						else
							$this->_errors[] = Tools::DisplayError($this->l('When multiple tables are used, each attribute must be referenced to a table.'));
				break;

				case 'testedRequired':
						$this->_errors[] = Tools::DisplayError($e[$key].' '.$this->l(' doesn\'t exist.'));
					break;

				case 'testedUnauthorized':
						$this->_errors[] = Tools::DisplayError($e[$key].' '.$this->l(' is a unauthorized keyword.'));
				break;
			}
		}
	}
}


