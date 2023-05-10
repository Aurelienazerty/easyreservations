<?php
	/**
	 * Reservations Class
	 * *
	 * @author Feryaz Beer (support@easyreservations.org)
	 * @version 1.0
	 *
	 * Usage:
	 * new Reservation(52); //by ID, load from database
	 * new Reservation(false, $array); //$array from foreach with all informations or custom one for new reservation
	 */

		class Reservation {
			public $id;
			public $arrival = '';
			public $departure = '';
			public $name = '';
			public $email = '';
			public $country = '';
			public $adults = 1;
			public $childs = 0;
			public $custom = array();
			public $prices = array();
			public $status = '';
			public $interval = 86400;
			public $resource = 0;
			public $resourcenumber = 0;
			public $reservated = 0;
			public $times = 1; // hours/days/weeks of reservation by resource interval
			public $user = 0; // reservation connected to wp_user; unused information
			public $price = 0;
			public $paid = 0;
			public $history = array(); // If called Calculate(true); this will contain a calculation history
			public $pricepaid = ''; // row price from database - contains price;paid if the price is fixed or paid
			public $admin = true;

			//----------------------------------------- Initialize -------------------------------------------------//

			/**
			 * Construction
			 * @param integer/bool $id ID of reservation
			 * @param array/bool $array Array with reservations informations
			 * @param bool $admin false for frontend
			 * @throws easyException #1, #2
			 */
			public function __construct($id = false, $array = false, $admin = true){
				$this->id = $id;
				$this->admin = $admin;

				if($this->id && ($this->id < 1 || !is_numeric($this->id))){
					throw new easyException( 'ID must be Integer and > 0; ID: '.$this->id, 1 );
				} elseif($id && $array){
					$this->ArrayToReservation($this->cleanSqlContent($array));
				} elseif($this->id){
					return $this->getInformations($this->id);
				} elseif($array){
					$this->fake = true;
					if(isset($array[0]) && $array[0] == 'dontclean'){
						unset($array[0]);
						$this->ArrayToReservation($array);
					} else
						$this->ArrayToReservation($this->cleanSqlContent($array));
				} else {
					throw new easyException( 'Need either reservations ID or array with information', 2 );
				}
			}

			/**
			 * Select from database
			 * @global object $wpdb Database class
			 * @param int $id ID of reservation
			 * @throws easyException #3
			 */
			private function getInformations($id){
				global $wpdb;
				$reservation = $wpdb->get_results($wpdb->prepare("SELECT id, name, approve, arrival, departure, room, roomnumber, number, childs, country, email, custom, customp, price, reservated, user FROM ".$wpdb->prefix ."reservations WHERE id='%d'", $id ));

				if(isset($reservation[0]) && $reservation[0] && $reservation[0] !== 0){
					$this->ArrayToReservation($this->cleanSqlContent((array) $reservation[0]));
					return true;
				} else {
					throw new easyException( 'Reservation isn\'t existing ID: '.$id, 3 );
				}
			}

			/**
			 * Change keys of database array to object informations names
			 */
			private function cleanSqlContent($array){
				if(isset($array['room'])) $array['resource'] = (int) $array['room'];
				if(isset($array['roomnumber'])) $array['resourcenumber'] = (int) $array['roomnumber'];
				if(isset($array['approve'])) $array['status'] = $array['approve'];
				if(isset($array['customp'])) $array['prices'] = $array['customp'];
				if(isset($array['number'])) $array['adults'] = (int) $array['number'];
				if(isset($array['childs'])) $array['childs'] = (int) $array['childs'];
				if(isset($array['price'])) $array['pricepaid'] = $array['price'];
				if(!is_numeric($array['arrival'])) $array['arrival'] = strtotime($array['arrival']);
				if(!is_numeric($array['departure'])) $array['departure'] = strtotime($array['departure']);
				if(isset($array['reservated']) && !is_numeric($array['reservated'])) $array['reservated'] = strtotime($array['reservated']);
				unset($array['room'], $array['roomnumber'], $array['customp'], $array['approve'], $array['price'], $array['number']);
				return $array;
			}

			/**
			 * Informations from fake/db array to class infromations; check if resource exists; get resource interval
			 * @throws easyException #4, #5
			 */
			public function ArrayToReservation($array){
				if(!empty($array)){
					foreach($array as $key => $information){
						if(isset($this->$key) || in_array($key, array('fake', 'fixed', 'coupon', 'new_custom'))) $this->$key = $information;
					}
				}

				if($this->resource){
					if($this->resource && is_numeric($this->resource) && $this->resource  > 0){
						easyreservations_load_resources(true);
						global $the_rooms_intervals_array, $the_rooms_array;
						if(isset($the_rooms_intervals_array[$this->resource])){
							$this->interval = $the_rooms_intervals_array[$this->resource];
							$this->resourcename = __($the_rooms_array[$this->resource]->post_title);
							$this->times = $this->getTimes( 0 );
						} else {
							$this->destroy();
							//throw new easyException( 'Resource isn\' existing ID: '.$this->resource, 4 );
						}
					} else {
						throw new easyException( 'Resource ID must be Integer and > 0; ID: '.$this->resource, 5 );
					}
				}
			}

			//----------------------------------------- Functions -------------------------------------------------//

			/**
			 *	Calculate reservation; access with $obj->price
			 * @param bool $history if true $obj->history will contain a calculation history
			 * @param bool $taxes if taxes should be calculated
			 * @return int $obj->price
			 */
			public function Calculate($history = false, $taxes = true){
				$this->price = 0;
				$this->history = array();
				$this->countpriceadd=0; // Count times (=days) a sum gets added to price
				$this->datearray = array();
				$filters = get_post_meta($this->resource, 'easy_res_filter', true);
				$this->groundprice = get_post_meta($this->resource, 'reservations_groundprice', true);
				$price_per_person = get_post_meta($this->resource, 'easy-resource-price', true);
				if(is_array($price_per_person)){
					if(isset($price_per_person[1]) && $price_per_person[1] == 1) $this->once = true;
					$price_per_person = $price_per_person[0];
				}
				if($taxes) $taxes = get_post_meta($this->resource, 'easy-resource-taxes', true);
				if($this->departure == 0) $this->departure = $this->arrival+$this->interval;

				if(!empty($this->pricepaid)){
					$price_xpl=explode(";", $this->pricepaid);
					if($price_xpl[0] >= 0 && $price_xpl[0] !== ''){
						$this->price = $price_xpl[0];
						$this->fixed = true;
						$this->countpriceadd++;
					}
					if(isset($price_xpl[1]) && $price_xpl[1] > 0){
						$this->paid = (float) str_replace(',','.',$price_xpl[1]);
					}
					if(!is_numeric($this->paid) || $this->paid <= 0){
						$this->paid = 0;
					}
				}
				if(!isset($this->fixed)){
					if(!empty($filters)){
						foreach($filters as $num => $filter){
							if($filter['type'] == 'price'){
								$this->Filter($filter);
								unset($filters[$num]);
							} else break;
						}
					}

					while($this->countpriceadd < $this->times){
						if(isset($this->once) && $this->countpriceadd > 0) break;
						$this->price+=$this->groundprice;
						$ifDateHasToBeAdded=0;
						if(isset($this->datearray)){
							$getrightday=true;
							while($getrightday){
								if(is_array($this->datearray) && !empty($this->datearray) && in_array($this->arrival+($ifDateHasToBeAdded*$this->interval), $this->datearray))
									$ifDateHasToBeAdded++;
								else
									$getrightday=false;
							}
							$this->datearray[]=$this->arrival+($ifDateHasToBeAdded*$this->interval);
						}
						if($history) $this->history[] = array('date'=>$this->arrival+($ifDateHasToBeAdded*$this->interval), 'priceday'=>$this->groundprice, 'type'=> 'groundprice');
						$this->countpriceadd++;
					}
					unset($this->datearray);

					$checkprice=$this->price;
					if($price_per_person == 1 && ($this->adults > 1 || $this->childs > 0)) {  // Calculate Price if  "Calculate per person"  was choosen
						if($this->adults > 1){
							$price_adults = $checkprice*$this->adults;
							$this->price += $price_adults-$checkprice;
							if($history) $this->history[] = array('date'=>$this->arrival+($this->countpriceadd*$this->interval), 'priceday'=>$price_adults-$checkprice,'type'=> 'persons', 'name' => $this->adults);
							$this->countpriceadd++;
						}
						if(!empty($this->childs) && $this->childs > 0){
							$childprice = get_post_meta($this->resource, 'reservations_child_price', true);
							$childsPrice = 0;
							if($childprice != -1){
								if(substr($childprice, -1) == "%"){
									$childsPrice=$checkprice/100*(str_replace("%", "", $childprice));
								} else {
									if(!isset($this->once)) $childsPrice = ($childprice * $this->times);
									else $childsPrice = ($childprice);
								}
								$childsPrice = ($checkprice-$childsPrice) *$this->childs;
								$this->price += $childsPrice;
								if($history) $this->history[] = array('date'=>$this->arrival+($this->countpriceadd*$this->interval), 'priceday'=>$childsPrice, 'type'=> 'childs', 'name' => $this->childs);
								$this->countpriceadd++;
							}
						}
					}

					$checkprice = $this->price;
					if(!empty($filters)){
						$full = array();
						foreach($filters as $filter){
							if($this->Filter($filter, $full)){
								$full[] = $filter['type'];
								$the_discount = $this->multiplyAmount($filter['modus'],$filter['price'],$checkprice);
								$this->price += $the_discount;
								if(!isset($filter['cond'])) $filter['cond'] = '';
								if($history) $this->history[] = array('date'=>$this->arrival+($this->countpriceadd*$this->interval), 'priceday'=>$the_discount, 'type'=>$filter['type'], 'name' => __($filter['name']), 'cond' => $filter['cond'] );
							}
						}
					}

					$customprices = 0;
					if(!empty($this->prices) || !empty($this->new_custom)){
						if(is_numeric($this->prices)){
							$customprices = $this->prices;
						} else $res_custom_array = $this->getCustoms($this->prices, 'cstm', true);
						if(!empty($this->new_custom)){
							if(!isset($res_custom_array)) $res_custom_array = array();
							foreach($this->new_custom as $id => $option){
								$array = array('type' => 'cstm', 'id' => $id, 'value' => $option);
								if(isset($res_custom_array['c'.$id])) $res_custom_array[] = $array;
								$res_custom_array['c'.$id] = array('type' => 'cstm', 'id' => $id, 'value' => $option);
							}
						}

						if(isset($res_custom_array) && !empty($res_custom_array)){
							$custom_fields = get_option('reservations_custom_fields');
							foreach($res_custom_array as $custom_price){
								if(isset($custom_price['type']) && $custom_price['type'] == "cstm"){
									if(isset($custom_price['id']) && isset($custom_fields['fields'][$custom_price['id']]) && isset($custom_fields['fields'][$custom_price['id']]['price'])){
										if(isset($custom_fields['fields'][$custom_price['id']]['options'][$custom_price['value']])){
											$amount = $this->calculateCustom($custom_price['id'], $custom_price['value'],$res_custom_array);
											$this->countpriceadd++;
											if($history) $this->history[] = array('date'=>$this->arrival+($this->countpriceadd*$this->interval), 'priceday'=>$amount, 'type' => 'customp', 'id' => $custom_price['id'], 'value' => $custom_price['value']);
											$customprices += $amount;
										}
									} else {
									 if(!isset($custom_price['amount'])) continue;
										$this->countpriceadd++;
										if(substr($custom_price['amount'], -1) == "%"){
											$percent=$this->price/100*str_replace("%", "", $custom_price['amount']);
											$customprices+=$percent;
											if($history) $this->history[] = array('date'=>$this->arrival+($this->countpriceadd*$this->interval), 'priceday'=>$percent, 'type' => 'customp_p', 'name' => __($custom_price['title']), 'value' => __($custom_price['value']), 'amount' => $custom_price['amount']);
										} else {
											$customprices+=$custom_price['amount'];
											if($history) $this->history[] = array('date'=>$this->arrival+($this->countpriceadd*$this->interval), 'priceday'=>$custom_price['amount'], 'type' => 'customp_n', 'name' => __($custom_price['title']),'value' => __($custom_price['value']), 'amount' => $custom_price['amount']);
										}
									}
								}
							}
						}
						$this->price+= $customprices; //Price plus Custom prices
					}
					apply_filters('easy-calc-pricefields', $this);
				}

				$checkprice = $this->price;
				$checkprice_both = 0;
				$this->taxamount = 0;
				if($taxes && !empty($taxes)){
					$this->taxrate = 0;
					foreach($taxes as $tax){
						if(!isset($tax[2]) || $tax[2] == 0){
							if($checkprice_both == 0) $checkprice_both = $this->price;
							$theprice = $checkprice_both;
							$plus = 20;
						} elseif($tax[2] == 1){
							$theprice = $checkprice - $customprices;
							$plus = 5;
						} elseif($tax[2] == 2 && !isset($this->fixed)){
							$theprice = $customprices;
							$plus = 15;
						}

						$tax_amount = $theprice / 100 * $tax[1];
						if(!isset($this->fixed)) $this->price += $tax_amount;
						else $tax_amount = $theprice * (1-1/(1+$tax[1]/100));

						$this->taxamount += $tax_amount;
						$this->taxrate += $tax[1];
						if($history) $this->history[] = array('date'=>$this->arrival+(($this->countpriceadd+$plus)*$this->interval), 'priceday'=>$tax_amount, 'type' => 'tax', 'name' => __($tax[0]), 'amount' => $tax[1], 'class' => (isset($tax[2])) ? $tax[2] : 0);
					}
				}

				if(isset($this->fixed)) if($history) $this->history[] = array('date'=>$this->arrival, 'priceday'=>$this->price - $this->taxamount, 'type'=> 'groundprice');

				if($history && !empty($this->history)){
					$dates = null;
					foreach ($this->history as $key => $row) $dates[$key]  = $row['date'];
					array_multisort($dates, SORT_ASC, $this->history);
				}

				$this->price = round($this->price,2);
				return $this->price;
			}

			function calculateCustom($id, $opt, $all){
				$custom_fields = get_option('reservations_custom_fields');
				$amount = 0;
				$option = false;
				if(isset($custom_fields['fields'][$id])){
					$field = $custom_fields['fields'][$id];
					$option = $field['options'][$opt];
					$amount = $option['price'];
					if(isset($option['clauses'])){
						$last_next = false;
						foreach($option['clauses'] as $clause){
							$true = false;
							if($last_next){
								if($last_next[0] == "and" && !$last_next[1]){
									if(is_numeric($clause['price'])) $last_next = false;
									else $last_next[0] == $clause['price'];
									continue;
								} if($last_next[0] == "or" && $last_next[1]){
									$true = true;
								}
							}
							if(!$true){
								if($clause['type'] == 'field'){
									if(isset($all['c'.$clause['operator']])){
										if($clause['cond'] == "any" || $clause['cond'] == $all['c'.$clause['operator']]['value']) $true = true;
									} else {
										foreach($all as $filter){
											if(isset($filter['id']) && $filter['id'] == $clause['operator']){
												if($clause['cond'] == "any" || $clause['cond'] == $filter['value']){
													$true = true;
												}
												break;
											}
										}
									}
								} else {
									if($clause['type'] == 'resource') $comparator = $this->resource;
									elseif($clause['type'] == 'units') $comparator = $this->times;
									elseif($clause['type'] == 'adult') $comparator = $this->adults;
									elseif($clause['type'] == 'child') $comparator = $this->childs;
									elseif($clause['type'] == 'arrival'){
										$comparator = date('Y-m-d', $this->arrival);
										$clause['cond'] = date('Y-m-d', strtotime($clause['cond']));
									} elseif($clause['type'] == 'departure'){
										$comparator = date('Y-m-d', $this->departure);
										$clause['cond'] = date('Y-m-d', strtotime($clause['cond']));
									}
									switch($clause['operator']){
										case "equal":
											if($clause['cond'] == $comparator) $true = true;
											break;
										case "notequal":
											if($clause['cond'] !== $comparator) $true = true;
											break;
										case "greater":
											if($clause['cond'] < $comparator) $true = true;
											break;
										case "greaterequal":
											if($clause['cond'] <= $comparator) $true = true;
											break;
										case "smaller":
											if($clause['cond'] > $comparator) $true = true;
											break;
										case "smallerequal":
											if($clause['cond'] >= $comparator) $true = true;
											break;
									}
								}
							}
							if($true){
								if(substr($amount, -1) == "%")
									$amount= $this->price/100*str_replace("%", "", $amount);
								else
									$amount = $amount;

								if(is_numeric($clause['price'])){
									$amount = $clause['price'];
									if($clause['mult'] && $clause['mult'] !== 'x')
										$amount = $this->multiplyAmount($clause['mult'], $amount);
								}
							}
							if(is_numeric($clause['price'])) $last_next = false;
							else $last_next = array($clause['price'], $true);
						}
					}
				}
				return $amount;
			}

			public function multiplyAmount($mode, $amount, $full = 0){
				if(!isset($mode) || !$mode || $mode == "price_res"){
					return $amount;
				} elseif($mode == "price_pers"){
					return $amount * ($this->adults + $this->childs);
				} elseif($mode == "price_adul"){
					return $amount * $this->adults;
				} elseif($mode == "price_child"){
					return $amount * $this->childs;
				} elseif($mode == "price_both"){
					return $amount * ($this->adults + $this->childs) * $this->times;
				} elseif($mode == "price_day_adult"){
					return $amount * $this->adults * $this->times;
				} elseif($mode == "price_day_child"){
					return $amount * $this->childs * $this->times;
				} elseif($mode == "price_day"){
					return $amount *  $this->times;
				} elseif($mode == '%' || $mode == 'price_perc'){
					return $full /100* (int) $amount;
				}
				return $amount;
			}

			/**
			* Check availability
			* @global obj $wpdb database connection
			* @param int $mode 0: returns number; 1: returns unavail dates string
			* @param bool $filter
			* @return int/string availability information
			*/
			public function checkAvailability($mode = 0, $filter = true, $display_interval = 86400, $ids = false){
				global $wpdb, $reservations_settings;
				if($mode == 1 || $mode == 2) $error = array();
				else $error = 0;
				$check_by_person = false;
				$interval = easyreservations_get_interval($this->interval, $this->resource, 1);
				$times = $this->getTimes(0, $interval);
				$res_number = '';
				$arrival = "arrival";
				$departure = "departure";
				$merge_res = 0;

				if($interval == 3600) $date_pattern = RESERVATIONS_DATE_FORMAT.' H:00';
				else $date_pattern = RESERVATIONS_DATE_FORMAT;

				if(isset($reservations_settings['mergeres']) && is_array($reservations_settings['mergeres']) && ($mode != 0 || !$this->admin)){
					if(isset($reservations_settings['mergeres']['blockbefore']) && $reservations_settings['mergeres']['blockbefore'] > 0){
						$blockbefore = (int) $reservations_settings['mergeres']['blockbefore'] * 60;
						$arrival = "arrival - INTERVAL ".($blockbefore)." SECOND";
					}
					if(isset($reservations_settings['mergeres']['blockafter']) && $reservations_settings['mergeres']['blockafter'] > 0){
						$blockafter = (int) $reservations_settings['mergeres']['blockafter'] * 60;
						$departure = "departure + INTERVAL ".($blockafter)." SECOND";
					}
					$merge_res = $reservations_settings['mergeres']['merge'];
				}
				if($merge_res > 0){
					$resource_number = $merge_res;
					$res_sql = '';
				} else {
					if($this->resourcenumber > 0) $res_number = " roomnumber='$this->resourcenumber' AND";
					$res_sql = "room='$this->resource' AND";
					$resource_number = get_post_meta($this->resource, 'roomcount', true);
					if(is_array($resource_number)){
						$resource_number = $resource_number[0];
						$check_by_person = true;
					}
				}
				if($ids){
					$or = '';
					if(is_array($ids)){
						foreach($ids as $v) if(is_numeric($v)) $or .= $v.',';
						if(!empty($or)) $or = substr($or, 0, -1);
					} else $or = $ids;
					$approve = "(approve='yes' or id in ($or))";
				} else $approve = "approve='yes'";

				if(RESERVATIONS_USE_TIME) $timepattern = 'Y-m-d H:i:s';
				else $timepattern = 'Y-m-d';

				if($this->id) $idsql = " id != '$this->id' AND";
				else $idsql = '';
				if($this->resource > 0){
					if($filter) $error = $this->availFilter($resource_number, $mode, $interval);
					if($mode < 3){
						if($mode == 0){
							$startdate = date("Y-m-d H:i:s", $this->arrival+60);
							$enddate = date("Y-m-d H:i:s", $this->departure-60);
							if($check_by_person){
								$prepare = $wpdb->prepare("SELECT SUM(number+childs) FROM ".$wpdb->prefix."reservations WHERE $approve AND $res_sql $idsql '%s' <= $departure AND '%s' >= $arrival", array($startdate, $enddate));
								$count = $wpdb->get_var($prepare);
								if($count == NULL || $count < 1) $count = 0;
								$count = $count+$this->childs+$this->adults;
								if($count > $resource_number) $error += $count;
							} else {
								$sql = $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."reservations WHERE $approve AND $res_sql $res_number $idsql '%s' <= $departure AND '%s' >= $arrival", array($startdate, $enddate));
								$count = $wpdb->get_var($sql);
								if(!empty($res_number) || $count >= $resource_number) $error += $count;
							}
						} else {
							for($t = 0; $t < $times; $t++){
								$i = $this->arrival+($t*$interval);
								$startdate = date("Y-m-d H:i:s", $i+60);
								if($this->departure < $i+$interval) $enddate = date("Y-m-d H:i:s", $this->departure-60);
								else $enddate = date("Y-m-d H:i:s", $i+$interval-60);
								if($interval == 3600)	$addstart = "AND HOUR($departure) != HOUR('$startdate')";
								else $addstart = '';
								if($check_by_person){
									$count = $wpdb->get_var($wpdb->prepare("SELECT SUM(number+childs) FROM ".$wpdb->prefix ."reservations WHERE $approve AND $res_sql $idsql ((%s < $departure AND %s > $arrival) OR (%s < $departure AND %s > $arrival)) $addstart", array($startdate, $startdate, $enddate, $enddate)));
									if($count < 1) $count = 0;
									$count = $count+$this->childs+$this->adults;
									if($mode == 1 && $count > $resource_number) $error[] = $i;
									elseif($mode == 0 && $count > $resource_number) $error += $resource_number;
								} else {
									$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE $approve AND $res_sql $idsql ((%s < $departure AND %s > $arrival) OR (%s < $departure AND %s > $arrival))", array($startdate, $startdate, $enddate, $enddate)));
									if($mode == 1 && $count >= $resource_number) $error[] = $i;
									elseif($mode == 0 && $count >= $resource_number) $error += $resource_number;
								}
							}
						}
					} else {
						$startdate = date($timepattern, $this->arrival);
						if($check_by_person){
							$addstart = '';
							if($interval == 3600) $addstart1 = "'$startdate' BETWEEN $arrival AND $departure - INTERVAL 1 SECOND";
							else $addstart1 = "DATE('$startdate') BETWEEN DATE($arrival) AND DATE($departure)";
							if(RESERVATIONS_USE_TIME && $interval > 86300) $addstart1 .= " AND (DATE('$startdate') != DATE($departure) OR HOUR($departure) > 11)";
							$count = $wpdb->get_var("SELECT sum(number+childs) as count FROM ".$wpdb->prefix ."reservations WHERE $approve AND $res_sql $idsql $addstart1 $addstart");
							if($mode == 5)
								$error = array($error+$count, $error+$count);
						} else {
							$addstart = ''; $addend = ''; $case = '1'; $caseArrival = '1'; $caseDeparture = '1';
							if($interval == 3600) $addstart1 = "'$startdate' BETWEEN $arrival AND $departure";
							else $addstart1 = "DATE('$startdate') BETWEEN DATE($arrival) AND DATE($departure)";
							if(RESERVATIONS_USE_TIME){
								if($interval == 3600){
									$addstart = " AND HOUR($arrival) = HOUR('$startdate')";
									$addend  = " AND HOUR($departure) = HOUR('$startdate')";
								} else {
									$addstart1 .= " AND (DATE($arrival) != DATE($departure) OR HOUR($departure) > 11)";
								}
								$case = "Case When DATE($departure) = DATE('$startdate')$addend AND HOUR($departure) < 12 Then 0 Else 1 End";
								$caseArrival = "Case When (DATE($departure) = DATE('$startdate')$addend) OR (DATE($arrival) = DATE('$startdate')$addstart) Then 1 Else 0 End";
							}

							$count = $wpdb->get_results("SELECT sum($case) as count,
								sum($caseArrival) as arrivals
								FROM ".$wpdb->prefix ."reservations WHERE $approve AND $res_sql $idsql $addstart1", ARRAY_A);
							if($mode == 5){
								$error = array($error+$count[0]["count"], $count[0]["arrivals"]);
							} else $count = $count[0]["count"];
						}

						if($mode == 4 && $count >= $resource_number) $error += $count;
						elseif($mode == 3) $error += $count;

					}
				}

				if(empty($error)) $error = false;
				else {
					if($mode !== 5 && is_array($error)){
						$started = false;
						$string = '';
						foreach($error as $key => $date){
							if(!$started){
								$string .= date($date_pattern, $date).' -';
								$started = true;
							} elseif(!isset($error[$key+1]) || $error[$key+1] != $date+$interval){
								$string .= ' '.date($date_pattern, $date).', ';
								$started = false;
							}
						}
						$error = $string;
					}
					if($mode == 1) $error = substr($error,0,-2);
				}
				return $error;
			}

			public function availFilter($resource_number=1, $mode=0, $interval = false){ //Check if a resource is Avail or Full
				$filters = get_post_meta($this->resource, 'easy_res_filter', true);
				if(!$interval) $interval = easyreservations_get_interval($this->interval, $this->resource, 1);
				if($this->departure == 0) $this->departure = $this->arrival + $interval;
				if($mode == 1 || $mode == 2) $error = array();
				else $error = 0;
				if(!empty($filters)){
					foreach($filters as $filter){
						if($filter['type'] == 'unavail'){
							for($t = 0; $t < $this->times; $t++){
								$i = $this->arrival + ($t*$this->interval);
								$check = $this->timeFilter($filter, $i, 'cond', $interval);
								if($check){
									if($mode == 1 && is_string($check)) $error .= $check;
									elseif($mode == 1) $error[] = $i;
									elseif($mode == 2)  $error[$i] = $resource_number;
									else $error += $resource_number;
								}
							}
						}
					}
				}
				return $error;
			}

			private function Filter($filter, $full = false){
				if($filter['type'] == 'price'){
					if(isset($filter['cond'])) $timecond = 'cond';
					if(isset($filter['basecond'])) $condcond = 'basecond';
					if(isset($filter['condtype'])) $condtype = 'condtype';
				} elseif($filter['type'] == 'req' || $filter['type'] == 'unavail' ){
					return false;
				} else {
					$use_filter = false;
					if(isset($filter['timecond'])) $timecond = 'timecond';
					if(isset($filter['cond'])) $condcond = 'cond';
					if(isset($filter['type'])) $condtype = 'type';
				}

				if(isset($condcond)){
					$discount_add = 0;
					if(!$full || empty($full) || (is_array($full) && !in_array($filter[$condtype], $full))){
						if($filter[$condtype] == 'stay'){
							if((int) $filter[$condcond] <= (int) $this->times){
								$discount_add = 1;
							}
						} elseif($filter[$condtype] == 'loyal'){// Loyal Filter
							if(is_email($this->email)){
								global $wpdb;
								$items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND email='%s'",$this->email)); //number of total rows in the database
								if($filter[$condcond] <= $items1){
									$discount_add = 1;
								}
							}
						} elseif($filter[$condtype] == 'pers'){// Persons Filter
							if($filter[$condcond] <= ($this->adults + $this->childs)){
								$discount_add = 1;
							}
						} elseif($filter[$condtype] == 'adul'){
							if($filter[$condcond] <= $this->adults){
								$discount_add = 1;
							}
						} elseif($filter[$condtype] == 'child'){
							if($filter[$condcond] <= $this->childs){
								$discount_add = 1;

							}
						} elseif($filter[$condtype] == 'early'){// Early Bird Discount Filter
							if($this->reservated == 0) $this->reservated = time();
							$dayBetween=round(($this->arrival-$this->reservated)/$this->interval,2);
							if($filter[$condcond] <= $dayBetween){
								$discount_add = 1;
							}
						}
					}
					if($discount_add == 0) return false;
				}

				if($filter['type'] == 'price' || isset($timecond)){
					for($t = 0; $t < $this->times; $t++){
						$i = $this->arrival + ($t*$this->interval);
						if($filter['type'] == 'price'){
							if(isset($this->once) && $this->countpriceadd > 0) break;
							if(!is_array($this->datearray) || !in_array($i, $this->datearray)){
								if(!isset($timecond) || (isset($timecond) && $this->timeFilter($filter,$i,$timecond))){
									if(strpos($filter['price'], '%') !== false){
										$percent = str_replace('%',  '', $filter['price']);
										$amount = round($this->groundprice/100*$percent,2);
									} else $amount = $filter['price'];
									$this->price+=$amount;
									$this->countpriceadd++;
									$this->datearray[] = $i;
									$this->history[] = array('date' => $i, 'priceday' => $amount, 'type' => 'pricefilter', 'name' => __($filter['name']));
								}
							}
						} else {
							if($this->timeFilter($filter,$i,$timecond)){
								$use_filter = true;
							}
						}
					}
					if(isset($use_filter)) return $use_filter;
				}
				return true;
			}

			private function timeFilter($filter, $i, $cond = 'cond', $interval = false){
				if($filter[$cond] == 'unit'){
					if(!$this->unitFilter($filter,$i, $interval)) return false;
				}
				if(isset($filter['date'])){
					if($this->interval == 86400 && (date("d.m.Y", $i) == date("d.m.Y", $filter['date']))) return true;
	        elseif($this->interval < 3601 && date("d.m.Y H",$i) == date("d.m.Y H", $filter['date']))  return true;
	        elseif ($this->interval > 86450 && $i >= $this->arrival && $i <= $this->departure) return true;
					else return false;
				}
				if(isset($filter['from'])){
					if($i >= $filter['from'] && $i  <= $filter['to']){
						return true;
					} else return false;
				}
				return true;
			}

			private function unitFilter($filter, $i, $interval = false){
				if(!$interval) $interval = $this->interval;
				if(!isset($filter['year']) || empty($filter['year']) || ( in_array(date("Y", $i), explode(",", $filter['year'])))){
					if(!isset($filter['quarter']) || empty($filter['quarter']) || ( in_array(ceil(date("m", $i) / 3), explode(",", $filter['quarter'])))){
						if(!isset($filter['month']) || empty($filter['month']) || ( in_array(date("n", $i), explode(",", $filter['month'])))){
							if(!isset($filter['cw']) || empty($filter['cw']) || ( in_array(date("W", $i), explode(",", $filter['cw'])))){
								if(!isset($filter['day']) || empty($filter['day']) || ($interval < 86500 &&  in_array(date("N", $i), explode(",", $filter['day'])))){
									if(!isset($filter['hour']) || empty($filter['hour']) || ($interval < 3650 && in_array(date("H", $i), explode(",", $filter['hour'])))){
										return true;
									} elseif(isset($filter['hour']) && !empty($filter['hour']) && ($interval > 3650)){
										$time = strtotime(date(RESERVATIONS_DATE_FORMAT,$i));
										foreach(explode(",", $filter['hour']) as $hour){
											$checktime = $time+($hour*3600);
											if($this->arrival <= $checktime && $this->departure >=  $checktime) return date(RESERVATIONS_DATE_FORMAT.' H:00', $checktime).', ';
										}
									}
								}
							}
						}
					}
				}
				return false;
			}

			/**
			 * Add/edit/delete custom information
			 * @param array $new_custom
			 * @param bool $mass true for replacing all customs of the selected type/modus/check
			 * @param bool/int $thekey Key of custom information to edit/delete
			 * @param bool $price true for price fields
			 * @param string/bool $type Filter by type; false for all
			 * @param string/bool $modus Filter by modues; false for all
			 * @param array/bool $check Filter by array('attribute'  => 'value'); false for all
			 * @return array with all customs/prices
			 */
			public function Customs($new_custom, $mass = false, $thekey = false, $price = false, $type = false, $modus = false, $check = false){
				if($price) $all_customs = $this->prices;
				else $all_customs = $this->custom;
				$all_customs_save = array();

				if(!empty($all_customs)){
					$all_customs_save  = maybe_unserialize($all_customs);
					if($mass){
						if(!empty($all_customs_save)){
							foreach($all_customs_save as $key => $cstm){
								if((!$type || $cstm['type'] == $type) && (!$modus || $cstm['mode'] == $modus || $cstm['mode'] == 'visible') && (!$check || $cstm[$check[0]] == $check[1])){
									unset($all_customs_save[$key]);
								}
							}
						}
					} else {
						if(!is_numeric($thekey)){
							$all_customs_save = $all_customs_save;
						} else {
							if(isset($all_customs_save[$thekey]) && (!$type || $all_customs_save[$thekey]['type'] == $type) && (!$modus || $all_customs_save[$thekey]['mode'] == $modus) && (!$check || $all_customs_save[$thekey][$check[0]] == $check[1])) unset($all_customs_save[$thekey]);
							else return false;
						}
					}
				}

				if(!is_array($all_customs_save) || empty($all_customs_save)) $all_customs_save = array();
				if($new_custom){
					if(!isset($new_custom[0]) || !is_array($new_custom[0])) $new_custom = array($new_custom);
					foreach($new_custom as $newcustom){
						$all_customs_save[] = $newcustom;
					}
				}

				if($price) $this->prices = $all_customs_save;
				else $this->custom = $all_customs_save;
				return $all_customs_save;
			}

			public function getCustoms($custom = false, $type = false, $modus = false, $order = false){
				if(!$custom) $custom = $this->custom;
				if(!is_array($custom)){
					$custom = maybe_unserialize($custom);
				}

				if(!empty($custom) && is_array($custom)){
					foreach($custom as $key => $cstm){
						if(empty($cstm) || ( $type && isset($cstm['type']) && $cstm['type'] !== $type ) || ( $modus && isset($cstm['mode']) && $cstm['mode'] != $modus && $cstm['mode'] != 'visible' )) unset($custom[$key]);
						elseif($order){
							if(isset($cstm['id'])){
								if(isset($custom['c'.$cstm['id']])) $custom[] = $cstm;
								else $custom['c'.$cstm['id']] = $cstm;
							} else $custom[strtolower($cstm['title'])] = $cstm;
							unset($custom[$key]);
						}
					}
				}

				return $custom;
			}

			public function getCustomsValue($custom){
				$custom_fields = get_option('reservations_custom_fields');
				$return = $custom['value'];
				if(isset($custom['id']) && isset($custom_fields['fields'][$custom['id']])){
					$custom_field = $custom_fields['fields'][$custom['id']];
					if($custom_field['type'] == 'check') return $custom_field['title'];
					elseif($custom_field['type'] !== 'text' && $custom_field['type'] !== 'area' ){
						if(isset($custom_field['options']) && isset($custom_field['options'][$custom['value']])){
							return $custom_field['options'][$custom['value']]['value'];
						}
					}
				}
				return $return;
			}

			function formatPrice($color_paid=false, $cur = true, $dig = 2, $amount = false){
				if(!$amount && $amount !== 0) $amount = $this->price;
				if($cur) $price = easyreservations_format_money($amount, 1, $dig);
				else $price = easyreservations_format_money($amount, 0, $dig);

				if($color_paid){
					if($this->paid == $this->price) $price_bgcolor='color:#118D18;';
					elseif($this->paid > $this->price) $price_bgcolor='color:#ab2ad6;';
					elseif($this->paid > 0) $price_bgcolor='color:#ffcb49;';
					else $price_bgcolor='color:#BC0B0B;';
					$price = '<b style="'.$price_bgcolor.';padding:1px;font-weight:bold !important;">'.$price.'</b>';
				}
				return $price;
			}

			public function getTimes($mode = 1, $interval = false){
				if($interval == false) $interval = $this->interval;
				if(RESERVATIONS_USE_TIME == 1){
					$arrival = $this->arrival;
					$departure = $this->departure;
				} else {
					if($interval > 86300){
						$arrival = strtotime(date('d.m.Y', (int) $this->arrival))+43200;
						$departure = strtotime(date('d.m.Y', (int) $this->departure))+43200;
					} else {
						$arrival = $this->arrival;
						$departure = $this->departure+1800;
					}
				}
				$diff = 0;
				if(version_compare(PHP_VERSION, '5.3.0') >= 0 && is_numeric($departure)){
					$timezone = new DateTimeZone(date_default_timezone_get ());
					$transitions = $timezone->getTransitions($arrival, $departure);
					if(isset($transitions[1]) && $transitions[0]['offset'] != $transitions[1]['offset']){
						if($transitions[0]['offset'] > $transitions[1]['offset']) $diff = $transitions[0]['offset'] - $transitions[1]['offset'];
						else $diff = $transitions[1]['offset'] - $transitions[0]['offset'];
					}
				}
				//var_dump(easyreservations_get_interval($interval, 0,  $mode));
				$number = ($departure-$arrival-$diff) / $interval;
				$times = ( is_numeric($number)) ? (ceil(ceil($number/0.01)*0.01)) : false;
				if($times < 1) $times = 1;
				return $times;
			}

			public function Validate($mode = 'send', $avail = 1, $mini = false, $ids = false){
				$errors = array();
				$this->name = trim($this->name);
				if(strlen($this->name) > 50 || ($mode == 'send' && (empty($this->name) || strlen($this->name) <= 1))){
					if(!$this->admin) $errors[] = 'easy-form-thename';
					$errors[] = __( 'Please enter your name' , 'easyReservations' );
				}

				$this->email = trim($this->email);
				if($mode == 'send'  && (!is_email( $this->email) || empty($this->email))){ /* check email */
					if(!$this->admin) $errors[] = 'easy-form-email';
					$errors[] =  __( 'Please enter a correct email' , 'easyReservations' );
				}

				if($this->departure < 1000000 ||  $this->arrival < 1000000){
					if(!$this->admin) $errors[] = 'date';
					$errors[] =  __( 'Please enter correct dates' , 'easyReservations' );
					$daterror = true;
				}

				if($this->departure < $this->arrival){ /* check arrival Date */
					if(!$this->admin)  $errors[] = 'easy-form-to';
					$errors[] = __( 'The departure date has to be after the arrival date' , 'easyReservations');
					$daterror = true;
				}

				if(!is_numeric($this->adults) || $this->adults < 1){ /* check persons */
					if(!$this->admin)  $errors[] = 'easy-form-persons';
					$errors[]  = __( 'Adults has to be a number at least one' , 'easyReservations' );
				}
				$this->adults = (int) $this->adults;

				if(!is_numeric($this->childs)){ /* check persons */
					if(!$this->admin)  $errors[] = 'easy-form-childs';
					$errors[]  = __( 'Children\'s has to be a number' , 'easyReservations' );
				}
				$this->childs = (int) $this->childs;

				if(!isset($daterror) && !$mini){
					$availability = $this->checkAvailability($avail, ($this->admin) ? false : true, 86400, $ids);
					if($availability){
						if(!$this->admin){
							$errors[] = 'date';
							if($avail > 0) $errors[] = __( 'Not available at' , 'easyReservations' ).' '.$availability;
							else $errors[] = __( 'Selected time is occupied' , 'easyReservations' );
						} else $errors[] = __( 'Selected time is occupied' , 'easyReservations' );
					}
	//				else {
	//					$availability = $this->checkAvailability(0, ($this->admin) ? false : true);
	//					if($availability){
	//						if(!$this->admin) $errors[] = 'date';
	//						$errors[] = __( 'Selected time is occupied' , 'easyReservations' );
	//					}
	//				}
				}

				if(!$this->admin){
					if($this->arrival < time()-86400){ /* check arrival Date */
						$errors[] = 'easy-form-from';
						$errors[] = __( 'The arrival date has to be in future' , 'easyReservations');
					}
					$filters = get_post_meta($this->resource, 'easy_res_filter', true);
					$checked = false;
					if($filters && !empty($filters)){
						foreach($filters as $filter){
							if($filter['type'] == 'req'){
								$price_add = 0;
								for($t = 0; $t < $this->times; $t++){
									$i = $this->arrival + ($t*$this->interval);
									if($this->timeFilter($filter, $i)){
										$price_add = 1;
										break;
									}
								}
								if($price_add == 1){
									$checked = true;
									$errors = $this->checkRequirements($filter['req'], $errors, $mini);
									if(!empty($errors)) return $errors;
								}
							}
						}
					}
					if(!$checked){
						$resource_req = get_post_meta($this->resource, 'easy-resource-req', TRUE);
						if(!$resource_req || !is_array($resource_req)) $resource_req = array('nights-min' => 0, 'nights-max' => 0, 'pers-min' => 1, 'pers-max' => 0);
						$errors = $this->checkRequirements($resource_req, $errors, $mini);
					}
				}
				if(empty($errors)) return false;
				else return $errors;
			}

			private function checkRequirements($resource_req, $errors, $mini = false){
				easyreservations_load_resources(true);
				global $the_rooms_array, $the_rooms_intervals_array, $easy_max_persons;
				$easy_max_persons = $resource_req['pers-max'];
				if($resource_req['pers-min'] > ($this->adults+$this->childs)){
					if($mini) $errors[] = array('pers-min', $resource_req['pers-min']);
					else {
						$errors[] = 'easy-form-persons';
						$errors[] =  sprintf(__( 'At least %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-min'], __($the_rooms_array[$this->resource]->post_title));
					}
				}
				if($resource_req['pers-max'] != 0 && $resource_req['pers-max'] < ($this->adults+$this->childs)){
					if($mini) $errors[] = array('pers-max', $resource_req['pers-max']);
					else {
						$errors[] = 'easy-form-persons';
						$errors[] =  sprintf(__( 'Maximum %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-max'], __($the_rooms_array[$this->resource]->post_title));
					}
				}
				if($resource_req['nights-min'] != 0 && $resource_req['nights-min'] > $this->times){
					if($mini) $errors[] = array('nights-min', $resource_req['nights-min']);
					else {
						$errors[] = 'date';
						$errors[] =  sprintf(__( 'At least %1$s %2$s in %3$s' , 'easyReservations' ), $resource_req['nights-min'], easyreservations_interval_infos($the_rooms_intervals_array[$this->resource], 0, $resource_req['nights-min']), __($the_rooms_array[$this->resource]->post_title));
					}
				}
				if($resource_req['nights-max'] != 0 && $resource_req['nights-max'] < $this->times){
					if($mini) $errors[] = array('nights-max', $resource_req['nights-max']);
					else {
						$errors[] = 'date';
						$errors[] =  sprintf(__( 'Maximum %1$s %2$s in %3$s' , 'easyReservations' ), $resource_req['nights-max'], easyreservations_interval_infos($the_rooms_intervals_array[$this->resource], 0, $resource_req['nights-max']), __($the_rooms_array[$this->resource]->post_title));
					}
				}
				$day_names = easyreservations_get_date_name(0, 3);
				if(isset($resource_req['start-on']) && $resource_req['start-on'] != 0){
					if(!in_array(date("N", $this->arrival), $resource_req['start-on'])){
						$errors[] = 'easy-form-from';
						$start_days = '';
						foreach($resource_req['start-on'] as $starts){
							$start_days .= $day_names[$starts-1].', ';
						}
						if($mini) $errors[] = array('start-on', substr($start_days,0,-2));
						else $errors[] = sprintf(__( 'Arrival only possible on %s' , 'easyReservations' ), substr($start_days,0,-2));
					}
				}
				if(isset($resource_req['end-on']) && $resource_req['end-on'] != 0){
					if(!in_array(date("N", $this->departure), $resource_req['end-on'])){
						$errors[] = 'easy-form-to';
						$end_days = '';
						foreach($resource_req['end-on'] as $ends){
							$end_days .= $day_names[$ends-1].', ';
						}
						if($mini) $errors[] = array('end-on', substr($end_days,0,-2));
						else $errors[] = sprintf(__( 'Departure only possible on %s' , 'easyReservations' ), substr($end_days,0,-2));
					}
				}
				$zero = strtotime('20.10.2010 00:00:00');
				if(isset($resource_req['start-h']) && is_array($resource_req['start-h'])){
					if(date("G.i", $this->arrival) < $resource_req['start-h'][0]){
						$errors[] = 'easy-form-from';
						$errors[] = sprintf(__( 'Arrival only possible from %s' , 'easyReservations' ), date(easyreservations_get_time_pattern(), $zero+($resource_req['start-h'][0]*3600)));
					}
					if(date("G.i", $this->arrival) > $resource_req['start-h'][1]){
						$errors[] = 'easy-form-from';
						$errors[] = sprintf(__( 'Arrival only possible till %s' , 'easyReservations' ), date(easyreservations_get_time_pattern(), $zero+($resource_req['start-h'][1]*3600)));
					}
				}
				if(isset($resource_req['end-h']) && is_array($resource_req['end-h'])){
					if(date("G.i", $this->departure) < $resource_req['end-h'][0]){
						$errors[] = 'easy-form-to';
						$errors[] = sprintf(__( 'Departure only possible from %s' , 'easyReservations' ), date(easyreservations_get_time_pattern(), $zero+($resource_req['end-h'][0]*3600)));
					}
					if(date("G.i", $this->departure) > $resource_req['end-h'][1]){
						$errors[] = 'easy-form-to';
						$errors[] = sprintf(__( 'Departure only possible till %s' , 'easyReservations' ), date(easyreservations_get_time_pattern(), $zero+($resource_req['end-h'][1]*3600)));
					}
				}
				return $errors;
			}

			public function updatePricepaid($amount){
				if(!empty($this->pricepaid)){
					$explode = explode(';', $this->pricepaid);
					if(isset($explode[1]) && $explode[1] > 0) $amount = $amount + $explode[1];
					$this->pricepaid = $explode[0].';'.$amount;
				} else $this->pricepaid = ';'.$amount;
			}

			/**
			 * Send Mail
			 * @param string $options_name name of email option
			 * @param string $to (optional) Receiver's email - default: $this->email
			 * @param string $attachment (optional) URL of Attachment - default: false
			 * @return type bool true on success
			 */
			public function sendMail($options_name, $to = false, $attachment = false){
				if(is_array($options_name)){
					$option = $options_name;
					$options_name = 'none';
				} else $option = get_option($options_name);

				if(isset($option['active']) && $option['active'] == 1){
					$theForm = $option['msg'];
					$subj = $option['subj'];
					$local = false;
					if(isset($_POST['easy-set-local'])){
						$oldlocal = get_locale();
						$local = $_POST['easy-set-local'];
						setlocale(LC_TIME, $local);
					}
					$customs = $this->getCustoms($this->custom, 'cstm', 'edit', true);
					$custom_prices = $this->getCustoms($this->prices, 'cstm', 'edit', true);

					if(isset($_POST["approve_message"]) && !empty($_POST["approve_message"])) $theForm = stripslashes($_POST["approve_message"]).'-!DIVMESSAGE!-'.$theForm;
					$theForm = $theForm.'-!DIVSUBJECT!-'.$subj;
					$this->Calculate();
					$tags = easyreservations_shortcode_parser($theForm, true);
					foreach($tags as $fields){
						$field=shortcode_parse_atts( $fields);
						if(!isset($field[0])) continue;
						if($field[0]=="adminmessage"){
							$explode = explode('-!DIVMESSAGE!-',$theForm);
							if(isset($explode[1])){
								$message = $explode[0];
								$theForm = $explode[1];
							} elseif(isset($_POST["approve_message"])) $message = $_POST["approve_message"];
							$theForm=preg_replace('/\['.$fields.']/U', $message, $theForm);
						} elseif($field[0]=="ID"){
							$theForm=preg_replace('/\['.$fields.']/U', $this->id, $theForm);
						} elseif($field[0]=="thename"){
							$theForm=preg_replace('/\['.$fields.']/U', $this->name, $theForm);
						} elseif($field[0]=="email"){
							$theForm=preg_replace('/\['.$fields.']/U', $this->email, $theForm);
						} elseif($field[0]=="arrivaldate" || $field[0]=="arrival" || $field[0]=="date-from"){
							$format = isset($field['format']) ? $field['format'] : RESERVATIONS_DATE_FORMAT_SHOW;
							$theForm=preg_replace('/\['.$fields.']/U', date($format, $this->arrival), $theForm);
						} elseif($field[0]=="changelog"){
							$changelog = isset($this->changelog) ? $this->changelog : '';
							$theForm=preg_replace('/\['.$fields.']/U', $changelog, $theForm);
						} elseif($field[0]=="departuredate" || $field[0]=="departure"){
							$format = isset($field['format']) ? $field['format'] : RESERVATIONS_DATE_FORMAT_SHOW;
							$theForm=preg_replace('/\['.$fields.']/U', date($format, $this->departure), $theForm);
						} elseif($field[0]=="units" || $field[0]=="times"){
							$theForm=preg_replace('/\['.$fields.']/U', $this->times, $theForm);
						} elseif($field[0]=="nights" || $field[0]=="days"){
							$theForm=preg_replace('/\['.$fields.']/U', easyreservations_get_nights(86400, $this->reservated, time() ), $theForm);
						} elseif($field[0]=="hours"){
							$theForm=preg_replace('/\['.$fields.']/U', easyreservations_get_nights(3600, $this->reservated, time() ), $theForm);
						} elseif($field[0]=="weeks"){
							$theForm=preg_replace('/\['.$fields.']/U', easyreservations_get_nights(604800, $this->reservated, time(), 0), $theForm);
						} elseif($field[0]=="adults"){
							$theForm=preg_replace('/\['.$fields.']/U', $this->adults, $theForm);
						} elseif($field[0]=="childs"){
							$theForm=preg_replace('/\['.$fields.']/U', $this->childs, $theForm);
						} elseif($field[0]=="date"){
							if(isset($field[1])) $date = date($field[1], time());
							else $date = date(RESERVATIONS_DATE_FORMAT_SHOW, time());
							$theForm=preg_replace('/\['.$fields.']/U', $date, $theForm);
						} elseif($field[0]=="persons"){
							$theForm=preg_replace('/\['.$fields.']/U', $this->childs+$this->adults, $theForm);
						} elseif($field[0]=="rooms" || $field[0]=="resource"){
							$theForm=preg_replace('/\['.$fields.']/U',$this->resourcename, $theForm);
						} elseif($field[0]=="roomnumber" || $field[0]=="resource-number" || $field[0]=="resource-nr"){
							$theForm=preg_replace('/\['.$fields.']/U', __(easyreservations_get_roomname($this->resourcenumber, $this->resource)), $theForm);
						} elseif($field[0]=="country"){
							$theForm=preg_replace('/\['.$fields.']/U', easyreservations_country_name($this->country), $theForm);
						} elseif($field[0]=="taxes"){
							$theForm=str_replace('[taxes]', easyreservations_format_money($this->taxamount), $theForm);
						} elseif($field[0]=="price"){
							$theForm=str_replace('[price]', easyreservations_format_money($this->price), $theForm);
						} elseif($field[0]=="paid"){
							$theForm=str_replace('[paid]', easyreservations_format_money($this->paid), $theForm);
						} elseif($field[0]=="balance"){
							$theForm=str_replace('[balance]', easyreservations_format_money($this->price - $this->paid), $theForm);
						} elseif($field[0]=="coupon"){
							$theForm=str_replace('[coupon]', $this->coupon, $theForm);
						} elseif($field[0]=="editlink"){
							$the_link = get_option("reservations_edit_url");
							$nonce =  substr(wp_hash(wp_nonce_tick() .'|easyusereditlink|0', 'nonce'), -12, 10);
							$the_edit_link = trim($the_link).'?edit&id='.$this->id.'&email='.urlencode($this->email).'&ernonce='.$nonce;
							if(!empty($the_link)) $theForm=str_replace('[editlink]', $the_edit_link, $theForm);
							else  $theForm=str_replace('[editlink]', '', $theForm);
						} elseif($field[0]=="custom" && isset($field['id'])){
							$content = '';
							$custom_fields = get_option('reservations_custom_fields');
							if(isset($custom_fields['fields'][$field['id']])){
								$custom_field = $custom_fields['fields'][$field['id']];
								if(isset($custom_field['price'])) $array = $custom_prices;
								else $array = $customs;
								if(isset($array['c'.$field['id']])){
									$cstm = $array['c'.$field['id']];
									if(!isset($field['show'])){
										$content = $this->getCustomsValue($cstm);
										if(isset($custom_field['price'])) $content .= ' ('.easyreservations_format_money($this->calculateCustom($field['id'], $cstm['value'], $this->prices),1).')';
									} elseif($field['show'] == 'title') $content = $custom_field['title'];
									elseif($field['show'] == 'value'){
										$content = $this->getCustomsValue($cstm);
									} elseif($field['show'] == 'amount') $content = easyreservations_format_money($this->calculateCustom($field['id'], $cstm['value'], $this->prices),1);
								} else $content = $custom_field['else'];
							}
							$theForm=str_replace('['.$fields.']', $content, $theForm);
						} elseif($field[0]=="customs"){
							$custom_in_mail = '';
							if(!empty($this->custom)){
								if(!isset($field[1])){
									foreach($customs as $custom) $custom_in_mail .= $custom['title'].': '.$custom['value'].'<br>';
								} elseif(isset($field[1]) && isset($customs[strtolower($field[1])])){
									$custom_in_mail .= $customs[strtolower($field[1])]['value'];
								} elseif(isset($field[1]) && isset($field['else'])) $custom_in_mail .= $field['else'];
							}
							$theForm=str_replace('['.$fields.']', $custom_in_mail, $theForm);
						} elseif($field[0]=="customprices" || $field[0]=="prices"){
							$custom_in_mail = '';
							if(!empty($this->prices)){
								if(!isset($field[1])){
									foreach($custom_prices as $custom) $custom_in_mail .= $custom['title'].' - '.$custom['value'].':  '.easyreservations_format_money($custom['amount']).'<br>';
								} elseif(isset($field[1]) && isset($custom_prices[strtolower($field[1])])){
									$custom = $custom_prices[strtolower($field[1])];
									if(isset($field['hide'])){
										if($field['hide'] == 'amount') $custom_in_mail = $custom['value'];
										else $custom_in_mail = easyreservations_format_money($custom['amount']);
									} else $custom_in_mail .= $custom['value'].': '.easyreservations_format_money($custom['amount']);
								} elseif(isset($field[1]) && isset($field['else'])) $custom_in_mail .= $field['else'];
							}
							$theForm=str_replace('['.$fields.']', $custom_in_mail, $theForm);
						} elseif($field[0]=="paypal"){
							$link = '';
							if(function_exists('easyreservations_generate_paypal_button')){
								$percent = false;
								$price = $this->price;
								if(isset($field[1]) && is_numeric($field[1])) $percent = $field[1];
								elseif(isset($field[1]) && $field[1] == "due") $price = $this->price - $this->paid;
								$link = easyreservations_generate_paypal_button($this, $price, true, true, $percent);
								if(isset($field['title'])) $link = '<a href="'.$link.'">'.str_replace('"', '', $field['title']).'</a>';
							}
							$theForm = str_replace('['.$fields.']', $link, $theForm);
						} else $theForm = apply_filters('easy-email-tag', $theForm, $fields, $field, $this);
					}

					$explode = explode('-!DIVSUBJECT!-', $theForm);
					$theForm = apply_filters( 'easy-email-content', $explode[0], $local);
					$subj = apply_filters( 'easy-email-subj', $explode[1], $local);
					$support_mail = get_option("reservations_support_mail");

					if(function_exists('easyreservations_send_multipart_mail')) $msg = easyreservations_send_multipart_mail($theForm);
					else {
						$theForm = explode('<--HTML-->', $theForm);
						$msg = htmlspecialchars_decode(str_replace('<br>', "\n",str_replace(']', '',  str_replace('[', '', $theForm[0]))));
					}

					if(empty($support_mail)) throw new easyException( 'No support email found', 6 );
					elseif(is_array($support_mail)) $send_from = $support_mail[0];
					else{
						if(preg_match('/[\,]/', $support_mail)){
							$support_mail  = explode(',', $support_mail);
							$send_from = $support_mail[0];
						} else $send_from = $support_mail;
					}

					$headers = "From: \"".str_replace(array(','), array(''), get_bloginfo('name'))."\" <".$send_from.">\n";
					if(!$attachment && function_exists('easyreservations_insert_attachment')) $attachment = easyreservations_insert_attachment($this, str_replace('reservations_email_', '', $options_name));

					if(!$to || empty($to)){
						$to = $support_mail;
						$headers = "From: \"".$this->name."\" <".$this->email.">\n";
					}

					$mail = @wp_mail($to,$subj,$msg,$headers,$attachment);
					if(isset($oldlocal)) setlocale(LC_TIME, $oldlocal);
					if($attachment) unlink($attachment);
					return $mail;
	      }
			}

			private function generateChangelog(){
				easyreservations_load_resources();
				global $the_rooms_array;
				$beforeArray = $this->save;
				unset($this->save);
				$afterArray = (array) $this;
				$changelog = '';

				if($beforeArray['arrival'] != $afterArray['arrival']) $changelog .= __('The arrival date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT, $beforeArray['arrival']).' => '.date(RESERVATIONS_DATE_FORMAT, $afterArray['arrival']).'<br>';
				if($beforeArray['departure'] != $afterArray['departure']) $changelog .= __('The departure date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT_SHOW, $beforeArray['departure']).' => '.date(RESERVATIONS_DATE_FORMAT_SHOW, $afterArray['departure']).'<br>';
				if($beforeArray['name'] != $afterArray['name']) $changelog .= __('The name was edited' , 'easyReservations' ).': '.$beforeArray['name'].' => '.$afterArray['name'].'<br>';
				if($beforeArray['email'] != $afterArray['email']) $changelog .= __('The email was edited' , 'easyReservations' ).': '.$beforeArray['email'].' => '.$afterArray['email'].'<br>';
				if($beforeArray['adults'] != $afterArray['adults']) $changelog .= __('The amount of adults was edited' , 'easyReservations' ).': '.$beforeArray['adults'].' => '.$afterArray['adults'].'<br>';
				if($beforeArray['childs'] != $afterArray['childs']) $changelog .= __('The amount of children was edited' , 'easyReservations' ).': '.$beforeArray['childs'].' => '.$afterArray['childs'].'<br>';
				if($beforeArray['country'] != $afterArray['country']) $changelog .= __('The country was edited' , 'easyReservations' ).': '.$beforeArray['country'].' => '.$afterArray['country'].'<br>';
				if($beforeArray['resource'] != $afterArray['resource']) $changelog .= __('The resource was edited' , 'easyReservations' ).': '.__($the_rooms_array[$beforeArray['resource']]->post_title).' => '.__($the_rooms_array[$afterArray['resource']]->post_title).'<br>';
				if($beforeArray['custom'] != $afterArray['custom']) $changelog .= __('Custom fields got edited', 'easyReservations' ).'<br>';
				if(isset($beforeArray['prices']) && $beforeArray['prices'] != $afterArray['prices']) $changelog .= __('Prices got edited' , 'easyReservations' ).'<br>';

				return $this->changelog = $changelog;
			}

			public function getStatus($color = false){
				$statuse = array('yes' => array(__('approved', 'easyReservations' ), '#1FB512'), 'no' => array(__('rejected', 'easyReservations' ), '#D61111'), 'del' => array(__('trashed', 'easyReservations' ), '#870A0A'), '' => array(__('pending', 'easyReservations' ), '#3BB0E2'));

				$formated_status = $statuse[$this->status][0];
				if($color) $formated_status = '<b style="color:'.$statuse[$this->status][1].';text-transform:capitalize">'.$formated_status.'</b>';
				$formated_status = apply_filters('easy-status-out', $formated_status, $this);

				return $formated_status;
			}

			//----------------------------------------- Save -------------------------------------------------//

			/**
			 * Call to edit reservation
			 * @param array $informations informations to edit
			 * @param bool $validate false to not validate reservation
			 * @param bool/string $mail (optional) name of email's option
			 * @return bool true on success
			 */
			public function editReservation($informations = array('all'), $validate = true, $mail = false, $to = false){
				if(is_array($informations) && !empty($informations)){
					$array = array();
					if($informations[0] == 'all') $informations = array('arrival', 'name', 'email', 'departure', 'resource', 'resourcenumber', 'adults', 'childs', 'country', 'status', 'custom', 'prices', 'reservated', 'user', 'pricepaid');
					foreach($informations as $information){
						if(isset($this->$information)) $array[$information] = $this->$information;
					}
					if($this->admin && $this->status !== 'yes') $theval = false;
					else $theval = $this->Validate('send', 0);
					if(!$validate || !$theval){
						$edit = $this->edit($this->ReservationToArray($array));
						if($mail && !$edit){
							if(!is_array($mail)) $mail = array($mail);
							if(!is_array($to)) $to = array($to);
							foreach($mail as $key => $themail) if($to[$key] !== true)  $this->sendMail($mail[$key], $to[$key]);
						}
						return $edit;
					} else return $theval;
				}
			}

			/**
			 * Call to add reservation
			 * @param mixed $mail (optional) name of email's option
			 * @return int ID of new reservation
			 */
			public function addReservation($mail = false, $to = false, $ids = false){
				if($this->admin && $this->status != 'yes') $validate = false;
				else $validate = $this->Validate('send', 1, false, $ids);
				if(!$validate){
					$array = array();
					foreach($this as $key => $information){
						$array[$key] = $information;
					}
					$add = $this->add($this->ReservationToArray($array));
					if($mail && !$add){
						if(!is_array($mail)) $mail = array($mail);
						if(!is_array($to)) $to = array($to);
						foreach($mail as $key => $the_mail) if($to[$key] !== true) $this->sendMail($mail[$key], $to[$key]);
					}
					return $add;
				} else return $validate;
			}

			/**
			 *	Objects informations array to database array
			 * @param array $array the array to edit
			 */
			private function ReservationToArray($array){
				if(isset($array['custom']) && is_array($array['custom'])) $array['custom'] = maybe_serialize($array['custom']);
				if(isset($array['prices']) && is_array($array['prices'])) $array['prices'] = maybe_serialize($array['prices']);
				if(isset($array['resource'])) $array['room'] = (int) $array['resource'];
				if(isset($array['resourcenumber'])) $array['roomnumber'] = $array['resourcenumber'];
				if(isset($array['status'])) $array['approve'] = $array['status'];
				if(isset($array['prices'])) $array['customp'] = $array['prices'];
				if(isset($array['custom'])) $array['custom'] = $array['custom'];
				if(isset($array['adults'])) $array['number'] = $array['adults'];
				if(isset($array['reservated'])) $array['reservated'] = date('Y-m-d H:i:s', $array['reservated']);
				if(isset($array['pricepaid'])) $array['price'] = $array['pricepaid'];
				if(isset($array['arrival'])) $array['arrival'] = date('Y-m-d H:i:s', $array['arrival']);
				if(isset($array['departure'])) $array['departure'] = date('Y-m-d H:i:s', $array['departure']);
				if(!isset($array['price'])) $array['price'] = '';
				unset($array['resource'], $array['resourcenumber'], $array['status'], $array['prices'], $array['adults'], $array['pricepaid']);
				return $array;
			}

			/**
			 * Edit reservation
			 *
			 * @global obj $wpdb database connection
			 * @return bool true on succes
			 * @throws easyException mysql error
			 */
			private function edit($array){
				if(!empty($array)){
					global $wpdb;
					$sql = array();
					foreach($array as $key => $info){
						$sql[$key] = $info;
					}
					$return = $wpdb->update( $wpdb->prefix.'reservations', $sql, array('id' => $this->id));
					if($return === 0){
						throw new easyException( 'No changes');
						return true;
					} elseif(!$return){
						throw new easyException( 'Reservation could not be edited. Error: '.mysql_error(), mysql_errno() );
						return true;
					} else return false;
				}
			}

			/**
			 * Add reservation
			 *
			 * @global obj $wpdb database connection
			 * @return int ID of new reservation
			 * @throws easyException mysql error
			 */
			private function add($array){
				global $wpdb;
				$informations = array('arrival', 'name', 'email', 'departure', 'room', 'roomnumber', 'number', 'childs', 'country', 'approve', 'custom', 'customp', 'reservated', 'user', 'price');
				$rarray = array();
				foreach($array as $key => $info){
					if(!in_array($key, $informations)) unset($array[$key]);
					else {
						$rarray[$key] = $info;
					}
				}
				if(!isset($rarray['price'])) $rarray['price'] = '';
				$return = $wpdb->insert( $wpdb->prefix.'reservations', $rarray);
				if(!$return){
					throw new easyException( 'Reservation couldn\'t be added. Error: '.mysql_error(), mysql_errno() );
					return true;
				} else {
					$this->id = $wpdb->insert_id;
					if(!$this->admin) do_action('easy-add-res', $this, 1);
					return false;
				}
			}

			public function destroy(){
				//unset($this);
			}

			public function deleteReservation(){
				global $wpdb;
				$return =$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE id='%d'", $this->id) );
				if(!$return){
					throw new easyException( 'Reservation couldn\'t be deleted. Error: '.mysql_error(), mysql_errno() );
					return true;
				}
			}
		}

		class easyException extends Exception {}
	?>