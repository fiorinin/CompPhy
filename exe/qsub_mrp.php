
<?php
$cmdstr = SYSTEMPATH . "qstat.sh j";
$nb_process = exec ($cmdstr, $output, $return_var);
if ($return_var != 0)
  $nb_process = 0;
if (MAX_PROCESS_NB == 0 || $nb_process > MAX_PROCESS_NB) {
  //echo "<br />The server is currently unavailable.";
  //exit();
  // => a voir : pour NICO !!!!!!!!!!!!  
}

$MAX_FILE_SIZE=1000000;

//vERIFIER AVEC NICO RECUP IP
$IPaddress = $_SERVER['REMOTE_ADDR'];

$Analysis = 'compphy-mrp';

$NbJobsUser = getNbRunPerUserEmail (RUNNING_JOBS_PATH, $IPaddress);
if ($NbJobsUser >= MAX_RUNNING_JOBS_PER_IP) {
  /* echo "<br /><br />
        You have already launched $NbJobsUser analyses.<br />
        Please wait for their end before submitting other ones.<br /><br />
        <div class=\"warning\">Operation aborted</div>";
  exit();*/
   // => a voir : pour NICO !!!!!!!!!!!!  
}


$filesize = filesize ($datafile);
if ($filesize > $MAX_FILE_SIZE) {
//  echo "<br /><br />The Newick tree file is too big.<br />Operation aborted.";
//  exit();
// => A VOIR POUR NICO
}

//echo "<br /><br />The Newick tree file was uploaded.";
// => A VOIR POUR NICO



// informations supplémentaires à logger
$logstr = "EXECUTION|IP $IPaddress";

$execpath = SYSTEMPATH . 'run.sh';
$binpath = BINPATH . 'mrp.sh';

$OK = TRUE;
$handle = fopen ($mrpdir . "cmd.txt","w");
$str = "SCRIPT::" . $execpath . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "BINARY::" . $binpath . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "BINPATH::" . BINPATH . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "OPTIONS::" . $mrpdir . "cmd.txt" . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "INPUT::" . $datafile . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "LOGS::" . $logstr . "\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "PROG::CompPhy-MRP\n";
if (fwrite($handle, $str) === FALSE) {
  $OK = FALSE;
}
$str = "DIR::" . $mrpdir . "\n";
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
fclose($handle);

if (!$OK) {
  Navigate::redirectMessage("project", "Error while creating temporary files. Please contact the administrator.", 2, $projet->getId());
}

// execute the program
#$cmdstr = SYSTEMPATH . "qsub.sh " . $mrpdir . "cmd.txt > " . $mrpdir . "exec_trace.txt 2>&1 &";
$cmdstr = SYSTEMPATH . "qsub.sh " . $mrpdir . "cmd.txt > " . $mrpdir . "exec_trace.txt 2>&1";
exec($cmdstr, $output, $return_var);

if ($return_var != 0) {
    $errors .= "Unexpected error during job submission.<br>";
}

?>

