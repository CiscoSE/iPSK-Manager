<?php
	
/**
 *@license
 *
 *Copyright 2021 Cisco Systems, Inc. or its affiliates
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 */
	

	
	$htmlbody = <<<HTML
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'authz'
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#mainContent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
</script>
HTML;

	$failure = false;

	if($sanitizedInput['authzPolicyName'] != "" && $sanitizedInput['termLengthSeconds'] < 157680000 && $sanitizedInput['ciscoAVPairPSK'] != ""){
		if(isset($sanitizedInput['pskLength'])){
			$sanitizedInput['pskLength'] = ($sanitizedInput['pskLength'] == 0) ? 8 : $sanitizedInput['pskLength'];
			$sanitizedInput['pskLength'] = ($sanitizedInput['pskLength'] < 8) ? 8 : $sanitizedInput['pskLength'];
			$sanitizedInput['pskLength'] = ($sanitizedInput['pskLength'] > 64) ? 64 : $sanitizedInput['pskLength'];
		}
		
		if($sanitizedInput['pskMode'] == 0){
			if(strlen($sanitizedInput['ciscoAVPairPSK']) > 7){
				$psk = $sanitizedInput['ciscoAVPairPSK'];
			}else{
				$failure = true;
			}
		}else{
			if($sanitizedInput['pskType'] == 0){
				$psk = "*devicerandom*";
			}else{
				$psk = "*userrandom*";
			}
		}
		
		if(!$failure){
			$ipskISEDB->updateAuthorizationTemplate($sanitizedInput['id'], $sanitizedInput['authzPolicyName'], $sanitizedInput['authzPolicyDescription'], $psk, $sanitizedInput['termLengthSeconds'], $sanitizedInput['pskLength'], $_SESSION['logonSID']);
		
			if($sanitizedInput['fullAuthZUpdate'] == true){
				$endpointsToUpdate = $ipskISEDB->getEndpointsByAuthZPolicy($sanitizedInput['id']);
				
				if($endpointsToUpdate){
					if($sanitizedInput['pskMode'] == 0){
						for($itemCount = 0; $itemCount < $endpointsToUpdate['count']; $itemCount++){
							$ipskISEDB->updateEndpointPsk($endpointsToUpdate[$itemCount]['id'], "psk=".$psk);
						}
					}else{
						for($itemCount = 0; $itemCount < $endpointsToUpdate['count']; $itemCount++){
							$ipskISEDB->updateEndpointPsk($endpointsToUpdate[$itemCount]['id'], "psk=".$ipskISEDB->generateRandomPassword($sanitizedInput['pskLength']));
						}
					}			
				}
			}
		}
	}

	print $htmlbody;

?>