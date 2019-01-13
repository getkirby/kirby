<?php
#
# SmartyPants Typographer  -  Smart typography for web sites
#
# PHP SmartyPants & Typographer  
# Copyright (c) 2004-2016 Michel Fortin
# <https://michelf.ca/>
#
# Original SmartyPants
# Copyright (c) 2003-2004 John Gruber
# <https://daringfireball.net/>
#
namespace Michelf;


#
# SmartyPants Typographer Parser Class
#
class SmartyPantsTypographer extends \Michelf\SmartyPants {

	### Configuration Variables ###

	# Options to specify which transformations to make:
	public $do_comma_quotes      = 0;
	public $do_guillemets        = 0;
	public $do_geresh_gershayim  = 0;
	public $do_space_emdash      = 0;
	public $do_space_endash      = 0;
	public $do_space_colon       = 0;
	public $do_space_semicolon   = 0;
	public $do_space_marks       = 0;
	public $do_space_frenchquote = 0;
	public $do_space_thousand    = 0;
	public $do_space_unit        = 0;

	# Quote characters for replacing ASCII approximations
	public $doublequote_low         = "&#8222;"; // replacement for ,,
	public $guillemet_leftpointing  = "&#171;"; // replacement for <<
	public $guillemet_rightpointing = "&#187;"; // replacement for >>
	public $geresh    = "&#1523;";
	public $gershayim = "&#1524;";

	# Space characters for different places:
	# Space around em-dashes.  "He_—_or she_—_should change that."
	public $space_emdash      = " ";
	# Space around en-dashes.  "He_–_or she_–_should change that."
	public $space_endash      = " ";
	# Space before a colon. "He said_: here it is."
	public $space_colon       = "&#160;";
	# Space before a semicolon. "That's what I said_; that's what he said."
	public $space_semicolon   = "&#160;";
	# Space before a question mark and an exclamation mark: "¡_Holà_! What_?"
	public $space_marks       = "&#160;";
	# Space inside french quotes. "Voici la «_chose_» qui m'a attaqué."
	public $space_frenchquote = "&#160;";
	# Space as thousand separator. "On compte 10_000 maisons sur cette liste."
	public $space_thousand    = "&#160;";
	# Space before a unit abreviation. "This 12_kg of matter costs 10_$."
	public $space_unit        = "&#160;";

	
	# Expression of a space (breakable or not):
	public $space = '(?: | |&nbsp;|&#0*160;|&#x0*[aA]0;)';


	### Parser Implementation ###

	public function __construct($attr = SmartyPants::ATTR_DEFAULT) {
	#
	# Initialize a SmartyPantsTypographer_Parser with certain attributes.
	#
	# Parser attributes:
	# 0 : do nothing
	# 1 : set all, except dash spacing
	# 2 : set all, except dash spacing, using old school en- and em- dash shortcuts
	# 3 : set all, except dash spacing, using inverted old school en and em- dash shortcuts
	# 
	# Punctuation:
	# q -> quotes
	# b -> backtick quotes (``double'' only)
	# B -> backtick quotes (``double'' and `single')
	# c -> comma quotes (,,double`` only)
	# g -> guillemets (<<double>> only)
	# d -> dashes
	# D -> old school dashes
	# i -> inverted old school dashes
	# e -> ellipses
	# w -> convert &quot; entities to " for Dreamweaver users
	#
	# Spacing:
	# : -> colon spacing +-
	# ; -> semicolon spacing +-
	# m -> question and exclamation marks spacing +-
	# h -> em-dash spacing +-
	# H -> en-dash spacing +-
	# f -> french quote spacing +-
	# t -> thousand separator spacing -
	# u -> unit spacing +-
	#   (you can add a plus sign after some of these options denoted by + to 
	#    add the space when it is not already present, or you can add a minus 
	#    sign to completly remove any space present)
	#
		# Initialize inherited SmartyPants parser.
		parent::__construct($attr);
				
		if ($attr == "1" || $attr == "2" || $attr == "3") {
			# Do everything, turn all options on.
			$this->do_comma_quotes      = 1;
			$this->do_guillemets        = 1;
			$this->do_geresh_gershayim  = 1;
			$this->do_space_emdash      = 1;
			$this->do_space_endash      = 1;
			$this->do_space_colon       = 1;
			$this->do_space_semicolon   = 1;
			$this->do_space_marks       = 1;
			$this->do_space_frenchquote = 1;
			$this->do_space_thousand    = 1;
			$this->do_space_unit        = 1;
		}
		else if ($attr == "-1") {
			# Special "stupefy" mode.
			$this->do_stupefy   = 1;
		}
		else {
			$chars = preg_split('//', $attr);
			foreach ($chars as $c){
				if      ($c == "c") { $current =& $this->do_comma_quotes; }
				else if ($c == "g") { $current =& $this->do_guillemets; }
				else if ($c == "G") { $current =& $this->do_geresh_gershayim; }
				else if ($c == ":") { $current =& $this->do_space_colon; }
				else if ($c == ";") { $current =& $this->do_space_semicolon; }
				else if ($c == "m") { $current =& $this->do_space_marks; }
				else if ($c == "h") { $current =& $this->do_space_emdash; }
				else if ($c == "H") { $current =& $this->do_space_endash; }
				else if ($c == "f") { $current =& $this->do_space_frenchquote; }
				else if ($c == "t") { $current =& $this->do_space_thousand; }
				else if ($c == "u") { $current =& $this->do_space_unit; }
				else if ($c == "+") {
					$current = 2;
					unset($current);
				}
				else if ($c == "-") {
					$current = -1;
					unset($current);
				}
				else {
					# Unknown attribute option, ignore.
				}
				$current = 1;
			}
		}
	}


