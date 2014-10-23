
<?php
$cmdstr = SYSTEMPATH . "qstat.sh j";
$nb_process = exec ($cmdstr, $output, $return_var);
if ($return_var != 0)
  $nb_process = 0;
if (MAX_PROCESS_NB == 0 || $nb_process > MAX_PROCESS_NB) {
  //echo "<br />The server is currently unavailable.";
  //exit();
  // => a voir : pour NICO !!!!!!!!!!!! 
    $errors .= "The server is currently unavailable. Operation aborted.<br>";
    // *Nico* : fait 
}

$MAX_FILE_SIZE=1000000;

//vERIFIER AVEC NICO RECUP IP
$IPaddress = $_SERVER['REMOTE_ADDR'];

$Analysis = 'compphy-physicist';

$NbJobsUser = getNbRunPerUserEmail (RUNNING_JOBS_PATH, $IPaddress);
if ($NbJobsUser >= MAX_RUNNING_JOBS_PER_IP) {
  /* echo "<br /><br />
        You have already launched $NbJobsUser analyses.<br />
        Please wait for their end before submitting other ones.<br /><br />
        <div class=\"warning\">Operation aborted</div>";
  exit();*/
   // => a voir : pour NICO !!!!!!!!!!!!  
    $errors .= "You have already launched $NbJobsUser analysis. Please wait for their end before submitting other ones.<br>";
    // *Nico* : fait 
}

// directory where all the execution files are copied
$now = date ('Ymd-His');
$uploaddir = EXECPATH . $now . "_" . genStr() . "/";
while (is_dir ($uploaddir)) {
  $uploaddir = EXECPATH . $now . "_" . genStr() . "/";
}
if (!mkdir ($uploaddir)) {
  /*echo "<br /><br />Unable to create the temporary execution directory.";
  echo "<br />Please contact the server administrator. Operation aborted";
  exit();*/
// => a voir : pour NICO !!!!!!!!!!!! 
    $errors .= "Unable to create the temporary execution directory. Please contact the server administrator.<br>";
    // *Nico* : fait  
}
else
  chmod ($uploaddir, 0774);

$nwk = '';
foreach($trees as $key => $value) {
        $nwk .= $value->getNewick()."\n";
}
$filename = 'sourceTrees.nwk';

$dataset = $uploaddir . $filename;

//copie de $nwk dans $dataset
file_put_contents ($dataset, $nwk);
chmod ($dataset, 0644);

$filesize = filesize ($dataset);
if ($filesize > $MAX_FILE_SIZE) {
  error_log("The Newick tree file is too big.");
//  exit();
// => A VOIR POUR NICO
    $errors .= "The Newick tree file is too big. Please select fewer trees.<br>";
    // *Nico* : fait
}

//echo "<br /><br />The Newick tree file was uploaded.";
// => A VOIR POUR NICO


// options de la ligne de commande
$cmdstr = '-s ' . $dataset;
if ($bootstrap) {
  $cmdstr = $cmdstr . ' -b ' . $bootstrap;
}
if ($correction) {
  $cmdstr = $cmdstr . ' -c ' . $correction;
}
$cmdstr = $cmdstr . ' -f ' . $dataset . '_newforest.txt';
$cmdstr = $cmdstr . ' -o ' . $dataset . '_supertree.txt';
// informations supplémentaires à logger
$logstr = "EXECUTION|IP $IPaddress";

$execpath = SYSTEMPATH . 'run.sh';
$binpath = BINPATH . '../physic_ist/physic_ist.sh';

$OK = TRUE;
$handle = fopen ($uploaddir . "cmd.txt","w");
$str = "SCRIPT::" . $execpath . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "BINARY::" . $binpath . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "BINPATH::" . BINPATH . "../physic_ist/\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "CMDSTR::" . $cmdstr . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "OPTIONS::" . $uploaddir . "cmd.txt" . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "INPUT::" . $dataset . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "LOGS::" . $logstr . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "PROG::CompPhy-PhySIC_IST\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "DIR::" . $uploaddir . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = MAX_RUNNING_DAYS;
$str = "MAX_RUNNING_DAYS::" . $str . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "USER_IP::" . $IPaddress . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = RUNNING_JOBS_PATH;
$str = "RUNNING_JOBS_PATH::" . $str . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = LOGPATH;
$str = "LOGPATH::" . $str . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "ANALYSIS::" . $Analysis . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "WS::" . WSPATH . "scriptree-client.php\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
fclose($handle);

if (!$OK) {
  Navigate::redirectMessage("project", "Error while creating temporary files. Please contact the administrator.", 2, $projet->getId());
}

// execute the program
#$cmdstr = SYSTEMPATH . "qsub.sh " . $uploaddir . "cmd.txt > " . $uploaddir . "exec_trace.txt 2>&1 &";
$cmdstr = SYSTEMPATH . "qsub.sh " . $uploaddir . "cmd.txt > " . $uploaddir . "exec_trace.txt 2>&1";
exec($cmdstr, $output, $return_var);

if ($return_var != 0) {
    $errors .= "Unexpected error during job submission.<br>";
}

?>

