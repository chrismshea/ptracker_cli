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
        public function addStory($token, $project, $sType, $sName, $sDesc) {

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
                . "\"https://www.pivotaltracker.com/services/v5/projects/$project/stories\"";
            $json = shell_exec($cmd);

            // Return an object
            $json_array = json_decode($json,true);
            $story = $json_array['id'];
            return $story;

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
        public function getStories($token, $project, $filter = '') {

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
            if ($filter != '') $cmd .= "?filter=$filter";
            $json = shell_exec($cmd);

            // Return an array
            $json_arrays = json_decode($json,true);
            foreach ($json_arrays as $json_array) {
                echo $json_array['id'] . " | " . $json_array['story_type'] . " | " . $json_array['name'];
                echo "\n";
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
            $username = trim(shell_exec("read -p 'username: ' username\necho \$username"));
            echo "password: ";
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
            $token = $json_array[api_token];

            // Create a "yet another .file" that contains the users token
            $helpers = new pivotalTrackerHelpers();
            $helpers->createTokenFile($token);

            // Tell the lucky user their token has been saved
            echo "Hey, we saved your token to a file here " . $helpers->tokenFile() . "!\n";

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
            $limit = date($format, strtotime ( '-7 day' . $date ) );
            //echo $limit;

            $cmd = "curl -s -H \"X-TrackerToken: $token\" "
                . "-X GET "
                . "https://www.pivotaltracker.com/services/v5/my/activity?occurred_after=$limit";
            $json = shell_exec($cmd);

            // Return an array
            $json_arrays = json_decode($json,true);
            foreach ($json_arrays as $json_array) {
                echo $json_array['project']['name'] . " - " . $json_array['project']['id'];
                echo "\n";
            }
            return false;
        }

        // ----------
        // getProject
        // -----
        //Returns the value of the filed specified fro the given project id
        public function getProject($token, $project, $field){

            $cmd = "curl -s -H \"X-TrackerToken: $token\" "
                . "-X GET "
                . "https://www.pivotaltracker.com/services/v5/projects/$project";
            $json = shell_exec($cmd);

            // Return an array
            $json_array = json_decode($json,true);
            $value = $json_array[$field];
            return $value;
        }

    }
?>