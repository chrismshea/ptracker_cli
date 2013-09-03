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
        if ($sType == 'bug'){
            fwrite(STDOUT, "\nDo you want to attach all logs?:\n");
            $sLogify = (trim(fgets(STDIN)));
            if ($sLogify == 'yes'){
                $sLogs = glob("$pFolder/var/{log,report}/*", GLOB_BRACE);
                $sLogs[] = "/var/log/apache2/error.log";
                echo "Uploading:" . " ";
                print_r($sLogs);
                $pUploads = $pivotaltracker->addUploads($token,$project,$sLogs);
            } else {
                return true;
            }
        } else {
            return true;
        }
        fwrite(STDOUT, "\nStory Name:\n");
        $sName = (trim(fgets(STDIN)));
        fwrite(STDOUT, "\nStory Desc:\n");
        $sDesc = (trim(fgets(STDIN)));
        fwrite(STDOUT, "\nStory Comment:\n");
        $sComm = (trim(fgets(STDIN)));
        $story = $pivotaltracker->addStory("$token", "$project", "$sType", "$sName", "$sDesc", "$sComm", "$pUploads");
        echo "Created storyId: " . $story;
    } else {
        $story = $sOption;
        //Logify Yes / No
        if ($sLogify == 1){
            echo "ugh";
        } else {
        //Status
        //Comment
        //Tasks
        //Attachment
        }
        $pivotaltracker->updateStory($token,$project,$story,$sStatus,$sComment,$sTask,$sAttachment);
        echo "Updated storyId: " . $story;
    }

echo "\nWhat happened?\n";
die;

?>