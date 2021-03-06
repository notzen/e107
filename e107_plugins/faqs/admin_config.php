<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/faqs/admin_config.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("P")) 
{
	header("location:".e_BASE."index.php");
	exit;
}



class faq_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'faq_main_ui',
			'path' 			=> null,
			'ui' 			=> 'faq_admin_form_ui',
			'uipath' 		=> null
		),
		'cat'		=> array(
			'controller' 	=> 'faq_cat_ui',
			'path' 			=> null,
			'ui' 			=> 'faq_cat_form_ui',
			'uipath' 		=> null
		)					
	);	

	protected $adminMenu = array(
		'main/list'		=> array('caption'=> 'FAQs', 'perm' => '0'),
		'main/create'	=> array('caption'=> 'Create FAQ', 'perm' => '0'),
		'cat/list' 		=> array('caption'=> 'Categories', 'perm' => '0'),
		'cat/create' 	=> array('caption'=> "Create Category", 'perm' => '0'),
		'main/prefs' 	=> array('caption'=> LAN_PREFS, 'perm' => '0'),
	//	'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'FAQs';
}

class faq_cat_ui extends e_admin_ui
{ 	 	 
		protected $pluginTitle	= 'FAQs';
		protected $pluginName	= 'plugin';
		protected $table 		= "faqs_info";
		protected $pid			= "faq_info_id";
		protected $perPage 		= 5; //no limit
		protected $listOrder	= 'faq_info_order ASC';

	//	protected $listQry = "SELECT * FROM #faq_info"; // without any Order or Limit. 
	//	protected $editQry = "SELECT * FROM #faq_info WHERE faq_info_id = {ID}";
	 	 	
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'faq_info_icon' 			=> array('title'=> LAN_ICON,		'type' => 'icon',			'width' => '5%', 'thclass' => 'left' ),	 
			'faq_info_id'				=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE),     		
         	'faq_info_title' 			=> array('title'=> LAN_TITLE,		'type' => 'text',			'width' => 'auto', 'thclass' => 'left'), 
         	'faq_info_about' 			=> array('title'=> LAN_DESCRIPTION,	'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	'faq_info_parent' 			=> array('title'=> LAN_CATEGORY,	'type' => 'text',			'width' => '5%'),		
			'faq_info_class' 			=> array('title'=> LAN_VISIBILITY,	'type' => 'userclass',		'width' => 'auto', 'data' => 'int'),
			'faq_info_order' 			=> array('title'=> LAN_ORDER,		'type' => 'text',			'width' => '5%', 'thclass' => 'left' ),					
			'options' 					=> array('title'=> LAN_OPTIONS,		'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center','readParms'=>'sort=1')
		);	
	
	public function init()
	{
		if(e_AJAX_REQUEST) // ajax link sorting. 
		{
			$sql = e107::getDb();
			$c=0;
			if(isset($_POST['all']))
			{
				foreach($_POST['all'] as $id)
				{
					$sql->db_Update("faqs_info","faq_info_order = ".intval($c)." WHERE faq_info_id = ".intval($id));
					$c++;		
				}	
			}
			
		
			exit;
		}		
		
		
	}	
	/**
	 * Get FAQ Category data
	 *
	 * @param integer $id [optional] get category title, false - return whole array
	 * @param mixed $default [optional] default value if not found (default 'n/a')
	 * @return 
	 */
	function getFaqCategoryTree($id = false, $default = 'n/a')
	{
		// TODO get faq category tree
	}
		
}

class faq_cat_form_ui extends e_admin_form_ui
{
	public function faq_info_parent($curVal,$mode)
	{
		// TODO - catlist combo without current cat ID in write mode, parents only for batch/filter 
		// Get UI instance
		$controller = $this->getController();
		switch($mode)
		{
			case 'read':
				return e107::getParser()->toHTML($controller->getFaqCategoryTree($curVal), false, 'TITLE');
			break;
			
			case 'write':
				return $this->selectbox('faq_info_parent', $controller->getFaqCategoryTree(), $curVal);
			break;
			
			case 'filter':
			case 'batch':
				return $controller->getFaqCategoryTree();
			break;
		}
	}
}

class faq_main_ui extends e_admin_ui
{
		//TODO Move to Class above. 
		protected $pluginTitle		= 'FAQs';
		protected $pluginName		= 'faqs';
		protected $table			= "faqs";
		
		protected $tableJoin = array(
			'u.user' => array('leftField' => 'faq_author', 'rightField' => 'user_id', 'fields' => 'user_id,user_loginname,user_name')
		);
		// without any Order or Limit. 
		//protected $listQry			= "SELECT  * FROM #faqs"; 
		
		protected $editQry		= "SELECT * FROM #faqs WHERE faq_id = {ID}";
		
