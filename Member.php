<?php
namespace modules\enterprise_customer;
use \classes\Auth as Auth;
use \classes\JSON as JSON;
use \classes\Validation as Validation;
use \classes\CustomHandlers as CustomHandlers;
use \modules\enterprise\Util as Util;
use PDO;
	/**
     * A class for member management
     *
     * @package    modules/enterprise_customer
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reslim-modules-enterprise_customer/blob/master/LICENSE.md  MIT License
     */
	class Member {
        // model data member
        var $username,$branchid,$memberid,$member_name,$member_name_alias,$addres,$phone,$fax,$email,$discount,$admin_cost,
        $statusid,$created_by,$updated_by;

        // for pagination
		var $page,$itemsPerPage;

		// for search
        var $firstdate,$lastdate,$search;
        
        // for multi language
        var $lang;

		protected $db;
        
        function __construct($db=null) {
			if (!empty($db)) 
	        {
    	        $this->db = $db;
        	}
        }

        private function generateID($prefix){
            return Auth::uniqidNumeric($prefix);
        }

        /**
		 * Determine if member name is already exist or not
		 * @return boolean true / false
		 */
		private function isMemberExist($branchid){
			$r = false;
			$sql = "SELECT a.Member_name
				FROM customer_member a 
				WHERE a.Member_name = :member_name AND a.BranchID=:branchid LIMIT 1;";
			$stmt = $this->db->prepare($sql);
            $stmt->bindParam(':member_name', $this->member_name, PDO::PARAM_STR);
            $stmt->bindParam(':branchid', $branchid, PDO::PARAM_STR);
			if ($stmt->execute()) {	
            	if ($stmt->rowCount() > 0){
	                $r = true;
    	        }          	   	
			} 		
			return $r;
			$this->db = null;
		}

        public function add(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                if (Util::isUserActive($this->db,$this->username)){
                    $newusername = strtolower($this->username);
                    $newbranchid = strtolower(Util::getUserBranchID($this->db,$newusername));
                    if ($this->isMemberExist($newbranchid) == false){
                        $newmemberid = $this->generateID('MM');
                        try{
                            $this->db->beginTransaction();
                            $sql = "INSERT INTO customer_member 
                                (BranchID,MemberID,Member_name,Member_name_alias,Address,Phone,Fax,Email,Discount,Admin_cost,
                                    StatusID,Created_at,Created_by) 
                                VALUES
                                (:branchid,:memberid,:member_name,:member_name_alias,:address,:phone,:fax,:email,:discount,:admin_cost,
                                    '1',current_timestamp,:username);";
                        
                            $stmt = $this->db->prepare($sql);
                            $stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
                            $stmt->bindParam(':branchid', $newbranchid, PDO::PARAM_STR);
                            $stmt->bindParam(':memberid', $newmemberid, PDO::PARAM_STR);
                            $stmt->bindParam(':member_name', $this->member_name, PDO::PARAM_STR);
                            $stmt->bindParam(':member_name_alias', $this->member_name_alias, PDO::PARAM_STR);
                            $stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
                            $stmt->bindParam(':phone', $this->phone, PDO::PARAM_STR);
                            $stmt->bindParam(':fax', $this->fax, PDO::PARAM_STR);
                            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
                            $stmt->bindParam(':discount', $this->discount, PDO::PARAM_STR);
                            $stmt->bindParam(':admin_cost', $this->admin_cost, PDO::PARAM_STR);

                            if ($stmt->execute()) {
                                if ($stmt->rowCount() > 0){
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
                            'code' => 'RS603',
                            'message' => CustomHandlers::getreSlimMessage('RS603',$this->lang)
                        ];
                    }
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 'RS906',
                        'message' => CustomHandlers::getreSlimMessage('RS906',$this->lang)
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

        public function update(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $roles = Auth::getRoleID($this->db,$this->token);
                if (Util::isUserActive($this->db,$this->username)){
                    $newusername = strtolower($this->username);
                    $newbranchid = strtolower(Util::getUserBranchID($this->db,$newusername));
                    try{
                        $this->db->beginTransaction();
                        $sql = "UPDATE customer_member a
                            SET
                                a.Member_name=:member_name,a.Member_name_alias=:member_name_alias,a.Address=:address,a.Phone=:phone,a.Fax=:fax,a.Email=:email,
                                a.Discount=:discount,a.Admin_cost=:admin_cost,
                                a.StatusID=:statusid,a.Updated_by=:username
                            WHERE 
                                a.MemberID=:memberid ".(($roles<3)?"":"AND a.BranchID = :branchid").";";
                        
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
                        if ($roles == 6 || $roles == 7) $stmt->bindParam(':branchid', $newbranchid, PDO::PARAM_STR);
                        $stmt->bindParam(':memberid', $this->memberid, PDO::PARAM_STR);
                        $stmt->bindParam(':member_name', $this->member_name, PDO::PARAM_STR);
                        $stmt->bindParam(':member_name_alias', $this->member_name_alias, PDO::PARAM_STR);
                        $stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
                        $stmt->bindParam(':phone', $this->phone, PDO::PARAM_STR);
                        $stmt->bindParam(':fax', $this->fax, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
                        $stmt->bindParam(':discount', $this->discount, PDO::PARAM_STR);
                        $stmt->bindParam(':admin_cost', $this->admin_cost, PDO::PARAM_STR);
                        $stmt->bindParam(':statusid', $this->statusid, PDO::PARAM_STR);

                        if ($stmt->execute()) {
                            $data = [
                                'status' => 'success',
                                'code' => 'RS103',
                                'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
                            ];
    					} else {
	    					$data = [
		    					'status' => 'error',
			    				'code' => 'RS203',
				    			'message' => CustomHandlers::getreSlimMessage('RS203',$this->lang)
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
                        'code' => 'RS906',
                        'message' => CustomHandlers::getreSlimMessage('RS906',$this->lang)
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

        public function delete(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $roles = Auth::getRoleID($this->db,$this->token);
                if ($roles == '1'){
    				try{
                        $this->db->beginTransaction();
    
                        $sql = "DELETE FROM customer_member WHERE MemberID = :memberid;";
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(':memberid', $this->memberid, PDO::PARAM_STR);
						
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
		 * Show data member only single detail for registered user
		 * @return result process in json encoded data
		 */
		public function showMemberDetail(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$sql = "SELECT a.BranchID,a.Member_name,a.Member_name_alias,a.Address,a.Phone,a.Fax,a.Email,a.Discount,a.Admin_cost,
                        a.StatusID,b.`Status`,a.Created_at,a.Created_by,a.Updated_at,a.Updated_by
                    FROM customer_member a 
                    INNER JOIN core_status b ON a.StatusID = b.StatusID
                    WHERE a.MemberID = :memberid LIMIT 1;";
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':memberid', $this->memberid, PDO::PARAM_STR);

				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'result' => $results, 
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
		 * Search all data member paginated
		 * @return result process in json encoded data
		 */
		public function searchMemberAsPagination() {
			if (Auth::validToken($this->db,$this->token,$this->username)){
                $newbranchid = ""; 
                if(empty($this->branchid)){
                    $newbranchid = Util::getUserBranchID($this->db,$this->username);
                } else {
                    $newbranchid = $this->branchid;
                }
				$search = "%$this->search%";
				//count total row
				$sqlcountrow = "SELECT count(a.MemberID) as TotalRow
                    FROM customer_member a 
                    INNER JOIN core_status b ON a.StatusID = b.StatusID
                    WHERE a.BranchID = :branchid 
                    AND (
                            a.MemberID like :search OR a.Member_name like :search OR a.Member_name_alias like :search  OR a.Created_by like :search
                        )
                    ORDER BY a.Member_name ASC;";
				$stmt = $this->db->prepare($sqlcountrow);		
                $stmt->bindParam(':search', $search, PDO::PARAM_STR);
                $stmt->bindParam(':branchid', $newbranchid, PDO::PARAM_STR);
				
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
						$sql = "SELECT a.BranchID,a.MemberID,a.Member_name,a.Member_name_alias,a.Address,a.Phone,a.Fax,a.Email,a.Discount,a.Admin_cost,
                                a.StatusID,b.`Status`,a.Created_at,a.Created_by,a.Updated_at,a.Updated_by
                            FROM customer_member a 
                            INNER JOIN core_status b ON a.StatusID = b.StatusID
                            WHERE a.BranchID = :branchid 
                            AND (
                                    a.MemberID like :search OR a.Member_name like :search OR a.Member_name_alias like :search  OR a.Created_by like :search
                                )
                            ORDER BY a.Member_name ASC LIMIT :limpage , :offpage;";
						$stmt2 = $this->db->prepare($sql);
                        $stmt2->bindParam(':search', $search, PDO::PARAM_STR);
                        $stmt2->bindParam(':branchid', $newbranchid, PDO::PARAM_STR);
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

    }
