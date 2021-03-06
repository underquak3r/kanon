<?php
class scaffoldModelCollectionController extends controller{
	/**
	 * @var modelCollection
	 */
	protected $_collection = null;
	protected $_itemsByPage = 50;
	public function onConstruct(){
		$this->_collection = $this->_options['collection'];
		$this->css('
		table.scaffold-list{
			border: solid 1px #666;
			border-collapse: collapse;
		}
		.scaffold-list th{
			background: #EEEEEE;
			border: solid 1px #CCCCCC;
			padding: 3px;
			font-size: 11px;
		}
		.scaffold-list td{
			border: solid 1px #CCCCCC;
			padding: 3px;
			font-size: 11px;
		}
		.scaffold-list td.odd{
			background: #F1F1F1;
		}
		.scaffold{
			font-size: 11px;
			font-family: \'lucida grande\', tahoma, verdana, arial, sans-serif;
		}
		.scaffold .pages{
			margin: 7px 0;
			
		}
		.scaffold .pages a, .scaffold .pages b{
			display: -moz-inline-box; display: inline-block; *zoom: 1; *display: inline;
			padding: 3px;
			margin-right: 2px;
			margin-bottom: 4px;
		}
		.scaffold .pages a{
			background: #eee;
			border: solid 1px #ccc;
			color: #000;
		}
		.scaffold .pages b{
			background: #316AC5;
			border: solid 1px #ccc;
			color: #fff;
		}
		');
	}
	public function header(){
		echo '<div class="scaffold">';
	}
	public function footer(){
		echo '</div>';
	}
	public function index($page = 1){
		$this->_collection->setItemsByPage($this->_itemsByPage);
		$itemsCount = count($this->_collection->select());
		if ($itemsCount){
			$pagesCount = ceil($itemsCount/$this->_itemsByPage);
			$this->viewPages($pagesCount, $page);
			echo '<table class="scaffold-list">';
			$first = true;
			$odd = true;
			foreach ($this->_collection->select()->page($page) as $item){
				$odd = !$odd;
				$properties = $item->getPropertyNames();
				if ($first){
					echo '<tr>';
					foreach ($properties as $propertyName){
						echo '<th>';
						echo $propertyName;
						echo '</th>';
					}
					echo '</tr>';
					$first = false;
				}
				echo '<tr'.($odd?' class="odd"':'').'>';
				foreach ($properties as $propertyName){
					echo '<td>';
					$property = $item->{$propertyName};
					echo $property->html();
					echo '</td>';
				}
				echo '</tr>';
			}
			echo '<tr>';
			echo '<th colspan="'.count($properties).'">';
			echo 'Найдено рядов: '.$itemsCount;
			echo '</th>';
			echo '</tr>';
			echo '</table>';
			$this->viewPages($pagesCount, $page);
		}
	}
}