	function decodeEntitiesInConfiguration() {
		parent::decodeEntitiesInConfiguration();
		$output_config_vars = array(
			'doublequote_low',
			'guillemet_leftpointing',
			'guillemet_rightpointing',
			'space_emdash',
			'space_endash',
			'space_colon',
			'space_semicolon',
			'space_marks',
			'space_frenchquote',
			'space_thousand',
			'space_unit',
		);
		foreach ($output_config_vars as $var) {
			$this->$var = html_entity_decode($this->$var);
		}
	}


	function educate($t, $prev_token_last_char) {
		# must happen before regular smart quotes
		if ($this->do_geresh_gershayim)  $t = $this->educateGereshGershayim($t);

		$t = parent::educate($t, $prev_token_last_char);
		
		if ($this->do_comma_quotes)      $t = $this->educateCommaQuotes($t);
		if ($this->do_guillemets)        $t = $this->educateGuillemets($t);
		
		if ($this->do_space_emdash)      $t = $this->spaceEmDash($t);
		if ($this->do_space_endash)      $t = $this->spaceEnDash($t);
		if ($this->do_space_colon)       $t = $this->spaceColon($t);
		if ($this->do_space_semicolon)   $t = $this->spaceSemicolon($t);
		if ($this->do_space_marks)       $t = $this->spaceMarks($t);
		if ($this->do_space_frenchquote) $t = $this->spaceFrenchQuotes($t);
		if ($this->do_space_thousand)    $t = $this->spaceThousandSeparator($t);
		if ($this->do_space_unit)        $t = $this->spaceUnit($t);
		
		return $t;
	}


	protected function educateCommaQuotes($_) {
	#
	#   Parameter:  String.
	#   Returns:    The string, with ,,comma,, -style double quotes
	#               translated into HTML curly quote entities.
	#
	#   Example input:  ,,Isn't this fun?,,
	#   Example output: &#8222;Isn't this fun?&#8222;
	#
	# Note: this is meant to be used alongside with backtick quotes; there is 
	# no language that use only lower quotations alone mark like in the example.
	#
		$_ = str_replace(",,", $this->doublequote_low, $_);
		return $_;
	}


	protected function educateGuillemets($_) {
	#
	#   Parameter:  String.
	#   Returns:    The string, with << guillemets >> -style quotes
	#               translated into HTML guillemets entities.
	#
	#   Example input:  << Isn't this fun? >>
	#   Example output: &#8222; Isn't this fun? &#8222;
	#
		$_ = preg_replace("/(?:<|&lt;){2}/", $this->guillemet_leftpointing, $_);
		$_ = preg_replace("/(?:>|&gt;){2}/", $this->guillemet_rightpointing, $_);
		return $_;
	}


