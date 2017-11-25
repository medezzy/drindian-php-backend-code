<?php
require 'vendor/autoload.php';

$usersDBName = "medical_users";
$mediDBName = "medicines_db";
$billsDBName = "bills_db";
$stocksDBName = "stocks_db";
$dbUser = "androidApp";
$dbPwd = "androidDoctor1234"; 


switch($_GET["type"]){
    case "check_user":
        
        //$dbUser = "androidApp";
        //$dbPwd = "androidDoctor1234";

        try {
            
            $username = utf8_encode($_GET['un']);
            $password = utf8_encode($_GET['pass']);
            $deviceId = utf8_encode($_GET['deviceId']);
            $role = utf8_encode($_GET['role']);
            
            $username = stripslashes($username);
            $password = stripslashes($password);
            $deviceId = stripslashes($deviceId);
            $role = stripslashes($role);
            
            $client = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$usersDBName);
            $medical_users_db = $client->medical_users;
            $users_collection = $medical_users_db->userscol;
            
            if($role == "admin"){
                $check_arr = array();
                $check_arr["username"] = $username;
                $check_arr["password"] = $password;
                                     
                $records = $users_collection->findOne($check_arr);
                
                if($records){
                    
                    login_success($records,"Login Successful",$role);
                    
                    //$devices_arr = ($records["devices_arr"] == null ? array() : $records["devices_arr"]);
                    //$MAX_USERS = 10;
                    //
                    ////$arr1 = ["228d165f-a4ad-4883-8238-bc0329c7ba8a"];
                    //
                    ////$devices_arr = json_encode($devices_arr);
                    ////$devices_arr = json_decode($devices_arr);
                    //    
                    ////echo "devices_arr : ".sizeof($devices_arr).": ".json_encode($devices_arr)."<br>";
                    ////echo "in array : ".in_array($deviceId,$devices_arr,true)."<br>";
                    //
                    //if($deviceId != "" and isDeviceIdExist($devices_arr,$deviceId)){
                    //   
                    //    login_success($records,"Login Successful");
                    //
                    //}elseif(sizeof($devices_arr)<$MAX_USERS){
                    //    //echo "device id not exist <br>";
                    //     
                    //    $update_arr = array();
                    //    $update_arr["username"] = $username;
                    //    $update_arr["password"] = $password;
                    //            
                    //    //$devices_arr = array();
                    //    array_push($devices_arr,$deviceId);
                    //    
                    //    $update_arr["devices_arr"] = $devices_arr;
                    //    
                    //    $records = $users_collection->findOneAndUpdate($check_arr,array('$set'=>$update_arr));
                    //    
                    //    login_success($records,"Login Successful");
                    //    
                    //}else{    
                    //    request_failed("Already login on MAX DEVICES!");                    
                    //}
                
                }else{
                    request_failed("Incorrect Username / Password");
                }
                //echo var_dump($records);
                
                //retrieve for multiple records.
                //foreach ($records as $document) {
                //    $response = array();
                //    $response["name"] = $document["name"];
                //    $response["username"] = $document["username"];
                //    echo json_encode($response);
                //    //echo $document["name"] . "<br>";
                //}
            }elseif($role == "sub_user"){
                $user = array();
                $user["Username"] = $username;
                $user["Password"] = $password;
                
                //$user["deviceId"] = $deviceId;
                
                $filter_arr = array("subUsers_arr" => array('$in' => $user));
                
                $records = $users_collection->findOne(array(),$filter_arr);
                
                if($records){
                    
                    $subUsers_arr = $records["subUsers_arr"];
                    $arrlength = count($subUsers_arr);
                    for($i = 0; $i < $arrlength ; $i++){
                        $item = $subUsers_arr[$i];
                        if($item["Username"] == $username && $item["Password"] == $password && $item["deviceId"]== ""){
                            
                            $item["deviceId"] = $deviceId;
                            $subUsers_arr[$i] = $item;
                            $records["subUsers_arr"] = $subUsers_arr;
                            
                            //$devices_arr = $records["devices_arr"];
                            //array_push($devices_arr,$deviceId);
                            //$records["devices_arr"] = $devices_arr;
                            
                            //$users_collection->updateOne(array("username"=>$records["username"],
                            //                                   "password"=>$records["password"]),
                            //                            array('$set'=>$records));
                            
                            $record_updated = $users_collection->findOneAndUpdate(array("username"=>$records["username"],
                                                                                 "password"=>$records["password"]),
                                                                           array('$set'=>array("subUsers_arr"=>$subUsers_arr)),
                                                                           array('returnDocument' => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER));
                            
                            //echo "updated : ".var_dump($record_updated)."<br>";
                            
                            login_success($record_updated,"Sub user login successful",$role);
                            break;
                        }elseif($item["Username"] == $username && $item["Password"] == $password && $item["deviceId"]== $deviceId){
                            login_success($records,"Login Successful",$role);
                            break;
                        }elseif($item["Username"] == $username && $item["Password"] == $password){
                            request_failed($item["Username"]." login from another device");
                            break;
                        }
                    }
                    
                }else{
                    request_failed("Incorrect Username / Password");
                }
                
                
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        
        break;

    case "sign_up":
        try {
            $username = utf8_encode($_GET['un']);
            $password = utf8_encode($_GET['pass']);
            $deviceId = utf8_encode($_GET['deviceId']);
            $mobile  = utf8_encode($_GET['mobile']);
            $email = utf8_encode($_GET['email']);
            
            $username = stripslashes($username);
            $password = stripslashes($password);
            $deviceId = stripslashes($deviceId);
            $mobile  = stripslashes($mobile);
            $email = stripslashes($email);
            
            $client = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$usersDBName);        
            $medical_users_db = $client->medical_users;
            $users_collection = $medical_users_db->userscol;
            
            $check_arr = array();
            array_push($check_arr,array("username"=>$username));
            array_push($check_arr,array("mobile"=>$mobile));
            array_push($check_arr,array("email"=>$email));
        
            $records = $users_collection->findOne(array('$or'=>$check_arr));
            
             if($records){
                request_failed("Username / Mobile / Email already used.\nUse another details to registration.");
             }else{
                $insert_array = array();
                $insert_array["username"] = $username;
                $insert_array["password"] = $password;
                
                //$insert_array["deviceId"] = $deviceId;
                $devices_arr = array();
                array_push($devices_arr,$deviceId);
                $insert_array["devices_arr"] = $devices_arr;
                
                $subUsers_arr = array();
                $insert_array["subUsers_arr"] = $subUsers_arr;
                
                $insert_array["mobile"] = $mobile;
                $insert_array["email"] = $email;
                $insert_array["expiry"] = ((round(microtime(true) ) + (7 * 24 * 60 * 60)))* 1000;
                
            
                $response = $users_collection->insertOne($insert_array);
                if($response){
                    login_success($insert_array,"Login Successful","admin");
                }else{
                    request_failed("Server error, Please contact support");
                }
             }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        break;
    
    case"add_sub_user";
        try {
            $username = utf8_encode($_GET['un']);
            $mobile  = utf8_encode($_GET['mobile']);
            $s_un = utf8_encode($_GET['s_un']);
            $s_pass = utf8_encode($_GET['s_pass']);
            $sub_users = utf8_encode($_GET['sub_users']);
            
            $username = stripslashes($username);
            $mobile  = stripslashes($mobile);
            $s_un = stripslashes($s_un);
            $s_pass = stripslashes($s_pass);
            $sub_users = stripcslashes($sub_users);
            
            
            $client = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$usersDBName);        
            $medical_users_db = $client->medical_users;
            $users_collection = $medical_users_db->userscol;
            
            $check_arr = array();
            $check_arr["username"] = $username;
            $check_arr["mobile"] = $mobile;

            
            $update_array = array();
            //$update_array["username"] = $username;
            //$update_array["password"] = $password;
            
            $sub_users = json_decode($sub_users);
            
            $update_array["subUsers_arr"] = $sub_users;
            
            
            //$update_array["mobile"] = $mobile;
            //$update_array["email"] = $email;
            //$update_array["expiry"] = ((round(microtime(true) ) + (7 * 24 * 60 * 60)))* 1000;
            
            $records = $users_collection->findOneAndUpdate($check_arr,array('$set'=>$update_array),array('returnDocument' => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER));
            if($records){
                //echo "found";
                addSubUserSuccess($records,"Users updated successfully.");
            }else{
                request_failed("Error, Please try later.");
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        break;
    
    case "sign_out":
        
        $username = utf8_encode($_GET['un']);
        $mobile  = utf8_encode($_GET['mobile']);
        
        
        
        
        break;
    
    case "add_bill_record":
        try{
            $username = utf8_encode($_GET['un']);
            $mobile  = utf8_encode($_GET['mobile']);
            $bill_info = utf8_encode($_GET['bill_info']);
            $role = utf8_encode($_GET['role']);
            $deviceId = utf8_encode($_GET['deviceId']);
            
            $username = stripslashes($username);
            $mobile  = stripslashes($mobile);
            $bill_info = stripslashes($bill_info);
            $role = stripslashes($role);
            $deviceId = stripslashes($deviceId);
            
            //echo "1";
            
            $bInfo = json_decode($bill_info,true);
            //echo " ".json_encode($bInfo);
            
            //$bInfo = json_decode(json_encode($bInfo));
            //echo " <br><br> bil : ".$bInfo->{"billNo"};
            
            $bInfo["billNo"] = "".round(microtime(true)* 10000);
            
            $client = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$usersDBName);        
            $medical_users_db = $client->$usersDBName;
            $users_collection = $medical_users_db->userscol;
                
            //echo " <br><br>  3";
                
            if($role == "admin"){
                $check_arr = array();
                //$check_arr["username"] = $username;
                //$check_arr["mobile"] = $mobile;
                
                $ids = array();
                array_push($ids,$deviceId);
                
                $check_arr["devices_arr"]= array('$in'=>$ids);
                
                $records = $users_collection->findOne($check_arr);
                //echo var_dump($records);
               addBill($billsDBName,$dbUser,$dbPwd,$records,$bInfo);
                
            }elseif($role == "sub_user"){
                //$check_arr = array();
                //array_push($check_arr,"228d165f-a4ad-4883-8238-bc0329c7ba8a");
                //$id = "228d165f-a4ad-4883-8238-bc0329c7ba8a";
                
                //$check_arr["username"] = $username;
                //$check_arr["mobile"] = $mobile;
                
                $check_arr = array();
                $check_arr["subUsers_arr"] = array('$elemMatch'=>array("deviceId"=>$deviceId));
                
                $records = $users_collection->findOne($check_arr);
                //echo var_dump($records);
                
                addBill($billsDBName,$dbUser,$dbPwd,$records,$bInfo);
            }
                
                   
                    
            
            //echo "".$bill_info;
         } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        break;
    
    case "add_stock":
        try{
            $username = utf8_encode($_GET['un']);
            $mobile  = utf8_encode($_GET['mobile']);
            $stock_info = utf8_encode($_GET['stock_info']);
            $role = utf8_encode($_GET['role']);
            $deviceId = utf8_encode($_GET['deviceId']);
            
            $username = stripslashes($username);
            $mobile  = stripslashes($mobile);
            $stock_info = stripslashes($stock_info);
            $role = stripslashes($role);
            $deviceId = stripslashes($deviceId);
            
            //echo "1";
            
            $sInfo = json_decode($stock_info,true);
            //echo " ".json_encode($bInfo);
            
            //$sInfo = json_decode(json_encode($sInfo));
            //echo " <br><br> bil : ".$bInfo->{"billNo"};
            
            //$sInfo->{"billNo"} = "".round(microtime(true)* 10000);
            
            $client = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$usersDBName);        
            $medical_users_db = $client->$usersDBName;
            $users_collection = $medical_users_db->userscol;
                
            //echo " <br><br>  3";
                
            if($role == "admin"){
                $check_arr = array();
                //$check_arr["username"] = $username;
                //$check_arr["mobile"] = $mobile;
                
                $ids = array();
                array_push($ids,$deviceId);
                
                $check_arr["devices_arr"]= array('$in'=>$ids);
                
                $records = $users_collection->findOne($check_arr);
                //echo var_dump($records);
                updateStock($stocksDBName,$dbUser,$dbPwd,$username,$records,$sInfo);
                
            }elseif($role == "sub_user"){
                //$check_arr = array();
                //array_push($check_arr,"228d165f-a4ad-4883-8238-bc0329c7ba8a");
                //$id = "228d165f-a4ad-4883-8238-bc0329c7ba8a";
                
                //$check_arr["username"] = $username;
                //$check_arr["mobile"] = $mobile;
                
                $check_arr = array();
                $check_arr["subUsers_arr"] = array('$elemMatch'=>array("deviceId"=>$deviceId));
                
                $records = $users_collection->findOne($check_arr);
                //echo var_dump($records);
                updateStock($stocksDBName,$dbUser,$dbPwd,$username,$records,$sInfo);
                
            }
              
            //echo "".$bill_info;
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n ", " line no: ", $e->getLine();
        }
        break;
    
    case "time":
        $role = utf8_encode($_GET['role']);
        $role = stripslashes($role);
        
        echo round(microtime(true)* 10000)."<br><br>";
        
        $client = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/");        
        $medical_users_db = $client->$usersDBName;
        $users_collection = $medical_users_db->userscol;
        
        if($role == "admin"){
            $check_arr = array();
            array_push($check_arr,"228d165f-a4ad-4883-8238-bc0329c7ba8a");
            $records = $users_collection->findOne(array("devices_arr"=>array('$in'=>$check_arr)));
            echo var_dump($records);
            
        }elseif($role == "sub_user"){
            //$check_arr = array();
            //array_push($check_arr,"228d165f-a4ad-4883-8238-bc0329c7ba8a");
            $id = "228d165f-a4ad-4883-8238-bc0329c7ba8a";
            $records = $users_collection->findOne(array("subUsers_arr"=>array('$elemMatch'=>array("deviceId"=>$id))));
            echo var_dump($records);
        }

        
        
        
        //$records = $users_collection->findOne($check_arr);
        
        
        break;
    case "insertdata":
        try{
            $var = "{
                        \"id\":\"ABIGEL\",
                        \"meicineType\":\"brand\",
                        \"referencedGenericMedicine\" :[\"aluminium hydroxide\"],
                        \"Manufacturer\":\"Alpic Biotech\",
                        \"ATC Classification\":{\"C01CA06 \":\"  phenylephrine ; Belongs to the class of adrenergic and dopaminergic cardiac stimulants excluding glycosides. Used in the treatment of heart failure.\",
                          \"R01AA04 \":  \"phenylephrine ; Belongs to the class of topical sympathomimetic agents used as nasal decongestants.\",
                          \"R01AB01 \":  \"phenylephrine ; Belongs to the class of topical sympathomimetic combination preparations, excluding corticosteroids. Used as nasal decongestants.\",
                          \"R01BA03 \":  \"phenylephrine ; Belongs to the class of systemic sympathomimetic preparations used as nasal decongestants.\",
                          \"S01FB01 \":  \"phenylephrine ; Belongs to the class of sympathomimetics used as mydriatics and cycloplegics.\",
                          \"S01GA05 \":  \"phenylephrine ; Belongs to the class of sympathomimetics used as ophthalmologic decongestants.\"
                        },
                        \"Contents\": \"ABIGEL oral susp: aluminium hydroxide gel 250 mg, magnesium hydroxide 250 mg, simeticone 25 mg/5 \",
                        \"CIMSClass\":\"Antacids, Antireflux Agents & Antiulcerants\",
                        \"presentation\" : [{
                          \"type\": \"bottol\",
                          \"quantity\" : \"170ml\",
                          \"Price\": \"49.95\"
                        },{
                          \"type\": \"strip\",
                          \"quantity\" : \"10\",
                          \"Price\": \"55\"
                        }]
                    }";
                           
            $username = "user1"."medicines";
            echo var_export($username,true)."<br><br>";        
            
            $client = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$mediDBName);        
            $medicines_db = $client->medicines_db;
        
            $medicines_db->selectCollection($username);
        
            $medicine_collection = $medicines_db->$username;
            
            $insert_array = json_decode($var);
            
            echo json_encode($insert_array)."<br><br>";
            
            $response = $medicine_collection->insertOne($insert_array);
            
            echo "after insert <br><br>";
            echo var_dump($response);
        
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        
        break;
}

