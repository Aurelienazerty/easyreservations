<?php
class Resource {
	public $id;
	public $title;
	public $content;
	public $interval = 86400;
	public $menu_order = 0;

	public function __construct($id = false, $informations  = array('all')){
		easyreservations_load_resources(true);
		global $the_rooms_array, $the_rooms_intervals_array;
		$all = array( 'taxes','req', 'child', 'count', 'countnames', 'groundprice', 'persons', 'permission', 'fitlers' );
		if($id && is_numeric($id)){
			if(isset($the_rooms_array[$id])){
				$this->id = $id;
				$this->title = $the_rooms_array[$id]->post_title;
				$this->menu_order = $the_rooms_array[$id]->menu_order;
				$this->interval = $the_rooms_intervals_array[$id];
				$all = array( 'taxes','req', 'child', 'count', 'countnames', 'groundprice', 'persons', 'permission', 'fitlers' );
				if(!is_array($informations)) $informations = array($informations);
				if(in_array('all', $informations) || in_array('content', $informations)) $this->getContent();
				if(in_array('all', $informations)) $informations = $all;
				if($informations && !empty($informations)){
					foreach($informations as $information){
						if(in_array($information, $informations)) $this->loadSetting($information);
					}
				}
			} else {
				throw new easyException( 'Resource isn\'t existing; ID: '.$this->id, 1 );
			}
		} else {
			$all[] = 'content';
			$all[] = 'title';
			$all[] = 'menu_order';
			$all[] = 'interval';
			foreach($informations as $key => $information){
				if(in_array($key, $all)) $this->$key = $information;
			}
		}
	}

	public function getContent(){
		global $wpdb;
		$rooms = $wpdb->get_results("SELECT post_content FROM ".$wpdb->prefix ."posts WHERE id='$this->id' ");
		return $this->content = $rooms[0]->post_content;
	}

	public function getSettingName($setting){
		$array =  array(
				'interval' => 'easy-resource-interval',
				'taxes' => 'easy-resource-taxes',
				'req' => 'easy-resource-req',
				'child' => 'reservations_child_price',
				'count' => 'roomcount',
				'countnames' => 'easy-resource-roomnames',
				'groundprice' => 'reservations_groundprice',
				'persons' => 'easy-resource-price',
				'permission' => 'easy-resource-permission',
				'fitlers' => 'easy_res_filter'
			);
		if(isset($array[$setting])) return $array[$setting];
		else return false;
	}

	public function loadSetting($name){
		$this->$name = get_post_meta($this->id, $this->getSettingName($name), TRUE);
	}

	public function editResource($information = array('all')){
		if(!$this->Validate()){
			global $wpdb;
			if(!is_array($information)) $information = array($information);
			$all = array( 'taxes','req', 'child', 'count', 'countnames', 'groundprice', 'persons', 'permission', 'fitlers', 'interval' );
			if(!is_array($information)) $information = array($information);
			if(in_array('content', $information) || in_array('menu_order', $information) || in_array('title', $information)){
				$sql = '';
				if(in_array('content', $information)) $sql.= "content='$this->content', ";
				if(in_array('menu_order', $information)) $sql.= "menu_order='$this->menu_order', ";
				if(in_array('title', $information)) $sql.= "post_title='$this->title', ";
				$sql = substr($sql, 0, -2);
				$return = $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."posts SET $sql WHERE id='%d' ", $this->id));
				if(!$return){
					throw new easyException( 'Resource couldn\'t be edited. Error: '.mysql_error(), mysql_errno() );
					return true;
				}
			}
			if(in_array('all', $information)) $information = $all;
			foreach($information as $info){
				if(in_array($info, $information) && isset($this->$info)) update_post_meta($this->id, $this->getSettingName($info), $this->$info);
			}
			return false;
		} else return true;
	}

	public function deleteResource(){
		$return = wp_delete_post( $this->id, true );
		if($return) return false;
		else {
			throw new easyException( 'Resource couldn\'t be deleted.' );
			return true;
		}
	}

	public function Validate(){
		foreach($this as $key => $option){
			if($key == 'interval'){
				if(!($option == 3600 || $option == 86400 || $option == 604800)){
					throw new easyException( 'Interval must be 3600, 86400 or 604800 - Interval: '.$this->interval );
					return true;
				}
				if(isset($this->groundprice) && (!is_numeric($this->groundprice) || $this->groundprice < 0)){
					throw new easyException( 'Base price must be float and >= 0 | Base price: '.$this->groundprice );
					return true;
				}
				if(isset($this->child) && (!is_numeric($this->child))){
					$this->child = 0;
				}
				if(isset($this->count) && (!is_numeric($this->count) || $this->count < 1)){
					throw new easyException( 'Count of resource must be integer and > 1 - Count: '.$this->count );
					return true;
				}
				if(empty($this->title) || strlen($this->title)  < 1){
					throw new easyException( 'Title must be string and > 0 - Title: '.$this->title );
					return true;
				}
				if(!is_numeric($this->menu_order)){
					throw new easyException( 'Menu order must be integer and > 1 - Order: '.$this->menu_order );
					return true;
				}
				return false;
			}
		}
	}

	public function addResource($informations = array('all')){
		if(!empty($this->title) && !$this->Validate()){
			$resource = array(
				'post_title' => $this->title,
				'post_content' => $this->content,
				'menu_order' => $this->menu_order,
				'post_status' => 'private',
				'post_type' => 'easy-rooms'
			);

			$this->id = wp_insert_post( $resource );
			if($this->id > 0){
				$all = array( 'taxes','req', 'child', 'count', 'countnames', 'groundprice', 'persons', 'permission', 'fitlers', 'interval' );
				if(!is_array($informations)) $informations = array($informations);
				if(in_array('all', $informations)) $informations = $all;
				foreach($informations as $information){
					if(in_array($information, $informations) && isset($this->$information)) add_post_meta($this->id, $this->getSettingName($information), $this->$information);
				}
			} else {
				throw new easyException( 'Resource couldn\'t be created' );
				return true;
			}
		} else {
			throw new easyException( 'Resource couldn\'t be created - empty title' );
			return true;
		}
	}
}
?>