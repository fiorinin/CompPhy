#!/usr/bin/perl
# V.Berry as of August 22th 2011.
#################################################
# INPUT: 
# - a first param indicating whether Lengths and/or Supports are to be added (L, S, or LS)
# - a file containing ROOTED trees in Newick format

# OUTPUT 
# - STANDARD output = same trees with FAKE supports for clades without support 
# and with FAKE branch lengths for those having none.
# - FILE fake.tmp containing a line for each source tree.
#   Each line contains the fake support value and the fake branch length value used for this tree
#   (this value is chosen so as not already appearing in this tree) or the word "NONE" if none was necessary
#   SPECIAL CASE: when fake.tmp contains just one line with "***" then this means all trees were originally complete
#   and nothing was to be done.


# Trees can contain polytomies or multiple occurences of a same taxa 
# (multi-labeled trees).


###################################################
# Global variables
my (@arb, @fakeSupp, @fakeLgth, @modif);
##################################
sub verbose { }# for (@_) {print;} } #  
################################
sub addFakeLgth { # add fake length to taxa and clades having no length.
# Pay attention to the fact that support values may however be present as in 
# (aaa,bbb)support,(cccc,ddd)support);
#
    verbose "Adding fake lengths\n";
    my ($c,$v) = @_;
    
    # BEWARE =~s//g  <--with option g:  does not work properly here for some reason
    
    # add fake lengths to clades without support ie '),' or '))' -> '):fakeVal,' or '):fakeVal)'
	while ($c =~ /\)\)/) {
		$c =~ s/\)([,\)])/):$v$1/; }
    # add fake lengths to clades with support
    while ($c =~ /\)([0-9\.eE\+\-]*)([,\)])/ ) {     # * as there may be no support
	    $c =~ s/\)([0-9\.eE\+\-]*)([,\)])/)$1:$v$2/; }  # idem
	# add fake lengths to taxa
	while ($c =~ /([\(,])([^:,\)]+)([,\)])/ )  
		{ $c =~ s/([\(,])([^:,\)]+)([,\)])/$1$2:$v$3/; }

	# add fake length to root
	$c =~ s/\)([^:\)]*)$/\)$1:$v/;  # put a length at root if none

