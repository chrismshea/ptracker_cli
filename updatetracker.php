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
 *     1. Currently story creation does not work
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
 * WELCOME
 *
 */
    // Let's set the stage, and hope that the user is paying attention to the output on the screen.
        echo  "\nUPDATING PIVOTAL TRACKER\n"
            . "    Please read and understand all output\n"
            . "    www.pivotaltracker.com\n";


/**
 *
 * ENSURE WE HAVE A TOKEN
 *
 */
    // Does our .pivotaltoken file exist?
        if (!file_exists($helpers->tokenFile()))
        {
            $pivotaltracker->token = $pivotaltracker->getToken();
        }
        $token = file_get_contents($helpers->tokenFile());
        echo ($helpers->displayToken($token));


/**
 *
 * WHAT PROJECT ARE WE USING
 *
 */
    // First we establish the location of the .git directory.  This tells us where to start looking for the Pivotal Tracker project id in the .git/hooks/prepare-commit-msg

        exec('git rev-parse --show-toplevel 2> /dev/null', $output);
        $pRoot = $output[0];

        echo ("\nPROJECT:\n"
            . "    Project directory: " .$pRoot . "\n");

    // If the prepare-commit-msg does not exist let's run the hooks command

        if (!file_exists($helpers->hookFile($pRoot)))
        {
            echo "    Please choose project id:\n"
                ."    ----------------------------------------\n\n";
            echo $pivotaltracker->getMyRecentProjects($token) . "\n"
                ."    ----------------------------------------\n";
            exec('hooks');
        }
        $pId = $helpers->getFileContents($helpers->hookFile($pRoot), 'project');
        $pName = $pivotaltracker->getProject($token,$pId,'name');
        echo "    Project Name/Id: " . $pName . " - " . $pId . "\n";


/**
 *
 * STORY TIME
 *
 */

    // Let's go ahead and show them their stories which are not accepted
        echo "\nSTORIES:\n";
        echo $cStories = $pivotaltracker->getStories($token,$pId);
        fwrite(STDOUT, "\n    Story id [story_id/new]: ");
        $sOption = (trim(fgets(STDIN)));

    // If they choose to create a new story we'll get the required information

        // Establishing these variables before the if

        if ($sOption == 'new')
        {
            fwrite(STDOUT, "\n    Story type [feature/bug/chore/release]: ");
            $sType = (trim(fgets(STDIN)));

            fwrite(STDOUT, "\n    Story Name: ");
            $sName = (trim(fgets(STDIN)));

            fwrite(STDOUT, "\n    Story Desc: ");
            $sDesc = (trim(fgets(STDIN)));

        // We have collectetd the information now create a new story.
            $sInfo = $pivotaltracker->addStory("$token", "$pId", "$sType", "$sName", "$sDesc");
            $sId = $sInfo[sId];
            $sUrl = $sInfo[sUrl];
            echo "\n    Created story: " . $sId
                ."\n    At: " . $sUrl . "\n";

        // If they want to create a bug we will ask them if they would like to include all of the log files
            if ($sType == 'bug')
            {
                fwrite(STDOUT, "\n    Attach logs [yes/no]: ");
                $sLogify = (trim(fgets(STDIN)));

            // If they want to include all of the logs this step will upload them to the project for processing later
                if ($sLogify == 'yes')
                {
                    $sLogs = glob("$pRoot/var/{log,report}/*", GLOB_BRACE);
                    $sLogs[] = "/var/log/apache2/error.log";
                    $pUploads = $pivotaltracker->addUploads($token,$pId,$sLogs);
                    $sComm = "Attaching magento and server logs";

                // Convert Uploads to a String and attach with comment
                    $pUploadsString = implode(",",$pUploads);
                    $cResult = $pivotaltracker->addAttachments($token,$pId,$sId,$sComm,$pUploadsString);
                    echo "\n    Comment: " . $cResult . "\n";
                }
            }
        }
    // Ok they have chosen to contribute to an existing story.
        else
        {
            $sId = $sOption;
        // Collect a comment for the story
            fwrite(STDOUT, "\n    Story Comment: ");
            $sComm = (trim(fgets(STDIN)));
            $cResult = $pivotaltracker->addComment($token,$pId,$sId,$sComm);
            echo "    Updated storyId: " . $sId . "\n"
                ."    With comment: " . $cResult . "\n";
        }
// Add Tasks
//fwrite(STDOUT, "\n    Story Comment: ");
//$sTask = (trim(fgets(STDIN)));
// Add an attachment?
//fwrite(STDOUT, "\n    Story Comment: ");
//$sAttachment = (trim(fgets(STDIN)));
?>