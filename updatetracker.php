<?php
/**
 * Created with pain sweat, and tears.
 * User: chris
 * Date: 8/29/13
 * Time: 3:29 PM
 * To change this template use File | Settings | File Templates.
 */
?>
<?php

// Load the classes
require ('pivotaltracker.php');
require ('helpers.php');

$pivotaltracker = new pivotaltracker;
$helpers = new pivotalTrackerHelpers;

/**
 * Before we begin let's make sure the user has a token
 */
    echo "\nPivotal Updates Begin, Please Read and Understand All Output\n";
    // Does our .pivotaltoken file exist?
    if (file_exists($helpers->tokenFile())) {
        echo ("\nUsing the token found in " . $helpers->tokenFile() . "\n");
    }
    else {
        // Retrieve token from PivotalTracker and create the ~/.pivotaltoken file
        $pivotaltracker->token = $pivotaltracker->getToken();
    }

    // Set token for use
    echo $token = file_get_contents($helpers->tokenFile());

/**
 * Let's start with the project we are contributing to
 */
    // if exists .git/hooks/prepare-commit-msg return the $PROJECT variable

    if (file_exists('.git/hooks/prepare-commit-msg')) {
        $project = $helpers->getFileContents('.git/hooks/prepare-commit-msg', 'project');
        $pName = $pivotaltracker->getProject($token,$project,'name');
        echo "\n" . "Using project " . $project . " - " . $pName . "\n";
        $pFolder = getcwd();
    }
    else {
        fwrite(STDOUT, "\nEnter the path to your project folder: ");
        $pFolder = trim(fgets(STDIN));
        if (file_exists("$pFolder/.git/hooks/prepare-commit-msg")) {
            $project = $helpers->getFileContents("$pFolder/.git/hooks/prepare-commit-msg", 'project');
            $pName = $pivotaltracker->getProject($token,$project,'name');
            echo "\n" . "Using project " . $project . " - " . $pName . "\n";
        }
        else {
            echo "Here are some of your recent projects: \n";
            echo $pivotaltracker->getMyRecentProjects($token);
            shell_exec("hooks");
        }
        $project = $helpers->getFileContents('.git/hooks/prepare-commit-msg', 'project');
    }

/**
 * Are you updating a story or creating a new one?
 */
    echo "\n" . $cStories = $pivotaltracker->getStories($token,$project);
    fwrite(STDOUT, "\nChoose one of the story id's or enter 'new':\n");
    $sOption = (trim(fgets(STDIN)));
    if ($sOption == 'new'){
        fwrite(STDOUT, "\nStory Type (feature, bug, chore, release):\n");
        $sType = (trim(fgets(STDIN)));
        fwrite(STDOUT, "\nStory Name:\n");
        $sName = (trim(fgets(STDIN)));
        fwrite(STDOUT, "\nStory Desc:\n");
        $sDesc = (trim(fgets(STDIN)));
        $story = $pivotaltracker->addStory("$token", "$project", "$sType", "$sName", "$sDesc");
    } else {
        $story = $sOption;

    }
echo "Created storyId: " . $story;

echo "\nWhat happened?\n";
die;

// Let's establish some of the resources that we will need.
$mage_logs = glob("/Users/chris/Sites/{$siteDir}/var/{log,report}/*", GLOB_BRACE);
    $mage_logs[] = "/var/log/apache2/error.log";

    // We will first upload the attachments to the project
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
?>