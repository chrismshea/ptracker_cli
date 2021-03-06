<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chris
 * Date: 9/1/13
 * Time: 3:51 PM
 * To change this template use File | Settings | File Templates.
 */
?>
<?php
	class pivotaltracker {

        // Public properties
        var $token;
        var $project;

        // ---------
        // addStory
        // -----
        // Add a story to an existing project
        public function addStory($token, $pId, $sType, $sName, $sDesc)
        {

            // Encode the description
            $sDesc = htmlentities($sDesc);

            // Make the fields safe
            $sType = escapeshellcmd($sType);
            $sName = escapeshellcmd($sName);

            // Create the new story
            $cmd = "curl -s -H \"X-TrackerToken: $token\" "
                . "-X POST -H \"Content-type: application/json\" "
                . "-d '{\"name\":\"$sName\","
                . "\"story_type\":\"$sType\","
                . "\"description\":\"$sDesc\"}' "
                . "\"https://www.pivotaltracker.com/services/v5/projects/$pId/stories\"";
            $json = shell_exec($cmd);

            // Return an object
            $json_array = json_decode($json,true);
            $sId = $json_array['id'];
            $sUrl = $json_array['url'];
            $sInfo = array(
                "sId"  => $sId,
                "sUrl" => $sUrl);
            return $sInfo;
        }


        // ----------
        // addComment
        // -----
        // Add a comment to an existing story
        public function addComment($token, $pId, $sId, $sComm)

        {
            // Create the new story
            $cmd = "curl -s -H \"X-TrackerToken: $token\" "
                . "-X POST -H \"Content-type: application/json\" "
                . "-d '{\"text\":\"$sComm\"}' "
                . "\"https://www.pivotaltracker.com/services/v5/projects/$pId/stories/$sId/comments\"";
            $json = shell_exec($cmd);

            // Return an object
            $json_array = json_decode($json,true);
            $cResult = $json_array['text'];
            return $cResult;
        }


        // ----------
        // addAttachmentsWithComment
        // -----
        // Add uploads as attachments with comment
        public function addAttachments($token, $pId, $sId, $sComm, $pUploadsString)

        {
            // Create the new story
            $cmd = "curl -s -H \"X-TrackerToken: $token\" "
                . "-X POST -H \"Content-type: application/json\" "
                . "-d '{\"file_attachments\":["
                . $pUploadsString . "],"
                . "\"text\":\"$sComm\"}' "
                . "\"https://www.pivotaltracker.com/services/v5/projects/$pId/stories/$sId/comments?fields=%3Adefault%2Cfile_attachment_ids\"";
            $json = shell_exec($cmd);

            // Return an object
            $json_array = json_decode($json,true);
            $cResult = $json_array['text'];
            return $cResult;
        }


        // ----------
        // addTask
        // -----
        // Add a task to an existing story.
        public function addTask($story, $desc) {

            // Encode the description
            $desc = htmlentities($desc);

            // Make the fields safe
            $story = escapeshellcmd($story);

            // Create the new task
            $cmd = "curl -H \"X-TrackerToken: {$this->token}\" "
                . "-X POST -H \"Content-type: application/xml\" "
                . "-d \"<task><description>$desc</description></task>\" "
                . "https://www.pivotaltracker.com/services/v3/projects/{$this->project}/stories/$story/tasks";
            $xml = shell_exec($cmd);

        }


        // ----------
        // addAttachment
        // -----
        // Add an attachment to an existing story.
        public function addAttachment($story, $filePath) {

            // Make the fields safe
            $story = escapeshellcmd($story);

            // Create the new attachment
            $cmd = "curl -H \"X-TrackerToken: {$this->token}\" "
                . "-X POST -F Filedata=@$filePath "
                . "https://www.pivotaltracker.com/services/v3/projects/{$this->project}/stories/$story/attachments";

            $xml = shell_exec($cmd);

        }

        // ----------
        // addUploads
        // -----
        // Add uploads to a project, the response will be used to add a comment and attach to a story.
        public function addUploads($token, $project, $sLogs)
        {

            $tUploads = "https://www.pivotaltracker.com/services/v5/projects/$project/uploads";
            $ch = curl_init();
            foreach($sLogs as $sLog)
            {

                // Let's establish the needed options for the post
                curl_setopt_array($ch, array(
                    CURLOPT_SSL_VERIFYHOST    => 1,
                    CURLOPT_SSL_VERIFYPEER    => 0,
                    CURLOPT_FOLLOWLOCATION    => 1,
                    CURLINFO_SSL_VERIFYRESULT => 0,
                    CURLOPT_VERBOSE           => 0,
                    CURLOPT_ENCODING          => "",
                    CURLOPT_POST              => 1,
                    CURLOPT_HTTPHEADER        => array("X-TrackerToken: $token"),
                    CURLOPT_POSTFIELDS        => array("file" => "@$sLog"),
                    CURLOPT_URL               => $tUploads,
                    CURLOPT_RETURNTRANSFER    => 1,
                ));

                // Ship IT! and capture the response
                $pUploads[] = curl_exec($ch);
                return $pUploads;
            }
            curl_close($ch);
            return NULL;
        }

            /**
             * @param $story
             * @param $labels
             *             // We will first upload the attachments to the project
            $trackerUploads = "https://www.pivotaltracker.com/services/v5/projects/$projectId/uploads";

            foreach($mage_logs as $file) {

            // We're going to need some curl
            $ch = curl_init();

            // Let's establish the needed options for the post
            curl_setopt_array($ch, array(
            CURLOPT_SSL_VERIFYHOST    => 1,
            CURLOPT_SSL_VERIFYPEER    => 0,
            CURLOPT_FOLLOWLOCATION    => 1,
            CURLINFO_SSL_VERIFYRESULT => 0,
            CURLOPT_VERBOSE           => 0,
            CURLOPT_ENCODING          => "",
            CURLOPT_POST              => 1,
            CURLOPT_HTTPHEADER        => array("X-TrackerToken: b4b8aa330d2dbad607be9433dc2f0d77"),
            CURLOPT_POSTFIELDS        => array("file" => "@/Users/chris/Sites/11202/var/log/system.log"),
            CURLOPT_URL               => $trackerUploads,
            CURLOPT_RETURNTRANSFER    => 1,
            ));

            // Ship IT! and capture the response
            $response = curl_exec($ch);

            // For Sanity when debugging let's show the response.
            echo $response;

            // Shut er down
            curl_close($ch);
            };
             */


            // ----------
            // addLabels
            // -----
            // Add a label to an existing story.
            public function addLabels($story, $labels) {

            // Make the fields safe
            $story = escapeshellcmd($story);
            $labels = escapeshellcmd($labels);

            // Create the new task
            $cmd = "curl -H \"X-TrackerToken: {$this->token}\" "
                . "-X PUT -H \"Content-type: application/xml\" "
                . "-d \"<story><labels>$labels</labels></story>\" "
                . "https://www.pivotaltracker.com/services/v3/projects/{$this->project}/stories/$story";
            $xml = shell_exec($cmd);

        }

        // ---------
        // getStories
        // -----
        // Get a list of stories from a project, optional filter
        public function getStories($token, $project, $state = '', $filter = 'state:unscheduled,unstarted,started,finished,delivered,rejected') {

            // Encode the filter
            $filter = urlencode($filter);

            // Make the fields safe
            $filter = escapeshellcmd($filter);
            $project = escapeshellcmd($project);

            // Request the stories
            $cmd = "curl -s -H \"X-TrackerToken: $token\" "
                . "-X GET "
                . "https://www.pivotaltracker.com/services/v5/projects/$project/stories";
            // Add the filter, if it was specified
            if ($state == 'true') $cmd .= "?with_state=unstarted";
            if ($filter != '') $cmd .= "?filter=$filter";
            $json = shell_exec($cmd);

            // Return an array
            $json_arrays = json_decode($json,true);
            foreach ($json_arrays as $json_array) {
                echo "    " . $json_array['id'] . " | "
                    . $json_array['current_state'] . " | "
                    . $json_array['story_type'] . " | "
                    . $json_array['estimate'] . " | "
                    . $json_array['name'] . "\n";
            }
            // Return an object
            return false;

        }

        // ---------
        // getProjects
        // -----
        // Get a list of your projects
        public function getProjects($token) {

            // Request the projects
            $cmd = "curl -H \"X-TrackerToken: $token\" "
                . "-X GET "
                . "https://www.pivotaltracker.com/services/v3/projects";
            $xml = shell_exec($cmd);

            // Return an object
            $projects = new SimpleXMLElement($xml);
            return $projects;

        }

        // ----------
        // getToken
        // -----
        public function getToken() {

            // Let's ask the user to login to tracker.
            echo "\n    Login to tracker and we will create a tokenfile";
            fwrite(STDOUT, "\n    username: ");
            $username = (trim(fgets(STDIN)));
            fwrite(STDOUT, "    password: ");
            system('stty -echo');
            $password = trim(fgets(STDIN));
            system('stty echo');
            echo "\n";

            // Make the fields safe
            $username = escapeshellcmd($username);
            $password = escapeshellcmd($password);

            // Request the token
            $cmd = "curl -s -u $username:$password "
                . "-X GET "
                . "https://www.pivotaltracker.com/services/v5/me";
            $json = shell_exec($cmd);

            // Return an array
            $json_array = json_decode($json,true);
            $token = $json_array['api_token'];

            // Create a "yet another .file" that contains the users token
            $helpers = new pivotalTrackerHelpers();
            $helpers->createTokenFile($token);

            // Tell the lucky user their token has been saved
            echo "    Hey, we saved your token to a file here " . $helpers->tokenFile() . "!\n";

            return false;

        }


        // ----------
        // getProjectActivity
        // -----
        //Get Activity Feed of a project. Number of activities is 50 by default
        public function getProjectActivity($project, $limit=50) {

            // Make the fields safe
            $project = escapeshellcmd($project);

            $cmd = "curl -H \"X-TrackerToken: {$this->token}\" "
                . "-X GET "
                . "https://www.pivotaltracker.com/services/v3/projects/$project/activities?limit=$limit";
            $xml = shell_exec($cmd);

            $activity = new SimpleXMLElement($xml);
            return $xml;
        }


        // ----------
        // getMyRecentProjects
        // -----
        //Creates a list of projects that you have recently worked on
        public function getMyRecentProjects($token) {

            // Let's set the limit to activity in the last week.
            date_default_timezone_set('UTC');
            $format = 'Y-m-d\TH:i:s';
            $date = date ($format);
            $limit = date($format, strtotime ( '-30 day' . $date ) );
            //echo $limit;

            $cmd = "curl -s -H \"X-TrackerToken: $token\" "
                . "-X GET "
                . "https://www.pivotaltracker.com/services/v5/my/activity?occurred_after=$limit";
            $json = shell_exec($cmd);

            // Return an array
            $json_arrays = json_decode($json,true);
            $tProjects=array();
            foreach ($json_arrays as $json_array) {
                $idx = $json_array['project']['name'];
                    if (!isset($tProjects[$idx])) $tProjects[$idx]=$json_array;
            }
            $tProjects = array_values($tProjects);
            foreach ($tProjects as $tProject)
            {
                echo "    " . $tProject['project']['id'] . " - " . $tProject['project']['name'] . "\n";
            }
            return false;
        }

        // ----------
        // getProject
        // -----
        //Returns the value of the filed specified fro the given project id
        public function getProject($token, $pId, $field){

            $cmd = "curl -s -H \"X-TrackerToken: $token\" "
                . "-X GET "
                . "https://www.pivotaltracker.com/services/v5/projects/$pId";
            $json = shell_exec($cmd);

            // Return an array
            $json_array = json_decode($json,true);
            $value = $json_array[$field];
            return $value;
        }

    }
?>