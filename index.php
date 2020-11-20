<html>
<head>
    <title>Face Detect Sample</title>
</head>
<body>
        <form style="float:left" action="index.php" method="post">
            <h3>Store Face to [Face List]</h3>
            <a target="_blank" href="https://westcentralus.dev.cognitive.microsoft.com/docs/services/563879b61984550e40cbbe8d/operations/5a158c10d2de3616c086f2d3">
                image instruction
            </a>
            <br/>
            <textarea required rows="4" cols="50" name="image_url" placeholder="Image URL"></textarea>
            <br/>
            <br/>
            <input type="hidden" name="type" value="save"/>
            <input type="submit" name="submit" value="Save Face"/>
        </form>

        <form  style="float:right" action="index.php" method="post">
            <h3>Searching Face</h3>
            <textarea required rows="4" cols="50" name="image_url" placeholder="Image URL"><?php echo !empty($_POST['image_url']) && !empty($_POST['mode'])  ? $_POST['image_url'] : ''?></textarea>
            <br/>
            <br/>
            <input type="hidden" name="type" value="match"/>
            matchPerson <Input type="radio" <?php echo  empty($_POST['mode']) || ( !empty($_POST['mode']) && $_POST['mode']=='matchPerson' ) ? 'checked' : '';  ?> name="mode" value="matchPerson">
            matchFace <Input type="radio" <?php echo  !empty($_POST['mode']) && $_POST['mode']=='matchFace' ? 'checked' : ''; ?> name="mode" value="matchFace">
            <input type="submit" name="match" value="Match Face"/>
        </form>

        <?php

        // persisted id
//        a398597a-7c64-45db-8917-0e40c6d56707
//        89531d23-9419-4499-becc-3b86403aa2d6
//        61ee7151-b629-4c04-9d82-0392b73fd71e
//        d1af7c58-473c-4702-be15-265125e091b1
//        0c7d2697-108f-494b-a9a9-ef22761bf879

        // safwan
//        b063b770-972c-4876-ae30-22d4a9aa76db

//        45fb40cc-8f45-40db-860a-debbf3302229

        // purnima
//        8d934061-4ea0-416c-a661-4c9c5dc3534a

        // authentication