	protected function educateGereshGershayim($_) {
	#
	#   Parameter:  String, UTF-8 encoded.
	#   Returns:    The string, where simple a or double quote surrounded by
	#               two hebrew characters is replaced into a typographic
	#               geresh or gershayim punctuation mark.
	#
	#   Example input:  צה"ל / צ'ארלס
	#   Example output: צה״ל / צ׳ארלס
	#
		// surrounding code points can be U+0590 to U+05BF and U+05D0 to U+05F2
		// encoded in UTF-8: D6.90 to D6.BF and D7.90 to D7.B2
		$_ = preg_replace('/(?<=\xD6[\x90-\xBF]|\xD7[\x90-\xB2])\'(?=\xD6[\x90-\xBF]|\xD7[\x90-\xB2])/', $this->geresh, $_);
		$_ = preg_replace('/(?<=\xD6[\x90-\xBF]|\xD7[\x90-\xB2])"(?=\xD6[\x90-\xBF]|\xD7[\x90-\xB2])/', $this->gershayim, $_);
		return $_;
	}


	protected function spaceFrenchQuotes($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				inside french-style quotes, only french quotes.
	#
	#	Example input:  Quotes in « French », »German« and »Finnish» style.
	#	Example output: Quotes in «_French_», »German« and »Finnish» style.
	#
		$opt = ( $this->do_space_frenchquote ==  2 ? '?' : '' );
		$chr = ( $this->do_space_frenchquote != -1 ? $this->space_frenchquote : '' );
		
		# Characters allowed immediatly outside quotes.
		$outside_char = $this->space . '|\s|[.,:;!?\[\](){}|@*~=+-]|¡|¿';
		
		$_ = preg_replace(
			"/(^|$outside_char)(&#171;|«|&#8250;|‹)$this->space$opt/",
			"\\1\\2$chr", $_);
		$_ = preg_replace(
			"/$this->space$opt(&#187;|»|&#8249;|›)($outside_char|$)/", 
			"$chr\\1\\2", $_);
		return $_;
	}


	protected function spaceColon($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				before colons.
	#
	#	Example input:  Ingredients : fun.
	#	Example output: Ingredients_: fun.
	#
		$opt = ( $this->do_space_colon ==  2 ? '?' : '' );
		$chr = ( $this->do_space_colon != -1 ? $this->space_colon : '' );
		
		$_ = preg_replace("/$this->space$opt(:)(\\s|$)/m",
						  "$chr\\1\\2", $_);
		return $_;
	}


	protected function spaceSemicolon($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				before semicolons.
	#
	#	Example input:  There he goes ; there she goes.
	#	Example output: There he goes_; there she goes.
	#
		$opt = ( $this->do_space_semicolon ==  2 ? '?' : '' );
		$chr = ( $this->do_space_semicolon != -1 ? $this->space_semicolon : '' );
		
		$_ = preg_replace("/$this->space(;)(?=\\s|$)/m", 
						  " \\1", $_);
		$_ = preg_replace("/((?:^|\\s)(?>[^&;\\s]+|&#?[a-zA-Z0-9]+;)*)".
						  " $opt(;)(?=\\s|$)/m", 
						  "\\1$chr\\2", $_);
		return $_;
	}


	protected function spaceMarks($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				around question and exclamation marks.
	#
	#	Example input:  ¡ Holà ! What ?
	#	Example output: ¡_Holà_! What_?
	#
		$opt = ( $this->do_space_marks ==  2 ? '?' : '' );
		$chr = ( $this->do_space_marks != -1 ? $this->space_marks : '' );

		// Regular marks.
		$_ = preg_replace("/$this->space$opt([?!]+)/", "$chr\\1", $_);

		// Inverted marks.
		$imarks = "(?:¡|&iexcl;|&#161;|&#x[Aa]1;|¿|&iquest;|&#191;|&#x[Bb][Ff];)";
		$_ = preg_replace("/($imarks+)$this->space$opt/", "\\1$chr", $_);
	
		return $_;
	}


	protected function spaceEmDash($_) {
	#
	#	Parameters: String, two replacement characters separated by a hyphen (`-`),
	#				and forcing flag.
	#
	#	Returns:    The string, with appropriates spaces replaced 
	#				around dashes.
	#
	#	Example input:  Then — without any plan — the fun happend.
	#	Example output: Then_—_without any plan_—_the fun happend.
	#
		$opt = ( $this->do_space_emdash ==  2 ? '?' : '' );
		$chr = ( $this->do_space_emdash != -1 ? $this->space_emdash : '' );
		$_ = preg_replace("/$this->space$opt(&#8212;|—)$this->space$opt/", 
			"$chr\\1$chr", $_);
		return $_;
	}
	
	
	protected function spaceEnDash($_) {
	#
	#	Parameters: String, two replacement characters separated by a hyphen (`-`),
	#				and forcing flag.
	#
	#	Returns:    The string, with appropriates spaces replaced 
	#				around dashes.
	#
	#	Example input:  Then — without any plan — the fun happend.
	#	Example output: Then_—_without any plan_—_the fun happend.
	#
		$opt = ( $this->do_space_endash ==  2 ? '?' : '' );
		$chr = ( $this->do_space_endash != -1 ? $this->space_endash : '' );
		$_ = preg_replace("/$this->space$opt(&#8211;|–)$this->space$opt/", 
			"$chr\\1$chr", $_);
		return $_;
	}