#TO FINISH
	
	# removing trailing length as root branch as no length  # TO change?
	#$c =~ s/:$v$//;
	
    return $c;
}
################################
sub addFakeSupp { # add fake support to taxa and clades having no length.
    verbose "Adding fake supports\n";
    my ($c,$v) = @_; 
    #print "\n receives:\n\t $c\n";    
    # BEWARE =~s//g  <--with option g:  does not work properly here for some reason
	while ($c =~ /\)[:,\)]/) {       
		$c =~ s/\)([:,\)])/)$v$1/; } 
	# Add support to root if none already put
	$c =~ s/\)$/)$v/;
    #print "\n with supports:\n\t $c\n";
    return $c;
}
################################
sub Present {
	my $val = shift @_;
	for (@_) {
		return 1 if ($val == $_);
	}
	return 0;
}
########################################################################################
########################################################################################
###############											################################
###############				 MAIN				 		################################
###############											################################
########################################################################################
########################################################################################
# params = names of files containing trees
die "\n\tUsage: ./addFake.... L|S|LS infile > outputFile\n\n" if ($#ARGV!=1);

# Determines whether to remove Lengths and / or Supports
my $options = shift @ARGV;
my ($addL,$addS);
if ($options eq "L") {$addL = 1; $addS = 0;}
elsif ($options eq "S") {$addL = 0; $addS = 1;}
elsif (($options eq "LS") || ($options eq "SL")) {$addL = 1; $addS = 1;}
else {die "First parameter must indicate whether Supports and/or Lengths must be added (S|L|LS)!\n";}


# Reads and process trees

my $inTree=0; # nb of input trees
my $fic = shift @ARGV;
    
    open F,$fic or die "Cannot open $fic\n";
    verbose "Reading file $fic\n";
    my $arbre;
    while (<F>) {$arbre .= $_ ;} 
    close F;
    $arbre =~ s/[^-+\w\.;:\,\(\)]//g; # vire tout ce qui n'est pas attendu (dont ^M, etc)

    $arbre =~ s/\n//g; chop $arbre; # enleve le ; final sinon arb fantome
    my @arb = split /;/, $arbre;
    verbose "This file contains ", 1+$#arb," trees to process\n";
    my $replace = 'no';
    for ($a=0;$a<=$#arb;$a++) {
       $arb[$a] =~ s/\s+ //g;    # vire espaces de la chaine si y en a
       
       # Check if correct numbers of opening and closing brackets
       $tmp = $arb[$a];
       $openNb = ($tmp =~ tr/(//); $closeNb = ($tmp =~ tr/)//);
       die "Error: unmatching numbers of brackets in Newick format of tree\n" unless ($openNb == $closeNb);

	
		my ($fa,$fb);
		# CHECK IF LENGTHS and which ones
		if ($addL ==0) { $fakeLgth[$a] = "no";$fa=$arb[$a];}
		else {
			my @bl;
			my $has_length = 0; 
			if ($arb[$a]=~ /\:/) { # has length somewhere
					$has_length = 1;
					# Collect branch lengths
					my $tmpT =  $arb[$a] ;
					while ($tmpT =~ s/:([0-9][0-9eE\.\+\-]*)//) { push @bl,$1;}
			}
			# Chooses a fake val not appearing as a length in the tree
			my $fakeVal = "0" ; # 0.000111222;
			if ($has_length != 0) {while (Present($fakeVal,@bl)!=0) {$fakeVal+=1;} }
			#print "replace lgth by $fakeVal\n";
			$fakeLgth[$a] = $fakeVal ;
			# Adds this fake val if necessary
			$fa = addFakeLgth $arb[$a], $fakeVal;
			if ($fa =~ /:${fakeVal}/) {$replace = 'yes';} # a replacement has been done
	 		#print "$fa\nReplace = $replace\n";
 		}
 	
 	
 		# CHECK IF SUPPORTS and which ones
		if ($addS ==0) { $fakeSupp[$a] = "no";$fb=$fa;}
		else {
		    #print "before adding supp:\n\t$arb[$a]\n";
			my @supp;
			my $has_supp = 0; 
			if ($arb[$a]=~ /\)[0-9]/) { # has support somewhere
					$has_supp = 1;
					# Collect branch lengths
					my $tmpT =  $arb[$a] ;
					while ($tmpT =~ s/\)([0-9][0-9eE\.\+\-]*)//) { push @supp,$1;}
			}
			# Chooses a fake val not appearing as a support in the tree
			my $fakeVal = "0" ; 
			if ($has_supp != 0) {while  (Present($fakeVal,@supp)!=0) {$fakeVal+=1;} }
			#print "replace supp by $fakeVal\n";
			$fakeSupp[$a] = $fakeVal ;
			# Adds this fake val if necessary
			$fb = addFakeSupp $fa, $fakeVal;
			if ($fb =~ /\)${fakeVal}[:\),]/) {$replace = 'yes';} # a replacement has been done
			#print "$fb\nReplace = $replace\n";
		}
		
 		$modif[$a] = $fb;
 		
     }
    #print $#arb+1," trees processed\n";

#print "replace = $replace\n";

# GENERATE fake.tmp file telling which fake values where added to the trees
open Fout,">fake.tmp";
if ($replace eq 'no') {print Fout "***\n";}
else {
	for (my $a=0;$a<=$#arb;$a++) { 		
		print Fout $fakeLgth[$a]," , ",$fakeSupp[$a],"\n";
	}
}
close OUT;

 # print modified trees to standard OUT
for (my $a=0;$a<=$#arb;$a++) { print $modif[$a],";\n";}