//        Endpoint: https://westcentralus.api.cognitive.microsoft.com/face/v1.0
//
//        Key 1: dfd66733c5a64b918a0ebcf1fa0d014a
//
//        Key 2: f5778fa95ea44baa872828d4dbed110c


        $conn = mysqli_connect("localhost","root","","face_recognition");
        // Check connection
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit();
        }
        else {
//            die('db connection successfully');
        }
        require_once 'HTTP/Request2.php';
        $subscription_key = 'd1624f32813f4867bf6ce72cc0f3a417';
       // $subscription_key = 'dfd66733c5a64b918a0ebcf1fa0d014a';
       // $api_base_url = 'https://westcentralus.api.cognitive.microsoft.com/face';
        $api_base_url = 'https://westcentralus.api.cognitive.microsoft.com/face/v1.0';
        $recognition_model = 'recognition_02';
        $face_list_id = 'safwan_test';
        $deduction_model = 'detection_02';

      //  https://westcentralus.api.cognitive.microsoft.com/face/v1.0/largefacelists/safwan_test

        function train_face() {
            global $subscription_key,$face_list_id;
            // This sample uses the Apache HTTP client from HTTP Components (http://hc.apache.org/httpcomponents-client-ga/)

            $request = new Http_Request2("https://westcentralus.api.cognitive.microsoft.com/face/v1.0/largefacelists/$face_list_id/train");
            $url = $request->getUrl();

            $headers = array(
                // Request headers
                'Ocp-Apim-Subscription-Key' => $subscription_key,
            );

            $request->setHeader($headers);

            $parameters = array(
                // Request parameters
            );

            $url->setQueryVariables($parameters);

            $request->setMethod(HTTP_Request2::METHOD_POST);

            // Request body
//            $request->setBody("{body}");

            try
            {
                $response = $request->send();
                echo $response->getBody();
            }
            catch (HttpException $ex)
            {
                echo $ex;
            }
        }

        function detect_face($image_url,$details = false) {

            global $subscription_key, $api_base_url;



            $request = new Http_Request2($api_base_url . '/detect');
            $url = $request->getUrl();

            $headers = array(
                // Request headers
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $subscription_key
            );
            $request->setHeader($headers);

            $parameters = array(
                // Request parameters
                'returnFaceId' => 'true',
                'returnRecognitionModel' => 'true',
                'recognitionModel' => 'recognition_02',
                'detectionModel' => $details ? '' : 'detection_02',
                'returnFaceLandmarks' => $details ? 'true' :'false',
                'returnFaceAttributes' => $details ? "age,gender,headPose,smile,facialHair,glasses,emotion,hair,makeup,occlusion,accessories,blur,exposure,noise" : ''

            );

            $url->setQueryVariables($parameters);

            $request->setMethod(HTTP_Request2::METHOD_POST);

            // Request body parameters
            $body = json_encode(array('url' => $image_url));

            // Request body
            $request->setBody($body);

            try {
                $response = $request->send();
                $face_info = json_decode($response->getBody());
                return $face_info;
            } catch (HttpException $ex) {
                echo "<pre>" . $ex . "</pre>";
                die('something is wrong');
            }


        }

        function add_face( $image_url,$target_face) {

            global $face_list_id , $deduction_model, $subscription_key, $api_base_url;

            // This sample uses the Apache HTTP client from HTTP Components (http://hc.apache.org/httpcomponents-client-ga/)

            $dimention = [
                'left' => $target_face['left'],
                'top' => $target_face['top'],
                'width' => $target_face['width'],
                'height' => $target_face['height'],
            ];

            $dimention_string = implode(",",$dimention);

            $request_url = $api_base_url."/largefacelists/{$face_list_id}/persistedfaces";

            $request = new Http_Request2($request_url);


            $url = $request->getUrl();

            $headers = array(
                // Request headers
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $subscription_key,
            );

            $request->setHeader($headers);

            $parameters = array(
                // Request parameters
                'targetFace' => $dimention_string,
                'detectionModel' => $deduction_model,
            );



            $url->setQueryVariables($parameters);

            $request->setMethod(HTTP_Request2::METHOD_POST);

            $body = json_encode(array(
                'url' => $image_url
            ));

            // Request body
            $request->setBody($body);

            try
            {
                $response = $request->send();
                $response_body = json_decode($response->getBody(),true);
                if(array_key_exists("persistedFaceId",$response_body)) {
                    $persisted_face_id = $response_body['persistedFaceId'];
                    return $persisted_face_id;
                }
                else {
                    print_r($response_body);
                    die();
                }
                echo "<pre>";
                print_r($response_body);
                echo "</pre>";
                die();
            }
            catch (HttpException $ex)
            {
                echo $ex;
            }
        }

        if(isset($_POST['submit'])) {

            $image_url = trim($_POST['image_url']);
            $face_ractangle =  detect_face($image_url);
			
			//echo "<pre/>";
			//print_r($face_ractangle);
			
            $face_info = detect_face($image_url,true);

            if(count($face_ractangle) > 0 ) {

                $sql = "INSERT INTO image_relation (image)
                    VALUES ('$image_url')";

                $insert_image = $conn->query($sql);

                if ($insert_image=== TRUE) {
                    $image_id = $conn->insert_id;
                    foreach ($face_ractangle as $key => $info) {

                        $face_id = add_face($image_url, ( array )$info->faceRectangle);
						echo "<pre/>";
						print_r($face_id);
						die();
						
                        // store information
                        if (!empty($face_id)) {
                            $details = json_encode($face_info[$key]);
                            $face_rectangle = json_encode($info->faceRectangle);
                            $sql = "INSERT INTO face_info (image_id,face_id,face_rectangle,face_details)
                    VALUES ('$image_id', '$face_id','$face_rectangle','$details')";

                            if ($conn->query($sql) === TRUE) {
								echo $face_id;
                            } else {
                                echo "Error: " . $sql . "<br>" . $conn->error;
                                die();
                            }

                        }
                    }
                }

                $conn->close();

                train_face(); // face detected face
            }

            echo "<pre>";
            echo "</br>";
            echo "record saved successfully";
            die();
        }


        function match_face($fatch_id,$mode = 'matchPerson') {
            global $subscription_key , $face_list_id, $api_base_url;
            // This sample uses the Apache HTTP client from HTTP Components (http://hc.apache.org/httpcomponents-client-ga/)

            $request = new Http_Request2($api_base_url.'/findsimilars');
            $url = $request->getUrl();

            $headers = array(
                // Request headers
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $subscription_key,
            );

            $request->setHeader($headers);

            $parameters = array(
                // Request parameters
            );

            $url->setQueryVariables($parameters);

            $request->setMethod(HTTP_Request2::METHOD_POST);

            $request_body = json_encode(array(
                'faceId' => $fatch_id,
                'largeFaceListId' => $face_list_id,
                'maxNumOfCandidatesReturned' => 10,
                'mode' => $mode
            ));
            // Request body
            $request->setBody($request_body);

            try
            {
                $response = $request->send();
                $response_body = $response->getBody();
                return $response_body;
            }
            catch (HttpException $ex)
            {
                echo $ex;
            }
        }


        // match api call
        if(isset($_POST['match'])) {
            $image_url = $_POST['image_url'];
            $mode = $_POST['mode'];
            $face_details =  detect_face($image_url);

//            
            $similar_faceids = [];
            foreach($face_details as $key => $face) {

                $get_face_ids =  match_face(!empty($face->faceId) ? $face->faceId :0,$mode);
                        
                $decode_faceids = json_decode(!empty($get_face_ids) ? $get_face_ids :0);

                foreach($decode_faceids as $dkey => $target_face_id) {
                    $similar_faceids[] = [
                        'face_id' => !empty($target_face_id->persistedFaceId) ? $target_face_id->persistedFaceId :'',
                        'confidence' => !empty($target_face_id->confidence) ? $target_face_id->confidence :'',
                    ];
                }

            }
            
           // echo "<pre/>";
           // print_r($similar_faceids);
           // die();
//            
            
            if(count($similar_faceids)> 0 ) {
                echo "<div style='float:right;height:auto;weight:500px;background:lightgray;'>";
                foreach($similar_faceids as $item) {
                    $sql = "SELECT face_info.*,image_relation.image FROM face_info INNER JOIN image_relation ON image_relation.id=face_info.image_id WHERE face_info.face_id='{$item['face_id']}'";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        // output data of each row
                        while ($row = $result->fetch_assoc()) {
                            $face_details = json_decode($row['face_details'],true);
//                            echo "<pre>";
//                            print_r($face_details['faceAttributes']['age']);die();

                            echo "<img src='{$row['image']}' style='height:300px;weight:300px'/> {$item['confidence']}"." | ".$face_details['faceAttributes']['gender']." | ".$face_details['faceAttributes']['age']."<br>";
                        }
                    } else {
//                        echo "0 results";
                    }

                }
                echo "</div>";
                $conn->close();
            }

//            echo "<pre>";
//            print_r($similar_faceids);
//            exit;
        }


        ?>
</body>
</html>