	protected function spaceThousandSeparator($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				inside numbers (thousand separator in french).
	#
	#	Example input:  Il y a 10 000 insectes amusants dans ton jardin.
	#	Example output: Il y a 10_000 insectes amusants dans ton jardin.
	#
		$chr = ( $this->do_space_thousand != -1 ? $this->space_thousand : '' );
		$_ = preg_replace('/([0-9]) ([0-9])/', "\\1$chr\\2", $_);
		return $_;
	}


	protected $units = '
		### Metric units (with prefixes)
		(?:
			p |
			µ | &micro; | &\#0*181; | &\#[xX]0*[Bb]5; |
			[mcdhkMGT]
		)?
		(?:
			[mgstAKNJWCVFSTHBL]|mol|cd|rad|Hz|Pa|Wb|lm|lx|Bq|Gy|Sv|kat|
			Ω | Ohm | &Omega; | &\#0*937; | &\#[xX]0*3[Aa]9;
		)|
		### Computers units (KB, Kb, TB, Kbps)
		[kKMGT]?(?:[oBb]|[oBb]ps|flops)|
		### Money
		¢ | &cent; | &\#0*162; | &\#[xX]0*[Aa]2; |
		M?(?:
			£ | &pound; | &\#0*163; | &\#[xX]0*[Aa]3; |
			¥ | &yen;   | &\#0*165; | &\#[xX]0*[Aa]5; |
			€ | &euro;  | &\#0*8364; | &\#[xX]0*20[Aa][Cc]; |
			$
		)|
		### Other units
		(?: ° | &deg; | &\#0*176; | &\#[xX]0*[Bb]0; ) [CF]? | 
		%|pt|pi|M?px|em|en|gal|lb|[NSEOW]|[NS][EOW]|ha|mbar
		'; //x

	protected function spaceUnit($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced
	#				before unit symbols.
	#
	#	Example input:  Get 3 mol of fun for 3 $.
	#	Example output: Get 3_mol of fun for 3_$.
	#
		$opt = ( $this->do_space_unit ==  2 ? '?' : '' );
		$chr = ( $this->do_space_unit != -1 ? $this->space_unit : '' );

		$_ = preg_replace('/
			(?:([0-9])[ ]'.$opt.') # Number followed by space.
			('.$this->units.')     # Unit.
			(?![a-zA-Z0-9])  # Negative lookahead for other unit characters.
			/x',
			"\\1$chr\\2", $_);

		return $_;
	}


	protected function spaceAbbr($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced
	#				around abbreviations.
	#
	#	Example input:  Fun i.e. something pleasant.
	#	Example output: Fun i.e._something pleasant.
	#
		$opt = ( $this->do_space_abbr ==  2 ? '?' : '' );
		
		$_ = preg_replace("/(^|\s)($this->abbr_after) $opt/m",
			"\\1\\2$this->space_abbr", $_);
		$_ = preg_replace("/( )$opt($this->abbr_sp_before)(?![a-zA-Z'])/m", 
			"\\1$this->space_abbr\\2", $_);
		return $_;
	}


	protected function stupefyEntities($_) {
	#
	#   Adding angle quotes and lower quotes to SmartyPants's stupefy mode.
	#
		$_ = parent::stupefyEntities($_);

		$_ = str_replace(array('&#8222;', '&#171;', '&#187'), '"', $_);

		return $_;
	}


	protected function processEscapes($_) {
	#
	#   Adding a few more escapes to SmartyPants's escapes:
	#
	#               Escape  Value
	#               ------  -----
	#               \,      &#44;
	#               \<      &#60;
	#               \>      &#62;
	#
		$_ = parent::processEscapes($_);

		$_ = str_replace(
			array('\,',    '\<',    '\>',    '\&lt;', '\&gt;'),
			array('&#44;', '&#60;', '&#62;', '&#60;', '&#62;'), $_);

		return $_;
	}
}
