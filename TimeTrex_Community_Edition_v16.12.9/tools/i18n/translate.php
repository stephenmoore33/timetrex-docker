<?php
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..'  . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..'  . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

use Gettext\Loader\PoLoader;
use Gettext\Generator\PoGenerator;

/*
 * This script when passed a gettext *.POT, or *.PO file will
 * attempt to use a online service to translate each string
 * to the specified language.
 *
 * This should hopefully serve as a good STARTING point for further
 * human transation.
 *
 * This file will first create batched input files ready for translation.
 * It will then load the translated files and create a messages.po file from them.
 *
 * Take .PO file and create small HTML batch files for translations
 * php translate.php -s ../../interface/locale/fr_FR/LC_MESSAGES/messages.po ./tr_batches.html
 *
 * Using a web browser to translate the .html file, scroll all the way to the bottom of the file, save it from the Dev Tools.
 *
 * Translate HTML batch files back into .PO file
 * php translate.php -t ../../interface/locale/fr_FR/LC_MESSAGES/messages.po ./tr_batches.html1 fr.po
 *
 */
if ( PHP_SAPI != 'cli' ) {
	echo "This script can only be called from the Command Line.\n";
	exit;
}

if ( $argc < 3 || in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) {
	$help_output = "Usage: translate.php [OPTIONS] \n";
	$help_output .= "  Options:\n";
	$help_output .= "    -s 	[.POT or .PO] [OUT HTML]\n";
	$help_output .= "    	 	Create a source translation file, suitable to be translated on mass.\n";
	$help_output .= "    -t 	[.POT or .PO] [IN HTML] [OUTFILE]\n";

	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = count( $argv ) - 1;

	if ( in_array( '-s', $argv ) ) {
		$create_source = true;
	} else {
		$create_source = false;
	}

	if ( isset( $argv[$last_arg - 2] ) && $argv[2] != '' ) {
		if ( !file_exists( $argv[2] ) || !is_readable( $argv[2] ) ) {
			echo ".POT or .PO File: " . $argv[2] . " does not exists or is not readable!\n";
		} else {
			$source_file = $argv[2];
		}

		if ( $create_source == true ) {
			$outfile = $argv[3];
			$infile = null;
		} else {
			$infile = $argv[3];
			$outfile = $argv[4];
		}
		echo "In File: $infile\n";
		echo "Out File: $outfile\n";

		//import from a .po file:
		$po = new PoLoader();
		$po_strings = $po->loadFile( $source_file );

		if ( $create_source == true ) {
			$batch_size = 1000;
			//$batch_size = 999999;
			$batch = 0;
			$prev_batch = 0;
			$i = 0;
			$out = null;
			$max = count( $po_strings->getTranslations() ) - 1;
			echo "Max: $max\n";
			foreach ( $po_strings->getTranslations() as $msg_obj ) {
				//echo "$i. $msgid\n";
				$msgid = preg_replace('/[\x00-\x1F\x7F]/', '', $msg_obj->getId() );
				$msgstr = trim( $msg_obj->getTranslation() );
				if ( $msgid == '#' || $msgstr != '' ) {
					$i++;

					if ( $i < $max ) {
						continue;
					}
				}

				if ( $i == 0 || $out == null ) {
					echo "I = 0 OR Batch = 0\n";
					$out = "<html>\n";
					$out .= "<body><pre>\n";
				}

				if ( $i > 0 && ( $i % $batch_size == 0 || $i == $max ) ) {
					$batch++;
					echo "New Batch = $batch\n";
				}

				$out .= '<span class="' . htmlentities( $msgid ) . '">' . htmlentities( $msgid ) . "</span><br>\n";
				//$out .= $i.': '. str_replace('<br>', '(11)', $msgid) ."<br>\n";

				if ( $batch != $prev_batch ) {
					echo "Writing...\n";
					$out .= "</pre></body>\n";
					$out .= "</html>\n";

					//Write the file.
					$output_file_name = str_replace( '.', '-'. $batch .'.', dirname( $outfile ) . DIRECTORY_SEPARATOR . basename( $outfile ) );
					echo "Writing to: ". $output_file_name ."\n";
					file_put_contents( $output_file_name, $out );

					$out = null;
				}

				$prev_batch = $batch;
				$i++;
			}
		} else {
			//Load translated HTML files.
			echo "Loading Translated File\n";

			$file_contents = file_get_contents( $infile );
			$file_contents = preg_replace( '/<html .*>/iu', '', $file_contents );
			$file_contents = preg_replace( '/<head>.*<\/head>/iu', '', $file_contents );
			$file_contents = preg_replace( '/<base.*>/iu', '', $file_contents );
			$file_contents = preg_replace( '/<\/span>([\s]*)<br>/iu', '</span>', $file_contents );
			$file_contents = preg_replace( '/<\/span><br>([\s]*)/iu', '</span>', $file_contents );
			$file_contents = preg_replace( '/<font style="(.*)">/iu', '', $file_contents );
			$file_contents = preg_replace( '/ :/iu', ':', $file_contents );
			$file_contents = str_replace( [ '<html>', '</html>', '<body>', '</body>', '<pre>', '</pre>', '</font>' ], '', $file_contents );

			$lines = explode( '</span>', $file_contents );
			//var_dump($lines);
			if ( is_array( $lines ) ) {
				echo "Total Lines: " . count( $lines ) . "\n";

				$i = 0;
				foreach ( $lines as $line ) {
					//Parse the string number
					if ( preg_match( '/<span class=\"(.*)\">(.*)/i', trim( $line ), $matches ) == true ) {
						if ( is_array( $matches ) && isset( $matches[1] ) && isset( $matches[2] ) ) {
							$msgid = html_entity_decode( $matches[1] );
							$msgstr = preg_replace( '/\s\"\s/iu', '"', html_entity_decode( $matches[2] ) );

							echo $i . ". Translating: " . $msgid . "\n";
							echo "              To: " . $msgstr . "\n";
							$tmp_translation = $po_strings->find( null, $msgid );
							if ( $tmp_translation ) {
								$tmp_translation->translate( $msgstr );
							} else {
								echo "Failed to find translation key...\n";
							}
						} else {
							echo "ERROR parsing line!\n";
						}
					} else {
						echo "Failed to match line!\n";
					}

					$i++;
				}
			}

			$po_generator = new PoGenerator();
			$po_generator->generateFile( $po_strings, $outfile );
		}
	}
}
?>
