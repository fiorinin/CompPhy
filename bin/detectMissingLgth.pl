#!/usr/bin/perl
# V.Berry as of August 22th 2011.
#################################################
# GOAL: detect whether a tree has some lengths but not all of them
#
# INPUT: 
# - a file containing ROOTED trees in Newick format

# OUTPUT 
# - STANDARD output = "OK" 
# or 
# - ERROR output = error message

# Trees can contain polytomies or multiple occurences of a same taxa 
# (multi-labeled trees).


###################################################
# Global variables

use strict;

my @arb;
##################################
sub verbose { }# for (@_) {print;} } #  

##################################################
sub CheckAllLgth {
 verbose "Check all lengths\n";
  my $tree = $_[0];
#  print "$tree\n";exit;
  # Checks if taxa have lengths
  while ($tree =~ /[\(,]\w/) {
  	$tree =~ s/([\(,])\w[^:,\)]+(.)/$1#$2/;
#  	verbose "REGEXP : ---$1--- ---$2---\n";
    #verbose "$tree\n";
  	die "Error: a tree must contain all or no branch length.\nCorrect the tree before rerooting (eg, via the 'manual tuning of the picture' feature)\n" unless ($2 eq ':');
  }
  # Checks if clades have lengths
  	# 1 - rmv supports
  while ($tree =~ /\)[0-9]/) { #print "$tree\n";
  	$tree =~ s/\)[0-9\.eE\+\-]+([:,\)])/\)$1/;
  }
  verbose "Tree without supports :\n$tree\n";
    # 2 - check if a closing bracket has no length behind
  die "Error: tree must contain all or no branch length" if ($tree =~ /\)[\),]/);
  verbose "c'est ok\n";
}
########################################################################################
########################################################################################
###############											################################
###############				 MAIN				 		################################
###############											################################
########################################################################################
########################################################################################
# params = names of files containing trees
die "\n\tUsage: $0 infile\n\n" if ($#ARGV!=0);


# Reads and examines trees

my $inTree=0; # nb of input trees
my $fic = shift @ARGV;
    
    open F,$fic or die "Cannot open $fic\n";
    verbose "Reading file $fic\n";
    my $arbre;
    while (<F>) {$arbre .= $_ ;} 
    close F;
    $arbre =~ s/[^-+\w\.;:\,\(\)]//g; # vire tout ce qui n'est pas attendu (dont ^M, etc)

	# rmv support value at root (PBIL trees) -> source of bug, and such a value has no meaning
		$arbre =~ s/\)[0-9][0-9eE\.\+\-]*;/);/g;

    $arbre =~ s/\n//g; chop $arbre; # enleve le ; final sinon arb fantome
    my @arb = split /;/, $arbre;
    verbose "This file contains ", 1+$#arb," trees to process\n";

    for ($a=0;$a<=$#arb;$a++) {
       $arb[$a] =~ s/\s+ //g;    # vire espaces de la chaine si y en a
       
       if ($arb[$a]=~ /\:/) { # tree has at least one branch length
       		CheckAllLgth $arb[$a] ;
       }       

       # Check if correct numbers of opening and closing brackets
#        $tmp = $arb[$a];
#        $openNb = ($tmp =~ tr/(//); $closeNb = ($tmp =~ tr/)//);
#        die "Error: unmatching numbers of brackets in Newick format of tree\n" unless ($openNb == $closeNb);

 		
     }
    #print $#arb+1," trees processed\n";

print "OK\n";