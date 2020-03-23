<?php
/**
 * Utility functions for the build PHP scripts.
 */

require_once __DIR__ . '/pue.php';

/**
 * Curried argument fetcher to avoid global spamming.
 *
 * @param array<string> $map The list of arguments to fetch from `$argv`.
 *
 * @return Closure The arg fetching closure.
 */
function args( array $map = [] ) {
	global $argv;

	$full_map = [];
	foreach ( $map as $position => $key ) {
		$full_map[ $key ] = isset( $argv[ $position + 1 ] ) ? $argv[ $position + 1 ] : null;
	}

	return static function ( $key, $default = null ) use ( $full_map ) {
		return null !== $full_map[ $key ] ? $full_map[ $key ] : $default;
	};
}

/**
 * Uses curl to fire a GET request to a URL.
 *
 * @param string $url The URL to fire the request to.
 * @param array  $query_args
 *
 * @return string  The curl response.
 */
function curl_get( $url, array $query_args = [] ) {
	$full_url = $url . ( strpos( $url, '?' ) === false ? '?' : '' ) . http_build_query( $query_args );

	$curl_handle = curl_init();
	curl_setopt( $curl_handle, CURLOPT_URL, $full_url );
	curl_setopt( $curl_handle, CURLOPT_HEADER, 0 );
	curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $curl_handle, CURLOPT_TIMEOUT, 10 );
	curl_setopt( $curl_handle, CURLOPT_FOLLOWLOCATION, true );

	if ( ! $result = curl_exec( $curl_handle ) ) {
		echo "\nFailed to process curl request.";
		echo "\nError: " . curl_error( $curl_handle );
		exit( 1 );
	}

	curl_close( $curl_handle );

	return $result;
}

/**
 * Parses a provided license file and puts into the env, if any.
 *
 * @param string|null $licenses_file The path to the licenses file to parse or `null` to read licenses from the
 *                                   environment variables.
 */
function parse_license_file( $licenses_file = null ) {
	if ( null !== $licenses_file ) {
		load_env_file( $licenses_file );
	} else {
		echo "\nLicenses file not specified, licenses will be read from environment.";
	}
}

/**
 * Loads the contents of an env file in the environment.
 *
 * @param string $env_file The env file to read the contents of.
 */
function load_env_file( $env_file ) {
	if ( ! file_exists( $env_file ) ) {
		echo "\nenv file ${env_file} does not exist.";
		exit( 1 );
	}

	$lines = array_filter( explode( "\n", file_get_contents( $env_file ) ) );
	foreach ( $lines as $env_line ) {
		if ( ! preg_match( '/^[^=]+=.*$/', $env_line ) ) {
			echo "\nLine '${env_line}' from env file is malformed.";
			exit( 1 );
		}
		putenv( $env_line );
	}
}

/**
 * Parses a string list into an array.
 *
 * @param array|string $list The list to parse.
 * @param string       $sep  The separator to use.
 *
 * @return array The parsed list.
 */
function parse_list( $list, $sep = ',' ) {
	if ( is_string( $list ) ) {
		$list = array_filter( preg_split( '/\\s*' . preg_quote( $sep ) . '\\s*/', $list ) );
	}

	return $list;
}

/**
 * Like `array_rand`, but returns the actual array key, not the index.
 *
 * @param array $array   The array to get the random keys for.
 * @param int   $num_req The required number of keys.
 *
 * @return array A set of random keys from the array.
 */
function array_rand_keys( array $array, $num_req = 1 ) {
	$picks = array_rand( $array, $num_req );

	return array_keys( array_intersect( array_flip( $array ), $picks ) );
}

/**
 * Checks the status of a process, or `exit`s.
 *
 * @param callable   $process The process to check.
 * @param mixed|null $message An optional message to print after the output, if the message is not a string, then
 *                            the message data will be encoded and printed using JSON.
 *
 * @return callable The process handling closure.
 */
function check_status_or_exit( callable $process, $message = null ) {
	if ( 0 !== (int) $process( 'status' ) ) {
		echo "\nProcess status is not 0, output: \n\n" . implode( "\n", $process( 'output' ) );
		if ( null !== $message ) {
			echo "\nDebug:\n" .
			     ( is_string( $message ) ? $message : json_encode( $message, JSON_PRETTY_PRINT ) ) .
			     "\n";
		}
		exit ( 1 );
	}

	return $process;
}

/**
 * Checks the status of a process on a timeout, or `exit`s.
 *
 * @param callable $process The process to check.
 * @param int      $timeout The timeout, in seconds.
 *
 * @return callable The process handling closure.
 */
