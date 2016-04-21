<style>

#jstree_demo_div{

		max-height:400px;
		overflow: auto;
	
}
.jstree-default .jstree-clicked {
		background: #428bca;
		border-radius: 2px;
		box-shadow: inset 0 0 1px #999;
		color: white;
}


</style>
<?php

	function hasParent($irn)
	{
		$parent_IRN=0;
		$hasParent=false;
		$sessionx = new IMuSession();
		$sessionx->connect();
		$catalogue = new IMuModule('ecatalogue', $sessionx);
		$ParentSearch = new IMuTerms();
		$ParentSearch->add('irn', $irn);
		$catalogue->findTerms($ParentSearch);
		$columns = array();
		$columns[] ='objectNumber=ColObjectNumber';
		$columns[] = 'children=<ecatalogue:ColParentRecordRef>.(irn,ColObjectNumber)';
		$columns[] = 'parent=ColParentRecordRef.(irn,ColObjectNumber)';				
		$result = $catalogue->fetch('start',0,-1,$columns);	
		foreach($result as $rows)
		{
			if (is_array($rows))
			{
				foreach($rows as $record)
				{
					$parent_IRN=$record['parent']['irn'];
				}
			}
		}
		return $parent_IRN;
	}
	
	function child_html($record)
	{
		$children = array();
		foreach($record['children'] as $childx)
		{					
			$children[]['html'] = "<li nodeTooOpen=\"".$childx['irn']."\"><a href=\"details.php?irn=".$childx['irn']."\" >".$childx['ColObjectNumber']."</a></li>";	
			$children[]['irn'] = $childx['irn'];
		}
		return $children;
	}
	
		function aasort (&$array, $key) 
	{
		$sorter=array();
		$ret=array();
		reset($array);
		foreach ($array as $ii => $va) 
		{
			$sorter[$ii]=$va[$key];
		}
		asort($sorter);
		foreach ($sorter as $ii => $va)
		{
			$ret[$ii]=$array[$ii];
		}
		$array=$ret;
	}
	
	
	function archivesSort($archive_number_array,$key)
	{
		$newChildArray=array();
		$subnumber_count = 0;
		foreach($archive_number_array as $child)
		{			
			 $split=split("/", $child[$key]);
			 $array_rebuilt=array(); 
			 $array_rebuilt['irn']= $child['irn'];
			 $array_rebuilt[$key]= $child[$key];
			 $array_rebuilt['children']= $child['children'];
			 $array_rebuilt[0]= $split[0];
			 $array_rebuilt[1]= $split[1];
			 $array_rebuilt[2]= $split[2];
			 $array_rebuilt[3]= $split[3];
			 $array_rebuilt[4]= $split[4];
			 $array_rebuilt[5]= $split[5];					 			
			 $newChildArray[]=$array_rebuilt;
			 $subnumber_count =count($split)-1;					 
		}
		aasort($newChildArray, $subnumber_count);						
		return $newChildArray;
	}
	
	function returnChilren($irn)
		{
			$catalogue = new IMuModule('ecatalogue', $session);
			$ParentSearch = new IMuTerms();
			$ParentSearch->add('irn', $irn);
			$catalogue->findTerms($ParentSearch);
			$columns = array();
			$columns[] ='objectNumber=ColObjectNumber';
			$columns[] ='id=irn';
			$columns[] = 'children=<ecatalogue:ColParentRecordRef>.(irn,ColObjectNumber)';
			$result = $catalogue->fetch('start',0,-1,$columns);
			foreach($result as $rows)
			{
					if (is_array($rows))
					{
						$newResult=array();							
						foreach($rows as $record)
						{
							$newRows=array();
							$newRows['children']=archivesSort($record['children'],'ColObjectNumber');	
							$newRows['irn']=	$record['irn'];	
							$newRows['objectNumber']=	$record['objectNumber'];	
							$newResult[]=$newRows;
							$newRows="";								
						}
						$newResult=archivesSort($newResult,'objectNumber');	
						$result->rows=$newResult;
					}				
			}				
			return $result;
	}

	require_once 'lib/IMu.php';
	require_once IMu::$lib . '/Session.php';
	require_once IMu::$lib . '/Terms.php';
	require_once IMu::$lib . '/Module.php';
		
	IMuSession::setDefaultHost($config['server-host']);
	IMuSession::setDefaultPort($config['server-port']);
	function getIfSet(&$value, $default = null)
{
    return isset($value) ? $value : $default;
}

	$irn = getIfSet($_REQUEST['irn']);	
	$node_to_open = $irn;
	
	
	if($irn)
	{
	

	?>
	<div class="page-header">
	    		<label>Browse the collection</label>
	</div>
	<?php
	
	
	if(hasParent($irn)>0){$irn =hasParent($irn);}
	if(hasParent($irn)>0){$irn =hasParent($irn);}
	if(hasParent($irn)>0){$irn =hasParent($irn);}
	if(hasParent($irn)>0){$irn =hasParent($irn);}
	if(hasParent($irn)>0){$irn =hasParent($irn);}
	$result=returnChilren($irn);
	foreach($result as $rows)
	{
		if (is_array($rows))
		{
			foreach($rows as $record)
			{						
				$children = child_html($record);
				$tree_html="<div style='display:none' id='jstree_demo_div'>";	
				$tree_html.="<ul>";
				$tree_html.=(parent_and_children_as_tree_1($record['objectNumber'],$record['irn'],$children));
				$tree_html.="</ul>";
				$tree_html.="</div>";		
				echo($tree_html);
			}
		}
	}
	
	}
	
	function parent_and_children_as_tree_1($parent_object_number,$record_irn,$children)
	{
		$tree_htmlx="";
		$tree_htmlx.="<li nodeTooOpen=\"".$record_irn."\"><a href=\"details.php?irn=".$record_irn."\" >".$parent_object_number;
		$tree_htmlx.="</a>";
		$tree_htmlx.="<ul>";
		foreach($children as $child)
		{
			if($child['irn']>0)
			{
				$result2=returnChilren($child['irn']);
				foreach($result2 as $rowsx)
				{
					if (is_array($rowsx))
					{
						foreach($rowsx as $recordx)
						{
							$childrenx = child_html($recordx);
							$tree_htmlx.=(parent_and_children_as_tree_2($recordx['objectNumber'],$recordx['irn'], $childrenx));
							
						}
					}
				}
			}
		}
		
		$tree_htmlx.="</ul>";
		$tree_htmlx.="</li>";
		return $tree_htmlx;
	}
	
		function parent_and_children_as_tree_2($parent_object_number,$record_irnx, $children){
		
			$tree_htmlx="";
			$tree_htmlx.="<li nodeTooOpen=\"".$record_irnx."\" ><a href=\"details.php?irn=".$record_irnx."\" >".$parent_object_number;
			$tree_htmlx.="</a>";
			$tree_htmlx.="<ul>";
			
			foreach($children as $child)
			{
				if($child['irn']>0)
				{
					$result2=returnChilren($child['irn']);	
					foreach($result2 as $rowsx)
					{
						if (is_array($rowsx))
						{				
							foreach($rowsx as $recordx)
							{				
								$childrenx = child_html($recordx);						
								$tree_htmlx.=(parent_and_children_as_tree_3($recordx['objectNumber'],$recordx['irn'], $childrenx));				
							}
						}
					}		
				}
			}		
			$tree_htmlx.="</ul>";
			$tree_htmlx.="</li>";
			return $tree_htmlx;
	}
	
			function parent_and_children_as_tree_3($parent_object_number,$record_irnx, $children)
			{
		
				$tree_htmlx="";
				$tree_htmlx.="<li nodeTooOpen=\"".$record_irnx."\" ><a href=\"details.php?irn=".$record_irnx."\" >".$parent_object_number;
				$tree_htmlx.="</a>";
				$tree_htmlx.="<ul>";
				
				foreach($children as $child)
				{
					$tree_htmlx.= $child['html'];	
				}
				
				$tree_htmlx.="</ul>";
				$tree_htmlx.="</li>";
				return $tree_htmlx;
	
			}
	


?>
