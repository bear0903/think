<?php
 /**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) ARES International Inc.
 *	 
 *	Description:
 *     Get Tree Structure Script
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresTree.class.php $
 *  $Id: AresTree.class.php 698 2008-11-19 05:51:54Z dennis $
 *  $Rev: 698 $ 
 *  $Date: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 \****************************************************************************/
	class AresTree{
        var $treeData;
        var $nodeIDKey   = "NODEID";            // default index key of node id
        var $nodeTextKey = "NODETEXT";          // default index key of node text
        var $parentNodeIDKey = "P_NODEID";      // default index key of parent node id
        var $nodeTypeKey = "NODETYPE";          // node type, module, application, dept,emp etc.  

		var $stackNode;                         // Stack Node array.
	    var $stackIndex = -1;                   // Stack Index.
        
        /**
        *   Construct of AresTree
        */
        function AresTree($treedata)
        {
            $this->treeData = $treedata;
        }

        function set_nodeid_key($nodeidkey)
        {
            $this->nodeIDKey = $nodeidkey;
        }

        function set_nodetext_key($nodetextkey)
        {
            $this->nodeTextKey = $nodetextkey;
        }

        function set_parent_nodeid_key($parentnodeidkey)
        {
            $this->parentNodeIDKey = $parentnodeidkey;
        }
        function set_nodetype_key($nodetypekey)
        {
            $this->nodeTypeKey = $nodetypekey;
        }
        /**
        *   ehr System menu JS code generator
        *   for TaskMenu.js version
        *   @param $menuitem array, the menu item array
        *   @return string, Javascript code for generator menu
        *   @author Dennis
        *   @last update: 2006-01-13 11:16:46  by dennis
        */
        function GetMenuJSCode($menuItemIcon)
        {
            $menu_js = "";
            $menuitem = $this->treeData;
            
            $nodeid = $this->nodeIDKey;
            $parent_node_id = $this->parentNodeIDKey;
            $nodetext = $this->nodeTextKey;
            $nodetype = $this->nodeTypeKey;
            $_cnt = count($menuitem);
            if (is_array($menuitem))
            {
                for ($i=0; $i<$_cnt; $i++)
                {
                    if (strtoupper($menuitem[$i][$nodetype]) == "MENU")
                    {
                        $menu_js .= "var ".$menuitem[$i][$nodeid]." = new TaskMenu('".$menuitem[$i][$nodetext]."',false);\n";
                        $menu_js .= "var menu_".$menuitem[$i][$nodeid]." = new Array();\n";

                        $_k = 0;
                        // get child menu item
                        for($j=0; $j<$_cnt; $j++)
                        {
                            if ($menuitem[$j][$parent_node_id] == $menuitem[$i][$nodeid])
                            {
                                $menu_js .= "\t menu_".$menuitem[$i][$nodeid]."[$_k] = new TaskMenuItem('".$menuitem[$j][$nodetext]."' ,'$menuItemIcon' ,'OpenApp(\"".$menuitem[$j][$nodeid]."\")');\n";
                                $_k++;
                            }
                        }
                        if ($_k >0) // init the menu if the menu had child menu item
                        {
                            $menu_js .= "\t ".$menuitem[$i][$nodeid].".add(menu_".$menuitem[$i][$nodeid].");\n";
                            $menu_js .= "\t ".$menuitem[$i][$nodeid].".init();\n";
                        }
                    }
                }
            }else{
                $menu_js = '/t alert("No Menu Permission.\n Please contact your administrator.");\n';
            }
            return $menu_js;
        }// end function GetMenuJSCode()

        /**
        *   Help function of get_child_node
        */
		
        function has_child($treedata,$nodeid)
        {
            $cnt = count($treedata);
            for ($i=0; $i<$cnt; $i++)
            {
                if ($nodeid == $treedata[$i]["PARENT_NODE_ID"])
                {
                    return true;
                }
            }
            return false;
        }

        
		/**
        *   Get organization tree menu
		*	@param $parent_node_id string default "ROOT", parent node id
		*	@param $tree_js string tree javascript code, out type pamrameter
		*	@param $index_url string, the url when click tree node
		*	@return no return value, only out parameter store the javascript code.
		*	@author: Dennis
		*	@last update : 2006-02-17 14:18:37 by Dennis
        */
		function GetTreeJSCode($parent_node_id,&$tree_js,$index_url)
        {			
			if(!is_array($this->treeData)){
                return "";
            }
			// Click employee node, 预设为显示员工基本资料.
			// 如果选取了其它程式,则显示其它程式,在 redirect.php 中,切换到相对应的程式\
			$_empDefaultScript = "MGRA100";

			// Click department node, 预设显示当前部门有权限查看员工之清单
			// 含下阶人员(暂不加此功能)
			$_deptDefaultScript = "MGRA110";

			// add by dennis 2006-02-17 14:21:12 
			// 为了在 click tree node(employee node) 时显示哪一支程式,做判断用(redirect.php 中)
			$_referName = "orgtree";
		
			// 记录 有子 node 的 menu item 会于 menu items 中的位置
			$_cnt = count($this->treeData);
			
			// loop tree data
			for ($i=0; $i<$_cnt; $i++)
			{			
				// get node id | node text | icon | url
				$_node_id   = $this->treeData[$i]["NODE_SEQ_NO"];
				$_node_text = $this->treeData[$i]["NODE_TEXT"];
				$_tooltip = $this->treeData[$i]["NODE_TEXT"]."[".$this->treeData[$i]["NODE_ID"]."]";
				$_pnode_id  = $this->treeData[$i]["PARENT_NODE_ID"];
				$_emp_sex   = empty($this->treeData[$i]["EMP_SEX"]) ? "m" : strtolower($this->treeData[$i]["EMP_SEX"]);
				//$_emp_type  = $this->treeData[$i]["EMP_TYPE"]; remark by dennis 2007-01-30 13:30:09  by Dennis.Lan 
				$_is_emp    = $this->treeData[$i]["IS_EMP"];
                
				while($this->stackIndex != -1 &&
                      $_pnode_id        != $this->stackNode[$this->stackIndex]){
                    $lastmenuid = @$this->stackNode[$this->stackIndex-1];
                    $currmenuid = $this->stackNode[$this->stackIndex];
                    $tree_js .= "\t menu$lastmenuid.makeLastSubmenu(menu$currmenuid);\n";
                    $this->stackIndex--;
				};
               
				if ($_is_emp == "1")
				{
					$_icon = "emp_".$_emp_sex.".gif"; // 分男女不同的 icon
					$_url = $index_url;
					$_url .= "?dept_seq_no=".$this->treeData[$i]["DEPT_SEQ_NO"];
					$_url .= "&dept_id=".$this->treeData[$i]["DEPT_ID"];
					$_url .= "&dept_name=".urlencode($this->treeData[$i]["DEPT_NAME"]);
					$_url .= "&emp_seq_no=".$this->treeData[$i]["NODE_SEQ_NO"];
					$_url .= "&emp_id=".$this->treeData[$i]["NODE_ID"];
					$_url .= "&emp_name=".urlencode($this->treeData[$i]["NODE_TEXT"]);
                    $_url .= "&sex=$_emp_sex";
					$_url .= "&scriptname=".$_empDefaultScript;
					$_url .= "&refername=".$_referName;
				}else{
					$_icon = "dept.gif";
					$_url = $index_url;
					$_url .= "?dept_seq_no=".$this->treeData[$i]["DEPT_SEQ_NO"];
					$_url .= "&dept_id=".$this->treeData[$i]["DEPT_ID"];
					$_url .= "&dept_name=".urlencode($this->treeData[$i]["DEPT_NAME"]);
					$_url .= "&scriptname=".$_deptDefaultScript;
				}
               
				//IF ROOT node declare menu object
                //print $_pnode_id.'<br/>';
				if (strtoupper($_pnode_id) == "ROOT")
				{
					//declare menu item object, must be name "menu"
					$tree_js .= "\tvar menu = null;\n";
					$tree_js .= "\t menu = new MTMenu();\n";

					//add root node, maybe more than 1 node
					$tree_js .= "\tmenu.MTMAddItem(new MTMenuItem('$_node_text','$_url','','$_tooltip','$_icon'));\n";

					$tree_js .= "\t var menu$_node_id = null;\n";
					$tree_js .= "\t menu$_node_id = new MTMenu();\n";
                    
					$this->stackIndex++;
					$this->stackNode[$this->stackIndex]=$_node_id;
				}else{
					// IF department node declare menu object
					if (intval($_is_emp) == 0 && is_array($this->stackNode))
					{						
						$menuid=$this->stackNode[$this->stackIndex];
						$tree_js .= "\t menu$menuid.MTMAddItem(new MTMenuItem('$_node_text','$_url','','$_tooltip','$_icon'));\n";	
						
						$tree_js .= "\t var menu$_node_id = null;\n";
						$tree_js .= "\t menu$_node_id = new MTMenu();\n";

						$this->stackIndex++;
						$this->stackNode[$this->stackIndex]=$_node_id;
					}
					// IF employee node declare menu item object
					if (intval($_is_emp) == 1 && is_array($this->stackNode))
					{
						$menuid=$this->stackNode[$this->stackIndex];
						$tree_js .= "\t menu$menuid.MTMAddItem(new MTMenuItem('$_node_text','$_url','','$_tooltip','$_icon'));\n";
					}
				}
			}            
			while($this->stackIndex != -1)
            {
				$lastmenuid = @$this->stackNode[$this->stackIndex-1];
				$currmenuid = $this->stackNode[$this->stackIndex];
				$tree_js .= "\t menu$lastmenuid.makeLastSubmenu(menu$currmenuid);\n";
				$this->stackIndex--;
			};           
		} // end function GetTreeJSCode()
	}
?>