function login_success($records,$msg,$role){
    $response = array();
    $response["resultCode"] = 200;
    $response["msg"] = $msg;
    $response["username"] = $records["username"];
    
    $response["role"] = $role;
    
    $response["mobile"] = $records["mobile"];
    $response["email"] = $records["email"];
    $response["expiry"] = $records["expiry"];
    
    $response["devices_arr"] = $records["devices_arr"];
    $response["subUsers_arr"] = $records["subUsers_arr"];
    
    
            
    echo json_encode($response);
}

function request_failed($msg){
    $response = array();
    $response["resultCode"] = 401;
    $response["msg"] = $msg;
    echo json_encode($response);
}

function addSubUserSuccess($records,$msg){
    $response = array();
    $response["resultCode"] = 201;
    $response["msg"] = $msg;
    $response["subUsers_arr"] = $records["subUsers_arr"];
    echo json_encode($response);
}

function addBill($billsDBName,$stocksDBName,$dbUser,$dbPwd,$records,$bInfo){
     if($records){
        $client = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$billsDBName);        
        $bills_db_selected = $client->$billsDBName;
        $billColName = $username."bills";
        $bills_collection = $bills_db_selected->$billColName;
        
        $response = $bills_collection->insertOne($bInfo);
        
        if($response){
            
            
            $client1 = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$stocksDBName);        
            $stocks_db_selected = $client1->$stocksDBName;
            $stockColName = $username."stocks";
            $stocks_collection = $stocks_db_selected->$stockColName;
            
            $billMedicinesList = $bInfo["billMedicinesList"];
            
            $mediStock = array();
            
            foreach($billMedicinesList as $item){
                $c_arr = array();
                $c_arr["id"] = $item["id"];
                $stock_record = $stocks_collection->findOne($c_arr);
                if($stock_record){
                    $details_arr = $stock_records["details"];
                    
                    ksort($details_arr["expiry"]);
                    
                    $item0 = $details_arr[0];
                    $item0["quantity"] = $item0["quantity"] - $item["quantity"];
                    if($item0["quantity"]<0){
                        $item0["quantity"] = 0;
                    }
                    
                    $details_arr[0] = $item0;
                    $stock_records["details"] = $details_arr;
                    
                    $arr = array();
                    $arr["id"] = $item["id"];
                    $arr["type"] = $item["type"];
                    $arr["updatedStock"] = $item0["quantity"];
                    array_push($mediStock, $arr);
                    
                    $updateResult = $stocks_collection->findOneAndUpdate($c_arr,array('$set'=> $stock_record));
                }
            }
            
            $result = array();
            $result["billNo"] = $bInfo["billNo"];
            $result["mediStock"] = $mediStock;
            
            addBillSuccess($result,"Bill saved.");
            
        }else{
            request_failed("Bill not saved");    
        }
        //echo var_dump($response);
    }else{
        request_failed("Not valid device");
    }
}

