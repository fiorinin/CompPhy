<?php
  // Server variables
  define ('ROOT', ''); // URL of the project
  define ('ROOTPATH', ''); // Path of HTTP websites (e.g. /var/www/)
  define ('HEREPATH', '');  // Path of the project, here /var/www/compphy/, like COMPPHYROOT
  define ('BINPATH', ''); // Path of the perl scripts, here it's the bin/ directory at the root of the repository
  define ('WS', ''); // URL of the ScripTree webservice for generating trees
  define ('WSPATH', ''); // Path of the ScripTree webservice for generating trees
  define ('COMPPHYROOT', ''); // Path of CompPhy
  define ('COMPPHYROOTWEB', ''); // URL of CompPhy
  set_include_path (get_include_path() . PATH_SEPARATOR . COMPPHYROOT . 'classes/'); // include all classes

  // Design definitions
  define ('TOOLS', (file_exists ('includes/tools.php')) ? HEREPATH . 'includes/tools.php' : '');
  define ('USERMENU', (file_exists ('html/usermenu.html')) ? HEREPATH . 'html/usermenu.html' : '');
  define ('HEADERC', is_dir (COMPPHYROOT .'html/') ?  COMPPHYROOT .'html/header.html' : ROOTPATH .'include/header.html');
  define ('FOOTERC', is_dir (COMPPHYROOT .'html/') ?  COMPPHYROOT .'html/footer.html' : ROOTPATH .'include/header.html');

  // Limits
  define ('MAXTREES', 20000); // Number of tree per project
  define ('MAXTAXA', 5000); // Number of taxa per project
  define ('MAXTREEPOST', 1000); // Number of trees per import
  
  //DB
  define ("DBHOST", "");
  define ("DBPORT", "");
  define ('DB', '');
  define ('USER', '');
  define ('PASS', '');

  // Python for MRP
  putenv ("PYTHONPATH=". BINPATH ."MRP/:". BINPATH ."MRP/nm/lib/python2.6/site-packages/:". BINPATH ."MRP/spruce/lib/python2.6/site-packages/");

?>
