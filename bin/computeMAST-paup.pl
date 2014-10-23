#!/usr/bin/perl
# Version as of August 22th 2011

# usage : computeMAST-paup.pl fileContainingNewickRootedTrees
# IN = a file containing trees in Newick format
# FILE OUTPUT = rooted MAST tree in file
# STANDARD OUTPUT = list of taxa in a mast tree in file
#---------------------------------------------

# PARAM = nom du fic ou trouver les arbres de depart. 

die "\n\tUsage: $0 tree_infile\n\n" unless ($#ARGV==0);

my $infile = shift @ARGV;

# 1 - RESTREINT LES ARBRES SOURCES A LEURS TAX COMMUNS
# my $PRG = "$ENV{HOME}/Prog/Supertree/Programs";
my $PRG = "/data/http/www/binaries/compphy";

# BEFORE : `${PRG}/addFakeLengthAndSupport.pl SL $infile > rest.tmp`;
# but Paup had some problems with some support values

`${PRG}/ote-lg-bp.pl $infile > rest.tmp`;
`${PRG}/restrict2commonsDeLuxe.pl rest.tmp > fic.tmp`;

# 2 - Generate the PAUP command file:

		# 2.1 reads taxa and a list of them for PAUP
		open F, "<fic.tmp"; my %taxa,@TREES;
		while (<F>) {
			push @TREES,$_;
			while (s/([,\(])(\w+)([,:\)])/$1$3/) {$taxa{$2} ++ ; }
		}
		close F;
		$nbtaxa = keys %taxa;
		#foreach (keys %taxa) { print "$_\n";}
		open PAUPIN,">mast.cmd"; 
		print PAUPIN "Begin taxa;\n dimensions ntax=",$nbtaxa,";\n taxlabels\t ", join(" ",keys %taxa),";\nend;\n";
		#print $#TREES+1," arbres\n";

		# 2.2 generate tree block
		print PAUPIN "Begin TREES;\n"; $num = 1;
		foreach (@TREES) {print PAUPIN "TREE T",$num++," [&R] = $_";}
		print PAUPIN "Endblock;\n\n";

		# 2.3 put commands to compute the agreement subtree
		print PAUPIN "Begin Paup;\n";
		print PAUPIN "agree all /file=mastPaup.out replace=YES showtree=NO;\nQUIT;\nEndblock;\n";
		close PAUPIN;


# 3 - launches PAUP

		`/data/http/www/binaries/compphy/paup-linux mast.cmd` or die "PBM in launching or processing PAUP!!!\n";

# 4 - gets a MAST TREE
		open PAUPOUT, "<mastPaup.out" or die "PBM: not finding PAUP output file!\n";
		open MASTOUT, ">mastTree.nwk" or die "Cannot create mastTree.nwk file!\n";
		while (<PAUPOUT>) {
			if (/^\s+tree.*=\s*\[\&R\]\s*([^;\s]+;)/) {print MASTOUT "$1\n";$mt=$1;last;} 
		}	
		close PAUPOUT; close MASTOUT;

# 5 - COMPUTES LEAVES OF THE MAST
		my @leaves;		
		while ($mt =~ s/[\(,](\w+)[,\)]//) {push @leaves,$1	;} 

		#open MASTLEAVES, ">mastLeaves.txt" or die "Cannot create mastLeaves.txt file!\n";
		print join(" ",@leaves),"\n"; # print on standard output
		#close MASTLEAVES;
		
# NB: to obtain the subtree of each source tree restricted to the MAST leaves : 
#  6.1 add mastTree to source tree files
#  6.2 restrict2commonsDeLuxe trees of this new file to common leaves: this prog now preserves branch lengths and support values
#  6.3 output all restricted trees except the last one