function check_status_or_wait( callable $process, $timeout = 10 ) {
	$end = time() + (int) $timeout;
	while ( time() <= $end ) {
		if ( 0 !== (int) $process( 'status' ) ) {
			echo "\nProcess status is not 0, waiting...";
			sleep( 2 );
		} else {
			return $process;
		}
	}

	return check_status_or_exit( $process );
}

/**
 * Returns the relative path of a file, from a root.
 *
 * @param string $root The root file to build the relative path from.
 * @param string $file The file, or directory, to return the relative path for.
 *
 * @return string The file path relative to the root directory.
 */
function relative_path( $root, $file ) {
	$root          = rtrim( $root, '\\/' );
	$relative_path = str_replace( $root, '', $file );

	return ltrim( $relative_path, '\\/' );
}

/**
 * Sets up the user id and group in the environment.
 */
function setup_id() {
	$uid = getenv( 'UID' );
	putenv( 'UID=' . $uid );

	$gid = getenv( 'GID' );
	putenv( 'GID=' . $gid );
}

/**
 * Echoes a process output.
 *
 * @param callable $process the process to output from.
 */
function the_process_output( callable $process ) {
	echo "\n" . implode( "\n", $process( 'output' ) );
}

/**
 * Clarifies the nature of the issue.
 *
 * @return string Helpful ASCII art.
 */
function the_fatality() {
	return '
                       _..----------.._                       
                  .-=""        _       ""=-.                  
               .-"    _.--""j _\""""--._    "-.               
            .-"  .-i   \   / / \;       ""--.  "-.            
          .\'  .-"  : ( "  : :                "-.  `.          
        .\'  .\'      `.`.   \ \                  `.  `.        
       /  .\'      .---" ""--`."-./\'---.           `.  \       
      /  /      .\'                    \'-.           \  \      
     /  /      /                         `.          \  \     
    /  /      /                  ,--._   (            \  \    
   ,  /    \'-\')                  `---\'    `.           \  .   
  .  :      .\'                              "-._.-.     ;  ,  
  ;  ;     /            :;         ,-"-.    ,--.   )    :  :  
 :  :     :             ::        :_    "-. \'-\'   `,     ;  ; 
 |  |     :              \\     .--."-.    `._ _   ;     |  | 
 ;  ;     :              / "---"    "-."-.    l.`./      :  : 
:  :      ;             :              `. "-._; \         ;  ;
;  ;      ;             ;                `..___/\\        :  :
;  ;      ;             :                        \\    _  :  :
:  :     /              \'.                        ;;.__)) ;  ;
 ;  ; .-\'                 "-...______...--._      ::`--\' :  : 
 |  |  `--\'\                                "-.    \`._, |  | 
 :  :       \                                  `.   "-"  ;  ; 
  ;  ;       `.                                  \      :   \' 
  \'  :        ;                                   ;     ;  \'  
   \'  \    _  : :`.                               :    /  /   
    \  \   \`-\' ; ; ._                             ;  /  /    
     \  \   `--\'  : ; "-.                          : /  /     
      \  \        ;/     \                         ;/  /      
       \  `.              ;                        \'  /       
        `.  "-.   bug    /                          .\'        
          `.   "-..__..-"                         .\'          
            "-.                                .-"            
               "-._                        _.-"               
                   """---...______...---"""	
	';
}

/**
 * Returns the host machine IP address as reachable from the containers.
 *
 * The way the host machine IP address is fetched will vary depending on the Operating System the function runs on.
 *
 * @param string $os The operating system to get the host machine IP address for.
 *
 * @return string The host machine IP address or host name (e.g. `host.docker.internal` on macOS or Windows), or
 *                an empty string to indicate the host machine IP address could not be obtained.
 */
function host_ip( $os = 'Linux' ) {
	switch ( $os ) {
		case 'Linux':
			$command = "$(ip route | grep docker0 | awk '{print $9}')";
			exec( $command, $host_ip_output, $host_ip_status );
			if ( 0 !== (int) $host_ip_status ) {
				echo "\033[31mCannot get the host machine IP address using '${command}'" .
				     $host_ip = false;
			}
			$host_ip = $host_ip_output[0];
			break;
		default:
			$host_ip = 'host.docker.internal';
	}

	return $host_ip;
}

/**
 * Returns whether the current running context is a Continuous Integration one or not.
 *
 * @return bool Whether the current running context is a Continuous Integration one or not.
 */
function is_ci() {
	$env_vars = [
		'CI',
		'TRAVIS_CI',
		'CONTINUOUS_INTEGRATION',
		'GITHUB_ACTION',
	];
	foreach ( $env_vars as $key ) {
		if ( (bool) getenv( $key ) ) {
			return true;
		}
	}

	return false;
}