		protected $pid 			= "faq_id";
		protected $perPage 		= 10;
		protected $batchDelete	= true;
		protected $listOrder	= 'faq_order ASC';
		
		//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
    	protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'faq_id'				=> array('title'=> LAN_ID,			'type' => 'int',			'width' =>'5%', 'forced'=> TRUE),
         	'faq_question' 			=> array('title'=> "Question",		'type' => 'text',			'width' => 'auto', 'thclass' => 'left first'), // Display name
         	'faq_answer' 			=> array('title'=> "Answer",		'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	'faq_parent' 			=> array('title'=> "Category",		'type' => 'method',			'data'=> 'int','width' => '5%', 'filter'=>TRUE, 'batch'=>TRUE),		
			'faq_comment' 			=> array('title'=> "Comment",		'type' => 'userclass',		'data' => 'int',	'width' => 'auto'),	// User id
			'faq_datestamp' 		=> array('title'=> "datestamp",		'type' => 'datestamp',		'data'=> 'int','width' => 'auto', 'noedit' => TRUE),	// User date
            'faq_author' 			=> array('title'=> LAN_USER,		'type' => 'user',			'data'=> 'int', 'width' => 'auto', 'thclass' => 'center', 'class'=>'center', 'writeParms' => 'currentInit=1', 'filter' => true, 'batch' => true, 'nolist' => true	),	 	// Photo
       		'u.user_name' 			=> array('title'=> "User name",		'type' => 'user',			'width' => 'auto', 'noedit' => true, 'readParms'=>'idField=faq_author&link=1'),	// User name
       		'u.user_loginname' 		=> array('title'=> "User login",	'type' => 'user',			'width' => 'auto', 'noedit' => true, 'readParms'=>'idField=faq_author&link=1'),	// User login name
			'faq_order' 			=> array('title'=> "Order",			'type' => 'number',			'data'=> 'int','width' => '5%', 'thclass' => 'center','nolist' => true ),	
			'options' 				=> array('title'=> LAN_OPTIONS,		'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center','readParms'=>'sort=1')
		);
		 
		protected $fieldpref = array('checkboxes', 'faq_question', 'faq_answer', 'faq_parent', 'faq_datestamp', 'options');
		
		
		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array( 
			'add_faq'	   				=> array('title'=> 'Allow submitting of FAQs by:', 'type'=>'userclass'),
			'submit_question'	   		=> array('title'=> 'Allow submitting of Questions by:', 'type'=>'userclass'),		
			'classic_look'				=> array('title'=> 'Use Classic Layout', 'type'=>'boolean')
		);
	
	public function init()
	{
		if(e_AJAX_REQUEST) // ajax link sorting. 
		{
			$sql = e107::getDb();
			$c= ($_GET['from']) ? intval($_GET['from']) : 0;
			$updated = array();
			foreach($_POST['all'] as $row)
			{
				
				list($tmp,$id) = explode("-",$row);
				if($sql->db_Update("faqs","faq_order = ".intval($c)." WHERE faq_id = ".intval($id)))
				{
					$updated[] = $id;
				}
				$c++;		
			}
			
		//	echo "Updated ".implode(",",$updated);
			exit;
		}	
		
		
	}
	
	
	
		
	/**
	 * FAQ categories
	 * @var array
	 */
	protected $categories = null;

	/**
	 * Get FAQ Category data
	 *
	 * @param integer $id [optional] get category title, false - return whole array
	 * @param mixed $default [optional] default value if not found (default 'n/a')
	 * @return array
	 */
	function getFaqCategory($id = false, $default = 'n/a')
	{
		
		if(null === $this->categories) //auto-retrieve on first call
		{
			$sql = e107::getDb();
			if($sql->db_Select('faqs_info'))
			{
				while ($row = $sql->db_Fetch())
				{
					$this->categories[$row['faq_info_id']] = $row['faq_info_title'];
				}
			}
			else
			{
				$this->categories = array(); //prevent PHP warnings
			}
		}
		if(false === $id)
		{
			return $this->categories;
		}
		return vartrue($this->categories[$id], $default);
	}
		
}

class faq_admin_form_ui extends e_admin_form_ui
{
	/**
	 * faq_parent field method
	 * 
	 * @param integer $curVal
	 * @param string $mode
	 * @return mixed
	 */
	function faq_parent($curVal,$mode)
	{ 
		// Get UI instance
		$controller = $this->getController();
		
		switch($mode)
		{
			case 'read':
				return e107::getParser()->toHTML($controller->getFaqCategory($curVal), false, 'TITLE');
			break;
			
			case 'write':
				return $this->selectbox('faq_parent', $controller->getFaqCategory(), $curVal);
			break;
			
			case 'filter':
			case 'batch':
				return $controller->getFaqCategory();
			break;
		}
	}
}

new faq_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

?>