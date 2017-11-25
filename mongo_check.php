<?php
    require 'vendor/autoload.php';
    $dbName = "medical_users";
    $dbUser = "androidApp";
    $dbPwd = "androidDoctor1234";
    
    try {
        //echo " Manager \n";
        //$manager = new MongoDB\Driver\Manager("mongodb://127.0.0.1:27017");
        
        
        //$client = new Client();
        //$this->assertEquals('mongodb://127.0.0.1/', (string) $client);
        //echo var_dump($client);
        
        //echo "before client \n";
        
        //("mongodb://${username}:${password}@localhost", array("db" => "myDatabase"));
        
        $client = new MongoDB\Client("mongodb://".$dbUser.":".$dbPwd."@localhost:27017/".$dbName);
        //echo json_encode($client);
        
        //try
        //{
            //echo "try clause \n";
            //$dbs = $client->listDatabases();
            //echo json_encode($dbs);
        //}
        //catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e)
        //{
        //    // PHP cannot find a MongoDB server using the MongoDB connection string specified
        //    // do something here
        //    echo "got error";
        //}
        
        //selecting database
        $medical_users_db = $client->medical_users;
        echo "Database db selected <br>";
        
        $users_collection = $medical_users_db->userscol;
        echo "collection selected <br>";
    
        $records = $users_collection->find();
        
        foreach ($records as $document) {
           echo $document["name"] . "<br>";
        }
        
        //$it = new \IteratorIterator($records);
        //$it->rewind(); // Very important
        //
        //while($doc = $it->current()) {
        //    echo $doc['name'];
        //    $it->next();
        //}
        
        // now update the document
        $users_collection->updateOne(array("name"=>"Zumbarlal"), 
           array('$set'=>array("name"=>"Zumbarlal Saindane","username"=>"user1","password"=>"password")));
        
        echo "<br>Document updated successfully";
        
        // now display the updated document
        $cursor = $users_collection->find();
         
        // iterate cursor to display title of documents
        echo "<br>Updated document<br>";
         
        foreach ($cursor as $document) {
           echo $document["name"] . "<br>";
        }
        
        
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
    
?>