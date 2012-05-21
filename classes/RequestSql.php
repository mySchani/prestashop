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
*  @version  Release: $Revision: 6844 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class RequestSqlCore extends ObjectModel
{
	public $name;
	public $sql;

	protected $fieldsRequired = array('name', 'sql');
	protected $fieldsSize = array('name' => 200 , 'sql' => 400);
	protected $fieldsValidate = array('name' => 'isString', 'sql' => 'isString');

	protected $table = 'request_sql';
	protected $identifier = 'id_request_sql';

	public $tested = array('required' => array ('SELECT', 'FROM'),
							'option' => array('WHERE', 'ORDER', 'LIMIT', 'HAVING', 'GROUP', 'UNION'),
							'operator' => array('AND', '&&', 'BETWEEN', 'AND', 'BINARY', '&', '~', '|', '^', 'CASE', 'WHEN', 'END', 'DIV', '/', '<=>', '=', '>=',
							'>', 'IS', 'NOT', 'NULL', '<<', '<=', '<', 'LIKE', '-', '%', '!=', '<>', 'REGEXP', '!', '||', 'OR', '+', '>>', 'RLIKE', 'SOUNDS', '*',
							'-', 'XOR', 'IN'),
							'function' => array('AVG', 'SUM', 'COUNT', 'MIN', 'MAX', 'STDDEV', 'STDDEV_SAMP', 'STDDEV_POP', 'VARIANCE', 'VAR_SAMP', 'VAR_POP',
							'GROUP_CONCAT', 'BIT_AND', 'BIT_OR', 'BIT_XOR'),
							'unauthorized' => array('DELETE', 'ALTER', 'INSERT', 'REPLACE', 'CREATE', 'TRUNCATE', 'OPTIMIZE', 'GRANT', 'REVOKE', 'SHOW', 'HANDLER',
							'LOAD', 'ROLLBACK', 'SAVEPOINT', 'UNLOCK', 'INSTALL', 'UNINSTALL', 'ANALZYE', 'BACKUP', 'CHECK', 'CHECKSUM', 'REPAIR', 'RESTORE', 'CACHE',
							'DESCRIBE', 'EXPLAIN', 'USE', 'HELP', 'SET', 'DUPLICATE', 'VALUES',  'INTO', 'RENAME', 'CALL', 'PROCEDURE',  'FUNCTION', 'DATABASE', 'SERVER',
							'LOGFILE', 'DEFINER', 'RETURNS', 'EVENT', 'TABLESPACE', 'VIEW', 'TRIGGER', 'DATA', 'DO', 'PASSWORD', 'USER', 'PLUGIN', 'FLUSH', 'KILL',
							'RESET', 'START', 'STOP', 'PURGE', 'EXECUTE', 'PREPARE', 'DEALLOCATE', 'LOCK', 'USING', 'DROP', 'FOR', 'UPDATE', 'BEGIN', 'BY', 'ALL', 'SHARE',
							'MODE', 'TO','KEY', 'DISTINCTROW', 'DISTINCT',  'HIGH_PRIORITY', 'LOW_PRIORITY', 'DELAYED', 'IGNORE', 'FORCE', 'STRAIGHT_JOIN',
							'SQL_SMALL_RESULT', 'SQL_BIG_RESULT', 'QUICK', 'SQL_BUFFER_RESULT', 'SQL_CACHE', 'SQL_NO_CACHE', 'SQL_CALC_FOUND_ROWS', 'WITH'));

	public $attributes = array('passwd' => '*******************',
								'secure_key' => '*******************');

	public $error_sql = array();

	public function getFields()
	{
		parent::validateFields();
		$fields['name'] = pSQL($this->name);
		$fields['sql'] = pSQL($this->sql);
		return $fields;
	}

	public static function getRequestSql()
	{
		if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM `'._DB_PREFIX_.'request_sql` ORDER BY `id_request_sql`'))
			return false;
		$request_sql = array();
		foreach ($result as $row)
			$request_sql[] = $row['sql'];
		return $request_sql;
	}

	public static function getRequestSqlById($id)
	{
		return Db::getInstance()->executeS(sprintf('SELECT `sql` FROM `'._DB_PREFIX_.'request_sql` WHERE `id_request_sql` = %d', $id));
	}

	public function parsingSql($sql)
	{
		return Tools::parserSQL($sql);
	}

	public function validateParser($tab, $in = false, $sql)
	{
		if (!$tab)
			return false;
		else if (isset($tab['UNION']))
		{
			$union = $tab['UNION'];
			foreach ($union as $tab)
			{
				if (!$this->validateSql($tab, $in, $sql))
					return false;
			}
			return true;
		}
		else
			return $this->validateSql($tab, $in, $sql);
	}

	public function validateSql($tab, $in = false, $sql)
	{
		if (!$this->testedRequired($tab))
			return false;
		else if (!$this->testedUnauthorized($tab))
			return false;
		else if (!$this->checkedFrom($tab['FROM']))
			return false;
		else if (!$this->checkedSelect($tab['SELECT'], $tab['FROM'], $in))
			return false;
		else if (isset($tab['WHERE']))
		{
			if (!$this->checkedWhere($tab['WHERE'], $tab['FROM'], $sql))
				return false;
		}
		else if (isset($tab['HAVING']))
		{
			if (!$this->checkedHaving($tab['HAVING'], $tab['FROM']))
				return false;
		}
		else if (isset($tab['ORDER']))
		{
			if (!$this->checkedOrder($tab['ORDER'], $tab['FROM']))
				return false;
		}
		else if (isset($tab['GROUP']))
		{
			if (!$this->checkedGroupBy($tab['GROUP'], $tab['FROM']))
				return false;
		}
		else if (isset($tab['LIMIT']))
		{
			if (!$this->checkedLimit($tab['LIMIT']))
				return false;
		}

		if (empty($this->_errors))
			if (!Db::getInstance()->executeS($sql))
				return false;
		return true;
	}

	public function showTables()
	{
		$results = Db::getInstance()->executeS('SHOW TABLES');
		foreach ($results as $result)
		{
			$key = array_keys($result);
			$tables[] = $result[$key[0]];
		}
		return $tables;
	}

	public function cutJoin($attrs, $from)
	{
		$attrs = explode('=', str_replace(' ', '', $attrs));
		foreach ($attrs as $attr)
		{
			if ($attribut = $this->cutAttribute($attr, $from))
				$tab[] = $attribut;
			else
				return false;
		}
		return $tab;
	}

	public function cutAttribute($attr, $from)
	{
		if (preg_match('#^((`(\()?([a-z_])+`(\))?)|((\()?([a-z_])+(\))?))\.((`(\()?([a-z_])+`(\))?)|((\()?([a-z_])+(\))?))$#i', $attr))
		{
			$tab = explode('.', str_replace(array('`', '(', ')'), '', $attr));
			if (!$table = $this->returnNameTable($tab[0], $from))
				return false;
			else
				return array ('table' => $table,
							'alias' => $tab[0],
							'attribut' => $tab[1],
							'string' => $attr);
		}
		else if (preg_match('#^((`(\()?([a-z_])+`(\))?)|((\()?([a-z_])+(\))?))$#i', $attr))
		{
			$attribut = str_replace(array('`', '(', ')'), '', $attr);
			if (!$table = $this->returnNameTable(false, $from))
				return false;
			else
				return array('table' => $table,
							'attribut' => $attribut,
							'string' => $attr);
		}
		else
			return false;
	}

	public function returnNameTable($alias = false, $tables)
	{
		if ($alias)
		{
			foreach ($tables as $table)
			{
				if ($table['alias'] == $alias)
					return array($table['table']);
			}
		}
		else if (!$alias && (count($tables) > 1))
		{
			$this->error_sql['returnNameTable'] = false;
			return false;
		}
		else
		{
			foreach ($tables as $table)
				$tab[] = $table['table'];
			return $tab;
		}
	}

	public function attributExistInTable($attr, $table)
	{
		if (is_array($table) && (count($table) == 1))
			$table = $table[0];
		$attributs = Db::getInstance()->executeS(sprintf('DESCRIBE `%s`', $table));
		foreach ($attributs as $attribut)
			if ($attribut['Field'] == trim($attr))
				return true;
		return false;
	}

	public function testedRequired($tab)
	{
		foreach ($this->tested['required'] as $key)
			if (!array_key_exists($key, $tab))
			{
				$this->error_sql['testedRequired'] = $key;
				return false;
			}
		return true;
	}

	public function testedUnauthorized($tab)
	{
		foreach ($this->tested['unauthorized'] as $key)
			if (array_key_exists($key, $tab))
			{
				$this->error_sql['testedUnauthorized'] = $key;
				return false;
			}
		return true;
	}

	public function checkedFrom($from)
	{
		$nb = count($from);
		for ($i = 0; $i < $nb; $i++)
		{
			$table = $from[$i];
			if (!in_array(str_replace('`', '', $table['table']), $this->showTables()))
			{
				$this->error_sql['checkedFrom']['table'] = $table['table'];
				return false;
			}
			if ($table['ref_type'] == 'ON' && (trim($table['join_type']) == 'LEFT' || trim($table['join_type']) == 'JOIN'))
			{
				if ($attrs = $this->cutJoin($table['ref_clause'], $from))
				{
					foreach ($attrs as $attr)
					{
						if (!$this->attributExistInTable($attr['attribut'], $attr['table']))
						{
							$this->error_sql['checkedFrom']['attribut'] = array($attr['attribut'], implode(', ', $attr['table']));
							return false;
						}
					}
				}
				else
				{
					if (isset($this->error_sql['returnNameTable']))
					{
						$this->error_sql['checkedFrom'] = $this->error_sql['returnNameTable'];
						return false;
					}
					else
					{
						$this->error_sql['checkedFrom'] = false;
						return false;
					}
				}
			}
		}
		return true;
	}

	public function checkedSelect($select, $from, $in = false)
	{
		$nb = count($select);
		for ($i = 0; $i < $nb; $i++)
		{
			$attribut = $select[$i];
			if ($attribut['base_expr'] != '*')
			{
				if ($attribut['expr_type'] == 'colref' || $attribut['expr_type'] == 'reserved')
				{
					if ($attr = $this->cutAttribute($attribut['base_expr'], $from))
					{
						if (!$this->attributExistInTable($attr['attribut'], $attr['table']))
						{
							$this->error_sql['checkedSelect']['attribut'] = array($attr['attribut'], implode(', ', $attr['table']));
							return false;
						}
					}
					else
					{
						if (isset($this->error_sql['returnNameTable']))
						{
							$this->error_sql['checkedSelect'] = $this->error_sql['returnNameTable'];
							return false;
						}
						else
						{
							$this->error_sql['checkedSelect'] = false;
							return false;
						}
					}
				}
			}
			else
			{
				if ($in)
				{
					$this->error_sql['checkedSelect']['*'] = false;
					return false;
				}
			}
		}
		return true;
	}

	public function checkedWhere($where, $from, $sql)
	{
		$nb = count($where);
		for ($i = 0; $i < $nb; $i++)
		{
			$attribut = $where[$i];
			if ($attribut['expr_type'] == 'colref' || $attribut['expr_type'] == 'reserved')
			{
				if ($attr = $this->cutAttribute($attribut['base_expr'], $from))
				{
					if (!$this->attributExistInTable($attr['attribut'], $attr['table']))
					{
						$this->error_sql['checkedWhere']['attribut'] = array($attr['attribut'], implode(', ', $attr['table']));
						return false;
					}
				}
				else
				{
					if (isset($this->error_sql['returnNameTable']))
					{
						$this->error_sql['checkedWhere'] = $this->error_sql['returnNameTable'];
						return false;
					}
					else
					{
						$this->error_sql['checkedWhere'] = false;
						return false;
					}
				}
			}
			else if ($attribut['expr_type'] == 'operator')
			{
				if (!in_array(strtoupper($attribut['base_expr']), $this->tested['operator']))
				{
					$this->error_sql['checkedWhere']['operator'] = array($attribut['base_expr']);
					return false;
				}
			}
			else if ($attribut['expr_type'] == 'subquery')
			{
				$tab = $attribut['sub_tree'];
				return $this->validateParser($tab, true, $sql);
			}
		}
		return true;
	}

	public function checkedHaving($having, $from)
	{
		$nb = count($having);
		for ($i = 0; $i < $nb; $i++)
		{
			$attribut = $having[$i];
			if ($attribut['expr_type'] == 'colref')
			{
				if ($attr = $this->cutAttribute($attribut['base_expr'], $from))
				{
					if (!$this->attributExistInTable($attr['attribut'], $attr['table']))
					{
						$this->error_sql['checkedHaving']['attribut'] = array($attr['attribut'], implode(', ', $attr['table']));
						return false;
					}
				}
				else
				{
					if (isset($this->error_sql['returnNameTable']))
					{
						$this->error_sql['checkedHaving'] = $this->error_sql['returnNameTable'];
						return false;
					}
					else
					{
						$this->error_sql['checkedHaving'] = false;
						return false;
					}
				}
			}

			if ($attribut['expr_type'] == 'operator')
			{
				if (!in_array(strtoupper($attribut['base_expr']), $this->tested['operator']))
				{
					$this->error_sql['checkedHaving']['operator'] = array($attribut['base_expr']);
					return false;
				}
			}
		}
		return true;
	}

	public function checkedOrder($order, $from)
	{
		$order = $order[0];
		if ($order['type'] == 'expression')
		{
			if ($attr = $this->cutAttribute($order['base_expr'], $from))
			{
				if (!$this->attributExistInTable($attr['attribut'], $attr['table']))
				{
					$this->error_sql['checkedOrder']['attribut'] = array($attr['attribut'], implode(', ', $attr['table']));
					return false;
				}
			}
			else
			{
				if (isset($this->error_sql['returnNameTable']))
				{
					$this->error_sql['checkedOrder'] = $this->error_sql['returnNameTable'];
					return false;
				}
				else
				{
					$this->error_sql['checkedOrder'] = false;
					return false;
				}
			}
		}
		return true;
	}

	public function checkedGroupBy($group, $from)
	{
		$group = $group[0];
		if ($group['type'] == 'expression')
		{
			if ($attr = $this->cutAttribute($group['base_expr'], $from))
			{
				if (!$this->attributExistInTable($attr['attribut'], $attr['table']))
				{
					$this->error_sql['checkedGroupBy']['attribut'] = array($attr['attribut'], implode(', ', $attr['table']));
					return false;
				}
			}
			else
			{
				if (isset($this->error_sql['returnNameTable']))
				{
					$this->error_sql['checkedGroupBy'] = $this->error_sql['returnNameTable'];
					return false;
				}
				else
				{
					$this->error_sql['checkedGroupBy'] = false;
					return false;
				}
			}
		}
		return true;
	}

	public function checkedLimit($limit)
	{
		if (!preg_match('#^[0-9]+$#', trim($limit['start'])) || !preg_match('#^[0-9]+$#', trim($limit['end'])))
		{
			$this->error_sql['checkedLimit'] = false;
			return false;
		}
		return true;
	}
}
