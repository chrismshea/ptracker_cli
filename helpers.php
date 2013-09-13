<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chris
 * Date: 9/1/13
 * Time: 8:53 PM
 * To change this template use File | Settings | File Templates.
 */
?>
<?php

class pivotalTrackerHelpers {

    public function tokenFile() {
        $home = getenv("HOME");
        $tokenfile =($home . "/" . '.pivotaltoken');
        return $tokenfile;
    }

    public function createTokenFile($token) {
//        $tokenfile = $this->tokenFile();
        $pivotaltoken = fopen($this->tokenFile(), 'w');
        fwrite($pivotaltoken, $token);
        fclose($pivotaltoken);
        return false;
    }
    public function getFileContents($file, $field){
        $lines = file($file);
        foreach (array_values($lines) AS $line) {
            list ($key, $val) = explode('=', trim($line) );
            if (trim($key) == $field) {
                return $val;
            }
        }
        return false;
    }
    public function displayToken($token){
        echo ("\nTOKEN INFO:\n"
            . "    Token: " . $token . "\n"
            . "    Found in: " . $this->tokenFile() . "\n");
        return false;
    }
    public function hookFile($pRoot) {
        $hFile = $pRoot . "/" . '.git/hooks/prepare-commit-msg';
        return $hFile;
    }
}
?>