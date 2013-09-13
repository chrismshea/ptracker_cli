<?php
/**
 * Created with pain sweat, and tears.
 * User: chris
 * Date: 8/29/13
 * Updated: 9/7/13 - Chris Shea "Adding comments"
 *
 *
 * CONTENTS:
 * This file takes a command line user through the process of:
 *     1. Establishing a connection with Pivotal Tracker
 *     2. Determining the project we are contributing too
 *     3. Deciding which story or add a new one
 *     4. Walk the user through adding all of the needed details to create a story.
 *     5. If they are adding a bug allow them to upload all of the log files that are found.
 *
 *
 * NEXT STEPS:
 * Feature Requests:
 *     1. Make sure all Story fields do not need to be answered.
 *     2. Create a function for logify
 *
 * Bugs:
 *     1. Currently bug creation does not work when using logify
 *
 */
?>
<?php

// Load the classes
// Loads the basic class to communicate with Pivotal Tracker
require ('pivotaltracker.php');
// Loads some tasks that fall outside of specifically communicating with Tracker
require ('helpers.php');

$pivotaltracker = new pivotaltracker;
$helpers = new pivotalTrackerHelpers;


/**
 *
 * How about a brief introduction before we begin.
 *
 */

    // Let's set the stage, and hope that the user is paying attention to the output on the screen.
        echo  "\nUPDATING PIVOTAL TRACKER\n"
            . "    Please read and understand all output\n"
            . "    www.pivotaltracker.com\n";


/**
 *
 * Before we begin let's make sure the user has a token
 *
 */

    // Our main objective of this section is to obtain the User's token
        $token = file_get_contents($helpers->tokenFile());

    // Does our .pivotaltoken file exist?
        if (!file_exists($helpers->tokenFile())) {
            $pivotaltracker->token = $pivotaltracker->getToken();
        }
        echo ($helpers->displayToken($token));


/**
 *
 * Let's start with the project we are contributing to
 *
 */

    // First we establish the location of the .git directory.  This tells us where to start looking for the Pivotal Tracker project id in the .git/hooks/prepare-commit-msg

        exec('git rev-parse --show-toplevel 2> /dev/null', $output);
        $pRoot = $output[0];

        echo ("\nPROJECT:\n"
            . "    Project directory: " .$pRoot . "\n");

    // If the prepare-commit-msg does not exist let's run the hooks command

        if (!file_exists($helpers->hookFile($pRoot))) {
            echo "\nChoose from the following projects: \n";
            echo $pivotaltracker->getMyRecentProjects($token);
            exec('hooks');
        }

        $project = $helpers->getFileContents($helpers->hookFile($pRoot), 'project');
        $pName = $pivotaltracker->getProject($token,$project,'name');
        echo "\n" . "Using project " . $project . " - " . $pName . "\n";
die;
        // Could we get by with this alone do we need to set $project at each statement?
            $project = $helpers->getFileContents('.git/hooks/prepare-commit-msg', 'project');

/**
 * Are you updating a story or creating a new one?
 *
 * At this point we have the basics to interact with tracker.
 * We have their token setup, and we have the project id they will be contributing too.
 */

    // Let's go ahead and show them their stories which are not accepted
        echo "\n" . $cStories = $pivotaltracker->getStories($token,$project);
        fwrite(STDOUT, "\nChoose one of the story id's or enter 'new':\n");
        $sOption = (trim(fgets(STDIN)));

    // If they choose to create a new story let's they ask them which type of story
        if ($sOption == 'new'){
        fwrite(STDOUT, "\nStory Type (feature, bug, chore, release):\n");
        $sType = (trim(fgets(STDIN)));

        // If they want to create a bug we will ask them if they would like to include all of the log files
            if ($sType == 'bug'){
            fwrite(STDOUT, "\nDo you want to attach all logs?:\n");
            $sLogify = (trim(fgets(STDIN)));

            // If they want to include all of the logs this step will upload them to the project for processing later
                if ($sLogify == 'yes'){
                    $sLogs = glob("$pFolder/var/{log,report}/*", GLOB_BRACE);
                    $sLogs[] = "/var/log/apache2/error.log";
                    echo "Uploading:" . " ";
                    print_r($sLogs);
                    $pUploads = $pivotaltracker->addUploads($token,$project,$sLogs);
                    foreach($pUploads as $val) {print $val;}
                }

            // So I don't think we need this here.  Dennis?
                else {return true;}
            }

        // And I think that applies to this also.
            else {return true;}

        // Let's finish collecting information for the remaining areas of the story.
            fwrite(STDOUT, "\nStory Name:\n");
            $sName = (trim(fgets(STDIN)));
            fwrite(STDOUT, "\nStory Desc:\n");
            $sDesc = (trim(fgets(STDIN)));
            fwrite(STDOUT, "\nStory Comment:\n");
            $sComm = (trim(fgets(STDIN)));
            $story = $pivotaltracker->addStory("$token", "$project", "$sType", "$sName", "$sDesc", "$sComm", "$pUploads");
            echo "Created storyId: " . $story;
        }

    // Ok they have chosen to contribute to an existing story. Let's set $story as that chosen.
        else {$story = $sOption;}

    //Status

    //Comment

    //Tasks

    //Attachment

        $pivotaltracker->updateStory($token,$project,$story,$sStatus,$sComment,$sTask,$sAttachment);
        echo "Updated storyId: " . $story;

echo "\nWhat happened?\n";
die;

?>