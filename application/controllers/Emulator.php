<?php
	/*
		https://www.codexworld.com/codeigniter-rest-api-web-services/

	*/
   require APPPATH . '/libraries/REST_Controller.php';
   use Restserver\Libraries\REST_Controller;
     
class Emulator extends REST_Controller {
    
	  /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function __construct() {
       parent::__construct();
       $this->load->database();
    }
       
    
	public function Log_post()
	{
		try{

			//$input = $this->input->post();

			$data = json_decode(file_get_contents('php://input'), true);
			
			log_message('INFO',print_r($data, TRUE) );

			$this->db->insert('eventlogger',$data);
		
			$this->response([
                'status' => TRUE,
                'message' => 'Emulator created successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}
		
    }	


	public function Login_post()
	{
		try{

			//$input = $this->input->post();

			$data = json_decode(file_get_contents('php://input'), true);
			
			log_message('INFO',print_r($data, TRUE) );

			$this->db->insert('eventlogger',$data);
		
			$this->response([
                'status' => TRUE,
                'message' => 'Emulator created successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}
		
    }	


	public function CheckAuthorization_get($EmployeeId){
		#$result =  $this->db->join('role urr','urr.id = ur.RoleId')->get_where('users ur', array('ur.Employee_Id'=>$EmployeeId))->result_array();
		$result = $this->db->select("ur.*,urr.role_name,	urr.super_user,	urr.local_admin,urr.creator, urr.viewer")->join('role urr','urr.id = ur.RoleId')->get_where('users ur', array('ur.Employee_Id'=>$EmployeeId))->result_array();
		$this->response($result, REST_Controller::HTTP_OK);		
	}

	public function CheckEmailAuthorization_get($EmailId){
		$result = $this->db->select("ur.*,urr.role_name,	urr.super_user,	urr.local_admin,urr.creator, urr.viewer")->join('role urr','urr.id = ur.RoleId')->get_where('users ur', array('ur.email'=>$EmailId))->result_array();
		$this->response($result, REST_Controller::HTTP_OK);		
	}
	
	public function CheckValidToken_get($token_payload){
		$result =  $this->db->get_where('used_token', array('token'=>$token_payload))->result_array();
		$this->response($result, REST_Controller::HTTP_OK);		
	}

	public function UpdateUser_post(){
		try{
			$data = json_decode(file_get_contents('php://input'), true);
			$Id = $data["id"];
			$RoleId = $data["RoleId"];
			$data = array(
				"RoleId"=>$RoleId,
			);
			$this->db->where("id = '$Id'")->update('users',$data); 	 
			$this->response([
				'status' => TRUE,
				'message' => 'User updated successfully.'
			], REST_Controller::HTTP_CREATED);			
		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}	
	}

	public function CreateUser_post(){
		try{

			//$input = $this->input->post();
			$data = json_decode(file_get_contents('php://input'), true);
			$EmployeeId = $data["Employee_Id"];
			$RoleId = $data["RoleId"];

			$user_epmsInfo =  $this->db->get_where('epms_roster', array('EmployeeID'=>$EmployeeId))->result_array();
			
			if (count($user_epmsInfo) != 0)
			{
				$data = array(
					"Employee_Id"=>$EmployeeId,  
					"fname"=>$user_epmsInfo[0]["FullName"],  
					"email"=>$user_epmsInfo[0]["Email"],  
					"RoleId"=>$RoleId
				);

				$user_Info =  $this->db->get_where('users', array('Employee_Id'=>$EmployeeId))->result_array();
				if (count($user_Info) == 0){

					$this->db->insert('users',$data);

					$result =  $this->db->get_where('users', array('Employee_Id'=>$EmployeeId))->result_array();

					$newUserId = $result[0]["id"];
				
					$this->response([
						'status' => TRUE,
						'newUserId' => $newUserId,
						"EmployeeName"=>$user_epmsInfo[0]["FullName"], 
						'message' => 'User created successfully.'
					], REST_Controller::HTTP_CREATED);
				}
				else{
						$this->response([
							'status' => FALSE,
							'EmployeeId ' => $EmployeeId,
							"EmployeeName"=>$user_epmsInfo[0]["FullName"],  
							'message' => 'User is already provisioned.'
						], REST_Controller::HTTP_BAD_REQUEST);

				}		
			}
			else
			{
				$this->response([
					'status' => FALSE,
					'EmployeeId ' => $EmployeeId,
					"EmployeeName"=>"",  
					'message' => 'User not found in the EPMS roster.'
				], REST_Controller::HTTP_BAD_REQUEST);

			}

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}		
	}


	public function CreateUserCampaignMapping_post()
	{
		try{

			//$input = $this->input->post();
			$data = json_decode(file_get_contents('php://input'), true);
			$UserId = $data["UserId"];
			$CampaignId = $data["CampaignId"];

			$user_campaign_Info =  $this->db->get_where('usercampaign', array('UserId'=>$UserId, 'CampaignId'=>$CampaignId))->result_array();
			if (count($user_campaign_Info) == 0){
				$this->db->insert('usercampaign',$data);
			
				$this->response([
					'status' => TRUE,
					'message' => 'Campaign mapping created successfully.'
				], REST_Controller::HTTP_CREATED);
			}
			else
			{
				$this->response([
					'status' => FALSE,
					'message' => 'Campaign mapping already exist.'
				], REST_Controller::HTTP_CREATED);

			}

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function CreateUserLobMapping_post()
	{
		try{

			//$input = $this->input->post();
			$data = json_decode(file_get_contents('php://input'), true);
			$userId = $data["userId"];
			$lobId = $data["lobId"];

			$user_lob_Info =  $this->db->get_where('userlob', array('userId'=>$userId, 'lobId'=>$lobId))->result_array();
			if (count($user_lob_Info) == 0){
				$this->db->insert('userlob',$data);		
				$this->response([
					'status' => TRUE,
					'message' => 'LOB mapping created successfully.'
				], REST_Controller::HTTP_CREATED);
			}
			else
			{
				$this->response([
					'status' => FALSE,
					'message' => 'LOB mapping already exist.'
				], REST_Controller::HTTP_CREATED);

			}

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}
	}


	public function Create_Simulation_post()
	{
		try{

			//$input = $this->input->post();
			$data = json_decode(file_get_contents('php://input'), true);
			
			$this->db->insert('emulators',$data);
		
			$this->response([
                'status' => TRUE,
                'message' => 'Emulator created successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

    public function Create_SimulatorStep_post(){
		try{

			//$input = $this->input->post();
			$data = json_decode(file_get_contents('php://input'), true);
			
			$this->db->insert('images',$data);
		
			$this->response([
                'status' => TRUE,
                'message' => 'Simulation step created successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}
        
        
    }
 
    public function Create_SimulatorStepHotspot_post(){
        try{

			//$input = $this->input->post();
			$data = json_decode(file_get_contents('php://input'), true);
			
			$this->db->insert('shapes',$data);
		
			$this->response([
                'status' => TRUE,
                'message' => 'Simulation hotstop created successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}
    }    


    public function UpdateSimulatorStepHotspot_post(
        $ShapeId
    ){
		try{

            $data = json_decode(file_get_contents('php://input'), true);	

            $this->db->where("ShapeId = '$ShapeId'")->update('shapes',$data); 	 
            
			$this->response([
                'status' => TRUE,
                'message' => 'Simulation hotspot updated successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}  			

    }			



    public function Update_SimulatorStep_post($ImageId){
		try{

            $data = json_decode(file_get_contents('php://input'), true);	

            $this->db->where("ImageId = '$ImageId'")->update('images',$data);  
            
			$this->response([
                'status' => TRUE,
                'message' => 'Simulation step updated successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}  			
    }		    
    
    public function Update_Simulation_post($EmulatorId){
		try{

            $data = json_decode(file_get_contents('php://input'), true);	

            $this->db->where('EmulatorId', $EmulatorId)->update('emulators',$data);  
            
			$this->response([
                'status' => TRUE,
                'message' => 'Emulator updated successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}

    }    


    public function SimulatorStep_delete($SimulatorStepId){
        try{

            $this->db->where('ImageId', $SimulatorStepId)->delete('images');  	
            $this->db->where('RedirectScreenTo', $SimulatorStepId)->delete('shapes');  	
                
			$this->response([
                'status' => TRUE,
                'message' => 'Simulator step deleted successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}

    }

    public function SimulatorImageShape_delete($EmulatorId,$SimulatorImageStepId){
        try{

            $this->db->where('ShapeId', $SimulatorImageStepId)->delete('shapes');  	
                
			$this->response([
                'status' => TRUE,
                'message' => 'Simulator hotspot deleted successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}        
    }	    


	public function SimulationInfo_get($EmulatorId)
	{
		try{
			$result =  $this->db->select('*')->from('emulators')->where("EmulatorId = '$EmulatorId'")->get()->result_array();		
			#$result = json_encode($result);
			$this->response($result, REST_Controller::HTTP_CREATED);
		}
		catch(Exception $ex){
			$this->response(['Emulator Id does not exists.'], REST_Controller::HTTP_NOT_FOUND);
		}
		
	}	

	
	public function UserProfileGrant_get($UserId){
		$result =  $this->db->select("rpg.*")->join('role urr','urr.id = ur.RoleId')->join("role_profile_grant rpg","urr.id = rpg.roleId")->get_where('users ur', array('ur.Id'=>$UserId))->result_array();		

        $count = count($result); //counting result from query
		if($count == 0){
			$this->response([
				"message"=>"You are not an authorized user, please contact your administrator for access."
			], REST_Controller::HTTP_NOT_FOUND);
			die();
		}

		$this->response($result, REST_Controller::HTTP_OK);
	}

	public function UserCampaigns_get($UserId){
		$result =  $this->db->join('role urr','urr.id = ur.RoleId')->get_where('users ur', array('ur.Id'=>$UserId))->result_array();		

        $count = count($result); //counting result from query
		if($count == 0){
			$this->response([
				"message"=>"You are not an authorized user, please contact your administrator for access."
			], REST_Controller::HTTP_NOT_FOUND);
			die();
		}

		#print_r($result);
        if ($result[0]['super_user'] == 1) {
			$result =  $this->db->select('*')->from('campaign')->get()->result_array();
		}
		else{

			$result =  $this->db->query("SELECT DISTINCT cm.campaignid as CampaignId,
														cm.campaignname as CampaignName
											FROM   campaign cm
											JOIN lob lb
												ON cm.campaignid = lb.campaignid
											JOIN userlob url
												ON lb.lobid = url.lobid
											JOIN users ur
												ON ur.id = url.userid
											WHERE  ur.id = '$UserId'")->result_array();

		}
		$this->response($result, REST_Controller::HTTP_OK);
	}

	public function UserCampaignMapping_delete($UserId){
        try{

            $this->db->where('UserId', $UserId)->delete('usercampaign');  	
                
			$this->response([
                'status' => TRUE,
                'message' => 'User campaign mapping delete successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}

	}


	public function UserLOBs_get($UserId){
		$result =  $this->db->join('role urr','urr.id = ur.RoleId')->get_where('users ur', array('ur.Id'=>$UserId))->result_array();		

        $count = count($result); //counting result from query
		if($count == 0){
			$this->response([
				"message"=>"You are not an authorized user, please contact your administrator for access."
			], REST_Controller::HTTP_NOT_FOUND);
			die();
		}

		#print_r($result);
        if ($result[0]['super_user'] == 1) {
			$result =  $this->db->select("lb.lobid,
											cm.CampaignId,
											lb.lobname,
											lb.lobshortname,
											CONCAT(cm.CampaignName,' - ', lb.lobname) as DisplayLOBName"
										)->from('lob lb')->join("campaign cm","cm.CampaignId = lb.campaignId")->get()->result_array();
		}
		else{

			$result =  $this->db->query("SELECT *
			FROM   (SELECT lb.lobid,
						   lb.lobname,
						   lb.lobshortname,
						   lb.campaignId as CampaignId,
						   CONCAT(cm.CampaignName,' - ', lb.lobname) as DisplayLOBName
					FROM   userlob url
						   JOIN lob lb
							 ON url.lobid = lb.lobid
						   JOIN campaign cm
						     ON cm.CampaignId = lb.campaignId
					WHERE  url.userid = '$UserId'
						   AND lb.lobname <> 'All'
					UNION
					SELECT lb.lobid,
						   lb.lobname,
						   lb.lobshortname,
						   lb.campaignId,
						   CONCAT(cm.CampaignName,' - ', lb.lobname) as DisplayLOBName
					FROM   lob lb
						   JOIN campaign cm
						     ON cm.CampaignId = lb.campaignId
					WHERE  cm.campaignid IN (SELECT campaignid
										  FROM   userlob url
												 JOIN lob lb
												   ON url.lobid = lb.lobid
										  WHERE  url.userid = '$UserId'
												 AND lobname = 'All')) finally
					ORDER  BY campaignid,
							lobid 
					  ")->result_array();

		}
		$this->response($result, REST_Controller::HTTP_OK);
	}

	public function UserLobMapping_delete($UserId){
        try{

            $this->db->where('userId', $UserId)->delete('userlob');  	
                
			$this->response([
                'status' => TRUE,
                'message' => 'User Lob mapping delete successfully.'
            ], REST_Controller::HTTP_CREATED);

		}
		catch(Exception $ex){
			$this->response([$ex->getMessage()], REST_Controller::HTTP_BAD_REQUEST);
		}

	}

	public function NonProvisionUsers_get($UserId,$limit,$offset){
		$result =  $this->db->join('role urr','urr.id = ur.RoleId')->get_where('users ur', array('ur.Id'=>$UserId))->result_array();		

        $count = count($result); //counting result from query
		if($count == 0){
			$this->response([
				"message"=>"You are not an authorized user, please contact your administrator for access."
			], REST_Controller::HTTP_NOT_FOUND);
			die();
		}

		$result =  $this->db->query("SELECT EmployeeID, concat(EmployeeID, ' - ', FullName) AS DisplayEmpName from epms_roster 
		where EmployeeID NOT in (Select Employee_Id from users where users.Employee_Id = epms_roster.EmployeeID) 
			LIMIT $limit  OFFSET  $offset")->result_array();

		$this->response($result, REST_Controller::HTTP_OK);
	}

	public function ManageUsers_get($UserId,$limit,$offset){
		$result =  $this->db->join('role urr','urr.id = ur.RoleId')->get_where('users ur', array('ur.Id'=>$UserId))->result_array();		

        $count = count($result); //counting result from query
		if($count == 0){
			$this->response([
				"message"=>"You are not an authorized user, please contact your administrator for access."
			], REST_Controller::HTTP_NOT_FOUND);
			die();
		}

		#print_r($result);
        if ($result[0]['super_user'] == 1) {
			$result =  $this->db->query("SELECT   id,
													email,
													fname,
													role_name,
													employee_id as Employee_Id,
													group_concat(DISTINCT CampaignId order BY CampaignId separator ',') AS campaignId,
													group_concat(DISTINCT campaignname order BY campaignname separator ', ') AS campaign,
													group_concat(DISTINCT lobId order BY lobId separator ',') AS lobId,
													group_concat(DISTINCT displayname ORDER BY displayname separator ', ')   AS lobname
										FROM     (
														SELECT ur.*,
																rl.role_name,
																cm.CampaignId,
																cm.campaignname,
																lb.lobId,
																lb.lobname,
																		concat(cm.campaignname, ' - ',lb.lobname) AS displayname
														FROM   users ur
														JOIN   role rl
														ON     ur.roleid = rl.id
														JOIN   usercampaign ucm
														ON     ur.id = ucm.userid
														JOIN   campaign cm
														ON     ucm.campaignid = cm.campaignid
														JOIN   userlob url
														ON     ur.id = url.userid
														JOIN   lob lb
														ON     url.lobid = lb.lobid
														AND    cm.campaignid = lb.campaignid where ur.id != '$UserId' ) finally 
														group by id,
																email,
																fname,
																role_name,
																employee_id
														LIMIT $limit  OFFSET  $offset
				   ")->result_array();
		}
		else{

			$result =  $this->db->query("SELECT   id,
													email,
													fname,
													role_name,
													employee_id as Employee_Id,
													group_concat(DISTINCT CampaignId order BY CampaignId separator ',') AS campaignId,
													group_concat(DISTINCT campaignname order BY campaignname separator ', ') AS campaign,
													group_concat(DISTINCT lobId order BY lobId separator ',') AS lobId,
													group_concat(DISTINCT displayname ORDER BY displayname separator ', ')   AS lobname
										FROM     (
														SELECT ur.*,
																rl.role_name,
																cm.CampaignId,
																cm.campaignname,
																lb.lobId,
																lb.lobname,
																		concat(cm.campaignname, ' - ',lb.lobname) AS displayname
														FROM   users ur
														JOIN   role rl
														ON     ur.roleid = rl.id
														JOIN   usercampaign ucm
														ON     ur.id = ucm.userid
														JOIN   campaign cm
														ON     ucm.campaignid = cm.campaignid
														JOIN   userlob url
														ON     ur.id = url.userid
														JOIN   lob lb
														ON     url.lobid = lb.lobid
														AND    cm.campaignid = lb.campaignid where ur.id != '$UserId' 
														AND    lb.lobId in (SELECT lobid
																			FROM   (SELECT lb.lobid
																					FROM   userlob url
																							JOIN lob lb
																							ON url.lobid = lb.lobid
																					WHERE  url.userid = '$UserId'
																							AND lb.lobname <> 'All'
																					UNION
																					SELECT lb.lobid
																					FROM   lob lb
																					WHERE  lb.campaignid IN (SELECT campaignid
																											FROM   userlob url
																													JOIN lob lb
																													ON
																											url.lobid = lb.lobid
																											WHERE  url.userid = '$UserId'
																													AND lobname = 'All'))
																					finally)
											) finally 
											group by id,
														email,
														fname,
														role_name,
														employee_id
											LIMIT $limit  OFFSET  $offset
					  ")->result_array();

		}
		$this->response($result, REST_Controller::HTTP_OK);
	}


	public function ManageUsersUsersByFilter_get($UserId, $Campaign, $Lob, $Profile, $EmployeeId,$limit,$offset){	
		$result =  $this->db->join('role urr','urr.id = ur.RoleId')->get_where('users ur', array('ur.Id'=>$UserId))->result_array();		


		$Campaign = ($Campaign == "All"? "%": urldecode($Campaign));
		$Lob = ($Lob == "All"? "%": urldecode($Lob));
		$Profile = ($Profile == "All"? "%": urldecode($Profile));
		$EmployeeId = ($EmployeeId == "All"? "%": urldecode($EmployeeId));
		
        $count = count($result); //counting result from query
		if($count == 0){
			$this->response([
				"message"=>"You are not an authorized user, please contact your administrator for access."
			], REST_Controller::HTTP_NOT_FOUND);
			die();
		}

		#print_r($result);
        if ($result[0]['super_user'] == 1) {
			$result =  $this->db->query("SELECT
											*
											FROM 
											(
												SELECT   id,
														email,
														fname,
														role_name,
														employee_id as Employee_Id,
														group_concat(DISTINCT CampaignId order BY CampaignId separator ',') AS campaignId,
														group_concat(DISTINCT campaignname order BY campaignname separator ', ') AS campaign,
														group_concat(DISTINCT lobId order BY lobId separator ',') AS lobId,
														group_concat(DISTINCT displayname ORDER BY displayname separator ', ')   AS lobname
												FROM     (
															SELECT ur.*,
																	rl.role_name,
																	cm.CampaignId,
																	cm.campaignname,
																	lb.lobId,
																	lb.lobname,
																			concat(cm.campaignname, ' - ',lb.lobname) AS displayname
															FROM   users ur
															JOIN   role rl
															ON     ur.roleid = rl.id
															JOIN   usercampaign ucm
															ON     ur.id = ucm.userid
															JOIN   campaign cm
															ON     ucm.campaignid = cm.campaignid
															JOIN   userlob url
															ON     ur.id = url.userid
															JOIN   lob lb
															ON     url.lobid = lb.lobid
															AND    cm.campaignid = lb.campaignid where ur.id != '$UserId' ) finally 
															
															group by id,
																	email,
																	fname,
																	role_name,
																	employee_id
											) finalView                    
											Where role_name like '%$Profile%'
											AND	campaign Like '%$Campaign%'
											AND	lobname Like '%$Lob%'	
											AND	Employee_Id Like '%$EmployeeId%'	
											LIMIT $limit  OFFSET  $offset		
				   ")->result_array();
		}
		else{

			$result =  $this->db->query("Select * from (
											SELECT   id,
													email,
													fname,
													role_name,
													employee_id as Employee_Id,
													group_concat(DISTINCT CampaignId order BY CampaignId separator ',') AS campaignId,
													group_concat(DISTINCT campaignname order BY campaignname separator ', ') AS campaign,
													group_concat(DISTINCT lobId order BY lobId separator ',') AS lobId,
													group_concat(DISTINCT displayname ORDER BY displayname separator ', ')   AS lobname
									FROM     (
														SELECT ur.*,
																rl.role_name,
																cm.CampaignId,
																cm.campaignname,
																lb.lobId,
																lb.lobname,
																		concat(cm.campaignname, ' - ',lb.lobname) AS displayname
														FROM   users ur
														JOIN   role rl
														ON     ur.roleid = rl.id
														JOIN   usercampaign ucm
														ON     ur.id = ucm.userid
														JOIN   campaign cm
														ON     ucm.campaignid = cm.campaignid
														JOIN   userlob url
														ON     ur.id = url.userid
														JOIN   lob lb
														ON     url.lobid = lb.lobid
														AND    cm.campaignid = lb.campaignid where ur.id != '$UserId' 
														AND    lb.lobId in (SELECT lobid
																			FROM   (SELECT lb.lobid
																					FROM   userlob url
																							JOIN lob lb
																							ON url.lobid = lb.lobid
																					WHERE  url.userid = '$UserId' 
																							AND lb.lobname <> 'All'
																					UNION
																					SELECT lb.lobid
																					FROM   lob lb
																					WHERE  lb.campaignid IN (SELECT campaignid
																											FROM   userlob url
																													JOIN lob lb
																													ON
																											url.lobid = lb.lobid
																											WHERE  url.userid = '$UserId' 
																													AND lobname = 'All'))
																					finally)
												) finally 
										Group by id, email, fname, role_name, employee_id
										)finalView
										Where
										role_name like '%$Profile%'
										AND	campaign Like '%$Campaign%'
										AND	lobname Like '%$Lob%'
										AND	Employee_Id Like '%$EmployeeId%'	

										LIMIT $limit  OFFSET  $offset
					  ")->result_array();

		}
		$this->response($result, REST_Controller::HTTP_OK);

	}
	public function Simulators_get($UserId,$query,$limit, $offset){			


			#$result =  $this->db->select('EmulatorId, EmulatorName')->from('emulators')->where("EmulatorName LIKE '%$query%'")->where("EmulatorCampaignId in (Select campaignId from usercampaign where UserId = '$UserId') and EmulatorLOBId in (Select lobId from userlob where userId = '$UserId')")->get()->result_array();
			$query = urldecode($query);
			$result =  $this->db->query("
										SELECT ems.EmulatorId, ems.EmulatorName FROM emulators ems join campaign cm on
										ems.EmulatorCampaignId = cm.CampaignId
										where
										ems.EmulatorName LIKE '%$query%'
										And EmulatorCampaignId
										in (
										SELECT CampaignId from usercampaign where userId = '$UserId' 
										)
										And EmulatorLOBId
										REGEXP (
											SELECT group_concat(Case WHEN lobname = 'All' THEN '.*' else ul.lobId end SEPARATOR '|') from userlob ul join lob l on ul.lobId = l.lobId where userId = '$UserId' and l.CampaignId = cm.CampaignId
										) UNION SELECT EmulatorId, EmulatorName FROM emulators where EmulatorName LIKE '%$query%' and Emulatorprovision='1'
										LIMIT $limit  OFFSET  $offset						
			
			")->result_array();
			
			$this->response($result, REST_Controller::HTTP_OK);
	}		
	
    public function SimulationSteps_get($EmulatorId){
        try{
			$result =  $this->db->select('*')->from('images')->where("EmulatorId = '$EmulatorId'  Order by ImageOrder" )->get()->result_array();		
			#$result = json_encode($result);
			$this->response($result, REST_Controller::HTTP_CREATED);
		}
		catch(Exception $ex){
			$this->response(['Emulator Id does not exists.'], REST_Controller::HTTP_NOT_FOUND);
		}
    }
    	
    public function SimulatorScreenInformation_get($EmulatorId, $ImageId){
        try{
			$result =  $this->db->select('*')->from('images')->where("EmulatorId = '$EmulatorId' and ImageId='$ImageId'")->get()->result_array();	
			#$result = json_encode($result);
			$this->response($result, REST_Controller::HTTP_CREATED);
		}
		catch(Exception $ex){
			$this->response(['Emulator Id does not exists.'], REST_Controller::HTTP_NOT_FOUND);
		}        
        
    }

    public function SimulatorScreenCoordInformation_get($ImageId){
        try{
			$result = $this->db->select('shapes.*, shapetype.ShapeType')->from('shapes')->join('shapetype', 'shapes.ShapeTypeId = shapetype.ShapeTypeId')->where("ImageId = '$ImageId'")->get()->result_array();	
			#$result = json_encode($result);
			$this->response($result, REST_Controller::HTTP_CREATED);
		}
		catch(Exception $ex){
			$this->response(['Image Id does not exists.'], REST_Controller::HTTP_NOT_FOUND);
		}                
    }

    

}