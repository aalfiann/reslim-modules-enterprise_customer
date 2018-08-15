<?php
namespace modules\enterprise_customer;
use \classes\Auth as Auth;
use \classes\JSON as JSON;
use \classes\Validation as Validation;
use \classes\CustomHandlers as CustomHandlers;
use PDO;
	/**
     * A class for master data industry
     *
     * @package    modules/enterprise_customer
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reslim-modules-enterprise_customer/blob/master/LICENSE.md  MIT License
     */
	class Industry {
        // model data industry
		var $username,$industryid,$industry;
		
		// for pagination
		var $page,$itemsPerPage;

		// for search
		var $search;

		// for multi language
		var $lang;

		protected $db;
        
        function __construct($db=null) {
			if (!empty($db)) 
	        {
    	        $this->db = $db;
        	}
		}
		
		/**
		 * Inserting into database to add new industry
		 * @return result process in json encoded data
		 */
		private function doAdd(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $roles = Auth::getRoleID($this->db,$this->token);
                if ($roles < 2){
			        $newindustry = ucwords($this->industry);			
        			try {
		        		$this->db->beginTransaction();
				        $sql = "INSERT INTO customer_mas_industry (Industry) 
        					VALUES (:industry);";
		    			$stmt = $this->db->prepare($sql);
    					$stmt->bindParam(':industry', $newindustry, PDO::PARAM_STR);
    					if ($stmt->execute()) {
	    					$data = [
		    					'status' => 'success',
			    				'code' => 'RS101',
				    			'message' => CustomHandlers::getreSlimMessage('RS101',$this->lang)
					    	];	
    					} else {
	    					$data = [
		    					'status' => 'error',
			    				'code' => 'RS201',
				    			'message' => CustomHandlers::getreSlimMessage('RS201',$this->lang)
					    	];
    					}
	    			    $this->db->commit();
		    	    } catch (PDOException $e) {
    			    	$data = [
	    			    	'status' => 'error',
		    			    'code' => $e->getCode(),
    		    			'message' => $e->getMessage()
	    		    	];
		    		    $this->db->rollBack();
    			    }
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 'RS404',
                        'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
                    ];
                }
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 'RS401',
                    'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
                ];
            }
			
			return $data;
			$this->db = null;
        }
        

		/**
		 * Determine if industry name is already exist or not
		 * @return boolean true / false
		 */
		private function isIndustryExist(){
			$newindustry = ucwords($this->industry);
			$r = false;
			$sql = "SELECT a.Industry
				FROM customer_mas_industry a 
				WHERE a.Industry = :industry;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':industry', $newindustry, PDO::PARAM_STR);
			if ($stmt->execute()) {	
            	if ($stmt->rowCount() > 0){
	                $r = true;
    	        }          	   	
			} 		
			return $r;
			$this->db = null;
		}

		/** 
		 * Add new industry
		 * @return result process in json encoded data
		 */
		public function add(){
			if ($this->isIndustryExist() == false){
                $data = $this->doAdd();
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 'RS603',
                    'message' => CustomHandlers::getreSlimMessage('RS603',$this->lang)
                ];
            }
			
			return JSON::encode($data,true);
        }
        
        /** 
         * Update industry
         *
         * @return json encoded data
         */
		public function update(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
                $roles = Auth::getRoleID($this->db,$this->token);
                if ($roles < 3){
                    $newindustryid = Validation::integerOnly($this->industryid);
                    $newindustry = ucwords($this->industry);
		    		try{
                        $this->db->beginTransaction();
    
                        $sql = "UPDATE customer_mas_industry a SET a.Industry=:industry
                            WHERE a.IndustryID = :industryid;";
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(':industry', $newindustry, PDO::PARAM_STR);
                        $stmt->bindParam(':industryid', $newindustryid, PDO::PARAM_STR);
                        $stmt->execute();
                    
                        $this->db->commit();
                        
                        $data = [
                            'status' => 'success',
                            'code' => 'RS103',
                            'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
                        ];
                    } catch (PDOException $e){
                        $data = [
                            'status' => 'error',
                            'code' => $e->getCode(),
                            'message' => $e->getMessage()
                        ];
                        $this->db->rollBack();
					}
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 'RS404',
                        'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
                    ];
				}
			} else {
				$data = [
	    			'status' => 'error',
				    'code' => 'RS401',
					'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
    			];
			}
			
		    return JSON::encode($data,true);
    		$this->db = null;
		}

		/** 
         * Delete industry
         *
         * @return json encoded data
         */
		public function delete(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
                $roles = Auth::getRoleID($this->db,$this->token);
                if ($roles == '1'){
                    $newindustryid = Validation::integerOnly($this->industryid);
    				try{
                        $this->db->beginTransaction();
    
                        $sql = "DELETE FROM customer_mas_industry WHERE IndustryID = :industryid;";
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(':industryid', $newindustryid, PDO::PARAM_STR);
                        
						if ($stmt->execute()) {
    						$data = [
	    						'status' => 'success',
		    					'code' => 'RS104',
			    				'message' => CustomHandlers::getreSlimMessage('RS104',$this->lang)
				    		];	
					    } else {
    						$data = [
	    						'status' => 'error',
		    					'code' => 'RS204',
			    				'message' => CustomHandlers::getreSlimMessage('RS204',$this->lang)
				    		];
						}
						$this->db->commit();
                    } catch (PDOException $e){
                        $data = [
                            'status' => 'error',
                            'code' => $e->getCode(),
                            'message' => $e->getMessage()
                        ];
                        $this->db->rollBack();
                    }
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 'RS404',
                        'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
                    ];
                }
			} else {
				$data = [
	    			'status' => 'error',
				    'code' => 'RS401',
					'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
    			];
			}
		    return JSON::encode($data,true);
    		$this->db = null;
		}

		/** 
		 * Search all data industry paginated
		 * @return result process in json encoded data
		 */
		public function searchIndustryAsPagination() {
			if (Auth::validToken($this->db,$this->token)){
				$search = "%$this->search%";
				//count total row
				$sqlcountrow = "SELECT count(a.IndustryID) as TotalRow 
					from customer_mas_industry a
					where a.IndustryID like :search
                    or a.Industry like :search
                    order by a.Industry asc;";
				$stmt = $this->db->prepare($sqlcountrow);		
				$stmt->bindParam(':search', $search, PDO::PARAM_STR);
				
				if ($stmt->execute()) {	
    	    		if ($stmt->rowCount() > 0){
						$single = $stmt->fetch();
						
						// Paginate won't work if page and items per page is negative.
						// So make sure that page and items per page is always return minimum zero number.
						$newpage = Validation::integerOnly($this->page);
						$newitemsperpage = Validation::integerOnly($this->itemsPerPage);
						$limits = (((($newpage-1)*$newitemsperpage) <= 0)?0:(($newpage-1)*$newitemsperpage));
						$offsets = (($newitemsperpage <= 0)?0:$newitemsperpage);

						// Query Data
						$sql = "SELECT a.IndustryID,a.Industry 
							from customer_mas_industry a
							where a.IndustryID like :search
                            or a.Industry like :search
                            order by a.Industry asc LIMIT :limpage , :offpage;";
						$stmt2 = $this->db->prepare($sql);
						$stmt2->bindParam(':search', $search, PDO::PARAM_STR);
						$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
						$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						
						if ($stmt2->execute()){
							$pagination = new \classes\Pagination();
							$pagination->lang = $this->lang;
							$pagination->totalRow = $single['TotalRow'];
							$pagination->page = $this->page;
							$pagination->itemsPerPage = $this->itemsPerPage;
							$pagination->fetchAllAssoc = $stmt2->fetchAll(PDO::FETCH_ASSOC);
							$data = $pagination->toDataArray();
						} else {
							$data = [
        	    	    		'status' => 'error',
		        		    	'code' => 'RS202',
	    			    	    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
							];	
						}			
				    } else {
    	    			$data = [
        	    			'status' => 'error',
		    	    		'code' => 'RS601',
        			    	'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
		    	    }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        		    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}
				
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}

		/** 
		 * Search all data industry paginated public
		 * @return result process in json encoded data
		 */
		public function searchIndustryAsPaginationPublic() {
			$search = "%$this->search%";
			//count total row
			$sqlcountrow = "SELECT count(a.IndustryID) as TotalRow 
				from customer_mas_industry a
				where a.IndustryID like :search
                or a.Industry like :search
                order by a.Industry asc;";
			$stmt = $this->db->prepare($sqlcountrow);		
			$stmt->bindParam(':search', $search, PDO::PARAM_STR);
				
			if ($stmt->execute()) {	
    			if ($stmt->rowCount() > 0){
					$single = $stmt->fetch();
						
					// Paginate won't work if page and items per page is negative.
					// So make sure that page and items per page is always return minimum zero number.
					$newpage = Validation::integerOnly($this->page);
					$newitemsperpage = Validation::integerOnly($this->itemsPerPage);
					$limits = (((($newpage-1)*$newitemsperpage) <= 0)?0:(($newpage-1)*$newitemsperpage));
					$offsets = (($newitemsperpage <= 0)?0:$newitemsperpage);

					// Query Data
					$sql = "SELECT a.IndustryID,a.Industry 
						from customer_mas_industry a
						where a.IndustryID like :search
                        or a.Industry like :search
                        order by a.Industry asc LIMIT :limpage , :offpage;";
					$stmt2 = $this->db->prepare($sql);
					$stmt2->bindParam(':search', $search, PDO::PARAM_STR);
					$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
					$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						
					if ($stmt2->execute()){
						$pagination = new \classes\Pagination();
						$pagination->lang = $this->lang;
						$pagination->totalRow = $single['TotalRow'];
						$pagination->page = $this->page;
						$pagination->itemsPerPage = $this->itemsPerPage;
						$pagination->fetchAllAssoc = $stmt2->fetchAll(PDO::FETCH_ASSOC);
						$data = $pagination->toDataArray();
					} else {
						$data = [
    	    	    		'status' => 'error',
			    		    'code' => 'RS202',
	    			        'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
						];	
					}			
				} else {
    	    		$data = [
            			'status' => 'error',
	    	    		'code' => 'RS601',
    			    	'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
					];
		    	}          	   	
			} else {
				$data = [
        			'status' => 'error',
					'code' => 'RS202',
	        		'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}
        
        
        //OPTIONS=======================================


        /** 
		 * Get all data industry as a list option
		 * @return result process in json encoded data
		 */
		public function showOptionIndustry() {
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$sql = "SELECT a.IndustryID,a.Industry
					FROM customer_mas_industry a
					ORDER BY a.Industry ASC";
				$stmt = $this->db->prepare($sql);
				
				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
        	   		   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'results' => $results, 
    	    		        'status' => 'success', 
			           	    'code' => 'RS501',
        		        	'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
						];
			        } else {
        			    $data = [
            		    	'status' => 'error',
		        		    'code' => 'RS601',
        		    	    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
	    	        }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        		    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
        }

        /** 
		 * Get all data industry as a list option
		 * @return result process in json encoded data
		 */
		public function showOptionIndustryPublic() {
			$sql = "SELECT a.IndustryID,a.Industry
				FROM customer_mas_industry a
				ORDER BY a.Industry ASC";
			$stmt = $this->db->prepare($sql);
				
			if ($stmt->execute()) {	
    		    if ($stmt->rowCount() > 0){
    	   		   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$data = [
			   	        'results' => $results, 
    	    	        'status' => 'success', 
		           	    'code' => 'RS501',
    		        	'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
					];
			    } else {
        		    $data = [
        		    	'status' => 'error',
	        		    'code' => 'RS601',
    		    	    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
					];
	    	    }          	   	
			} else {
				$data = [
        			'status' => 'error',
					'code' => 'RS202',
	        		'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
				];
			}		
        
	    	return JSON::safeEncode($data,true);
	        $this->db= null;
        }
        
    }