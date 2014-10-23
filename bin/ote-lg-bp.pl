#!/usr/bin/perl

# expect a input file containing trees in Newick format with branch lengths and/or support values, as in:
#    ...(aaa:0.1,bbb:0.012)50:0.23.... or ...(aaa:1.2E-2,bbb:0.012)0.9:0.23.... 
# returns a tree without branch lengths



open F, $ARGV[0];while (<F>) {$arbre .= $_ ;} close F;


$arbre =~ s/[^\w\.;:\,\(\)]//g; # removes stranges characters (such as ^M, etc)
$arbre =~ s/\n//g; 


#if ($arbre =~ /\)([0-9\.]*)([:,;\)])/) {print "trouve : $1 $2\n"; exit(1);}

# removes support values if any  (not working as a substitution with flag g !!!)
while ($arbre =~ /\)([0-9Ee+\-\.]+)([:,;\)])/) {
	$arbre =~ s/\)([0-9Ee+\-\.]+)([:,;\)])/)$2/ ;}
#print $arbre;
# exit(1);


# removes branch lengths if any
$arbre =~ s/(:[\.0-9eE\-\+]+)//g;
$arbre =~ s/;\(/;\n(/g; # one tree output on each line
print $arbre,"\n";
