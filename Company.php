<?php
namespace modules\enterprise_customer;
use \classes\Auth as Auth;
use \classes\JSON as JSON;
use \classes\Validation as Validation;
use \classes\CustomHandlers as CustomHandlers;
use \modules\enterprise\Util as Util;
use PDO;
	/**
     * A class for company management
     *
     * @package    modules/enterprise_customer
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reslim-modules-enterprise_customer/blob/master/LICENSE.md  MIT License
     */
	class Company {
        // model data company
        var $username,$branchid,$companyid,$company_name,$company_name_alias,$addres,$phone,$fax,$email,$discount,$tin,$pic,$tax,$admin_cost,
        $salesid,$industryid,$statusid,$created_by,$updated_by;

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
		 * Determine if company name is already exist or not
		 * @return boolean true / false
		 */
		private function isCompanyExist($branchid){
			$r = false;
			$sql = "SELECT a.Company_name
				FROM customer_company a 
				WHERE a.Company_name = :company_name AND a.BranchID=:branchid LIMIT 1;";
			$stmt = $this->db->prepare($sql);
            $stmt->bindParam(':company_name', $this->company_name, PDO::PARAM_STR);
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
                    if ($this->isCompanyExist($newbranchid) == false){
                        $newcompanyid = $this->generateID('CC');
                        try{
                            $this->db->beginTransaction();
                            $sql = "INSERT INTO customer_company 
                                (BranchID,CompanyID,Company_name,Company_name_alias,Address,Phone,Fax,Email,TIN,PIC,Discount,Tax,Admin_cost,
                                    IndustryID,SalesID,StatusID,Created_at,Created_by) 
                                VALUES
                                (:branchid,:companyid,:company_name,:company_name_alias,:address,:phone,:fax,:email,:tin,:pic,:discount,:tax,:admin_cost,
                                    :industryid,:salesid,'1',current_timestamp,:username);";
                        
                            $stmt = $this->db->prepare($sql);
                            $stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
                            $stmt->bindParam(':branchid', $newbranchid, PDO::PARAM_STR);
                            $stmt->bindParam(':companyid', $newcompanyid, PDO::PARAM_STR);
                            $stmt->bindParam(':company_name', $this->company_name, PDO::PARAM_STR);
                            $stmt->bindParam(':company_name_alias', $this->company_name_alias, PDO::PARAM_STR);
                            $stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
                            $stmt->bindParam(':phone', $this->phone, PDO::PARAM_STR);
                            $stmt->bindParam(':fax', $this->fax, PDO::PARAM_STR);
                            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
                            $stmt->bindParam(':tin', $this->tin, PDO::PARAM_STR);
                            $stmt->bindParam(':pic', $this->pic, PDO::PARAM_STR);
                            $stmt->bindParam(':discount', $this->discount, PDO::PARAM_STR);
                            $stmt->bindParam(':tax', $this->tax, PDO::PARAM_STR);
                            $stmt->bindParam(':admin_cost', $this->admin_cost, PDO::PARAM_STR);
                            $stmt->bindParam(':industryid', $this->industryid, PDO::PARAM_STR);
                            $stmt->bindParam(':salesid', $this->salesid, PDO::PARAM_STR);

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
                        $sql = "UPDATE customer_company a
                            SET
                                a.Company_name=:company_name,a.Company_name_alias=:company_name_alias,a.Address=:address,a.Phone=:phone,a.Fax=:fax,a.Email=:email,
                                a.TIN=:tin,a.PIC=:pic,a.Discount=:discount,a.Tax=:tax,a.Admin_cost=:admin_cost,
                                a.IndustryID=:industryid,a.SalesID=:salesid,a.StatusID=:statusid,a.Updated_by=:username
                            WHERE 
                                a.CompanyID=:companyid ".(($roles<3)?"":"AND a.BranchID = :branchid").";";
                        
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
                        if ($roles == 6 || $roles == 7) $stmt->bindParam(':branchid', $newbranchid, PDO::PARAM_STR);
                        $stmt->bindParam(':companyid', $this->companyid, PDO::PARAM_STR);
                        $stmt->bindParam(':company_name', $this->company_name, PDO::PARAM_STR);
                        $stmt->bindParam(':company_name_alias', $this->company_name_alias, PDO::PARAM_STR);
                        $stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
                        $stmt->bindParam(':phone', $this->phone, PDO::PARAM_STR);
                        $stmt->bindParam(':fax', $this->fax, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
                        $stmt->bindParam(':tin', $this->tin, PDO::PARAM_STR);
                        $stmt->bindParam(':pic', $this->pic, PDO::PARAM_STR);
                        $stmt->bindParam(':discount', $this->discount, PDO::PARAM_STR);
                        $stmt->bindParam(':tax', $this->tax, PDO::PARAM_STR);
                        $stmt->bindParam(':admin_cost', $this->admin_cost, PDO::PARAM_STR);
                        $stmt->bindParam(':industryid', $this->industryid, PDO::PARAM_STR);
                        $stmt->bindParam(':salesid', $this->salesid, PDO::PARAM_STR);
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
    
                        $sql = "DELETE FROM customer_company WHERE CompanyID = :companyid;";
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(':companyid', $this->companyid, PDO::PARAM_STR);
						
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
		 * Show data company only single detail for registered user
		 * @return result process in json encoded data
		 */
		public function showCompanyDetail(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$sql = "SELECT a.BranchID,a.CompanyID,a.Company_name,a.Company_name_alias,a.Address,a.Phone,a.Fax,a.Email,a.PIC,a.TIN,a.Discount,a.Tax,a.Admin_cost,
                        a.IndustryID,b.Industry,a.SalesID,a.StatusID,c.`Status`,a.Created_at,a.Created_by,a.Updated_at,a.Updated_by
                    FROM customer_company a 
                    INNER JOIN customer_mas_industry b ON a.IndustryID = b.IndustryID
                    INNER JOIN core_status c ON a.StatusID = c.StatusID
                    WHERE a.CompanyID = :companyid LIMIT 1;";
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':companyid', $this->companyid, PDO::PARAM_STR);

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
		 * Search all data company paginated
		 * @return result process in json encoded data
		 */
		public function searchCompanyAsPagination() {
			if (Auth::validToken($this->db,$this->token,$this->username)){
                $newbranchid = ""; 
                if(empty($this->branchid)){
                    $newbranchid = Util::getUserBranchID($this->db,$this->username);
                } else {
                    $newbranchid = $this->branchid;
                }
				$search = "%$this->search%";
				//count total row
				$sqlcountrow = "SELECT count(a.CompanyID) as TotalRow
                    FROM customer_company a 
                    INNER JOIN customer_mas_industry b ON a.IndustryID = b.IndustryID
                    INNER JOIN core_status c ON a.StatusID = c.StatusID
                    WHERE a.BranchID = :branchid 
                    AND (
                            a.CompanyID like :search OR a.Company_name like :search OR a.Company_name_alias like :search  OR a.Created_by like :search OR b.Industry like :search
                        )
                    ORDER BY a.Company_name ASC;";
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
						$sql = "SELECT a.BranchID,a.CompanyID,a.Company_name,a.Company_name_alias,a.Address,a.Phone,a.Fax,a.Email,a.PIC,a.TIN,a.Discount,a.Tax,a.Admin_cost,
                                a.IndustryID,b.Industry,a.SalesID,a.StatusID,c.`Status`,a.Created_at,a.Created_by,a.Updated_at,a.Updated_by
                            FROM customer_company a 
                            INNER JOIN customer_mas_industry b ON a.IndustryID = b.IndustryID
                            INNER JOIN core_status c ON a.StatusID = c.StatusID
                            WHERE a.BranchID = :branchid 
                            AND (
                                    a.CompanyID like :search OR a.Company_name like :search OR a.Company_name_alias like :search  OR a.Created_by like :search OR b.Industry like :search
                                )
                            ORDER BY a.Company_name ASC LIMIT :limpage , :offpage;";
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