function addBillSuccess($result,$msg){
    //$response = array();
    $result["resultCode"] = 202;
    $result["msg"] = $msg;
    //$response["billNo"] = $billNo;
    echo json_encode($result);
}


function updateStock($stocksDBName,$dbUser,$dbPwd,$username,$records,$sInfo){
    
    try{
        if($records){
            $client1 = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$stocksDBName);        
            $stocks_db_selected = $client1->$stocksDBName;
            $stockColName = $username."stocks";
            $stocks_collection = $stocks_db_selected->$stockColName;
            $c_arr = array();
            $c_arr["id"] = $sInfo["id"];
            
            $stock_records = $stocks_collection->findOne($c_arr);
            if($stock_records){
                //echo "<br><br><br> id present";
                
                $details_arr = $stock_records["details"];
                
                $details_item = $sInfo["details"][0];
                
                $qty = 0;
                $minExpiry = 0;
                $isSameBatch = false;
                
                $newDetailsArr = array();
                
                for ($i=0;$i<count($details_arr);$i++) {
                    
                    $item = $details_arr[$i];
                    
                    if($item["batch"] == $details_item["batch"]){
                        $isSameBatch = true;
                        $item["quantity"] = $item["quantity"] + $details_item["quantity"];
                        //$item["bill"] = $item["bill"] + $details_item["bill"];
                        $item["expiry"] = $details_item["expiry"];
                    }
                    
                    //$details_arr[$i] = $item;
                                        
                    if($item["quantity"]>0){
                        $qty = $qty + $item["quantity"];
                        
                        if($minExpiry == 0 or $minExpiry > $item["expiry"]){
                            $minExpiry = $item["expiry"];
                        }
                        
                        array_push($newDetailsArr, $item);
                    }
                    
                }
                
                if($isSameBatch == false){
                    array_push($newDetailsArr,$details_item);
                    $qty = $qty + $details_item["quantity"];
                    if($minExpiry == 0 or $minExpiry > $details_item["expiry"]){
                        $minExpiry = $details_item["expiry"];
                    }
                }
                
                //$sInfo["id"];
                $sInfo["details"] = $newDetailsArr;
                $sInfo["updatedStock"] = $qty;
                $sInfo["minExpiry"] = $minExpiry;
                
                $updateResult = $stocks_collection->findOneAndUpdate($c_arr,array('$set'=>$sInfo));
                addStockResult($updateResult,$sInfo);
                
            }else{
                //echo "<br><br><br> id not present";
                //id not present
                //$stock_records = array();
                //$details = $sInfo->{"details"};
                $details_item = $sInfo["details"][0];
                $sInfo["updatedStock"] = $details_item["quantity"];
                $sInfo["minExpiry"] = $details_item["expiry"];
                
                $response = $stocks_collection->insertOne($sInfo);
            
                if($response){
                    addStockResult($response,$sInfo);
                }else{
                    request_failed("Stock not added");
                }
            }
            
            //echo var_dump($response);
        }else{
            request_failed("Not valid device");
        }
    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n", $e->getTraceAsString();
    }
}

function addStockResult($updateResult,$sInfo){
    if($updateResult){
        $sInfo["resultCode"] = 203;
        $sInfo["msg"] = "Stock added succesfully";
        echo json_encode($sInfo);
    }else{
        request_failed("Stock not added");
    }
}




function isDeviceIdExist($devices_arr, $deviceId){
    $var = false;
    foreach($devices_arr as $id){
        if($id == $deviceId){
             //echo "device id exist <br>";
             $var = true;
        }
    }
    
    return $var;
}

?>