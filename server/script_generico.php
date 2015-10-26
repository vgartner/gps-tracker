#!/usr/bin/php -q
<?php
//waiting for system startup
//crontab: @reboot php -q /var/www/server/tracker_tlt.php
//sleep (180);

/**
 * Listens for requests and forks on each connection
 */
$tipoLog = "arquivo"; // tela //debug log, escreve na tela ou no arquivo de log.

$fh = null;
$remip = null;
$remport = null;

/* if ($tipoLog == "arquivo") {
  //Criando arquivo de log
  $fn = ROOT_URL."/sites/1/logs/" . "Log_". date("dmyhis") .".log";
  $fh = fopen($fn, 'w') or die ("Can not create file");
  $tempstr = "Log Inicio".chr(13).chr(10);
  fwrite($fh, $tempstr);
  } */

function abrirArquivoLog($imeiLog) {
    GLOBAL $fh;

    //$fn = ".".dirname(__FILE__)."/sites/1/logs/Log_". trim($imeiLog) .".log";
    $fn = "./var/www/html/gps-tracker/sites/1/logs/rastreador_generico.log";
    $fn = trim($fn);
    $fh = fopen($fn, 'a') or die("Can not create file");
    $tempstr = "Log Inicio" . chr(13) . chr(10);
    fwrite($fh, $tempstr);
}

function fecharArquivoLog() {
    GLOBAL $fh;
    if ($fh != null)
        fclose($fh);
}

function printLog($fh, $mensagem) {
    GLOBAL $tipoLog;
    GLOBAL $fh;

    if ($tipoLog == "arquivo") {
        //escreve no arquivo
        if ($fh != null)
            fwrite($fh, $mensagem . chr(13) . chr(10));
    } else {
        //escreve na tela
        echo $mensagem . "<br />";
    }
}

// IP Local
$ip = '172.31.27.135';
// Port
$port = 9000;
// Path to look for files with commands to send
$command_path = "./var/www/html/gps-tracker/sites/1/";
$from_email = 'brenowd@gmail.com';

$__server_listening = true;

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);
ini_set('sendmail_from', $from_email);

//printLog($fh, "become_daemon() in");
become_daemon();
//printLog($fh, "become_daemon() out");

/* nobody/nogroup, change to your host's uid/gid of the non-priv user

 * * Comment by Andrew - I could not get this to work, i commented it out
  the code still works fine but mine does not run as a priv user anyway....
  uncommented for completeness
 */
//change_identity(65534, 65534);

/* handle signals */
pcntl_signal(SIGTERM, 'sig_handler');
pcntl_signal(SIGINT, 'sig_handler');
pcntl_signal(SIGCHLD, 'sig_handler');

//printLog($fh, "pcntl_signal ok");

/* change this to your own host / port */
//printLog($fh, "server_loop in");
server_loop($ip,$port);

//Finalizando arquivo
//fclose($fh);

/**
 * Change the identity to a non-priv user
 */
function change_identity($uid, $gid) {
    if (!posix_setgid($gid)) {
        print "Unable to setgid to " . $gid . "!\n";
        exit;
    }

    if (!posix_setuid($uid)) {
        print "Unable to setuid to " . $uid . "!\n";
        exit;
    }
}

/**
 * Creates a server socket and listens for incoming client connections
 * @param string $address The address to listen on
 * @param int $port The port to listen on
 */
function server_loop($address, $port) {
    GLOBAL $fh;
    GLOBAL $__server_listening;

    printLog($fh, "server_looping...");

    if (($sock = socket_create(AF_INET, SOCK_STREAM, 0)) < 0) {
        printLog($fh, "failed to create socket: " . socket_strerror($sock));
        error_log("failed to create socket: " . socket_strerror($sock));
        exit();
    }

    if (($ret = socket_bind($sock, $address, $port)) < 0) {
        printLog($fh, "failed to bind socket: " . socket_strerror($ret));
        error_log("failed to bind socket: ".socket_strerror($ret));
        exit();
    }

    if (( $ret = socket_listen($sock, 0) ) < 0) {
        printLog($fh, "failed to listen to socket: " . socket_strerror($ret));
        error_log("failed to listen to socket: " . socket_strerror($ret));
        exit();
    }

    socket_set_nonblock($sock);

    printLog($fh, "waiting for clients to connect...");

    while ($__server_listening) {
        $connection = @socket_accept($sock);
        if ($connection === false) {
            usleep(100);
        } elseif ($connection > 0) {
            handle_client($sock, $connection);
        } else {
            printLog($fh, "error: ".socket_strerror($connection));
            error_log("error: ".socket_strerror($connection));
            die;
        }
    }
}

/**
 * Signal handler
 */
function sig_handler($sig) {
    switch ($sig) {
        case SIGTERM:
        case SIGINT:
            //exit();
            break;

        case SIGCHLD:
            pcntl_waitpid(-1, $status);
            break;
    }
}

$firstInteraction = false;

/**
 * Handle a new client connection
 */
function handle_client($ssock, $csock) {
    GLOBAL $__server_listening;
    GLOBAL $fh;
    GLOBAL $firstInteraction;
    GLOBAL $remip;
    GLOBAL $remport;

    $pid = pcntl_fork();

    if ($pid == -1) {
        /* fork failed */
        //printLog($fh, "fork failure!");
        die;
    } elseif ($pid == 0) {
        /* child process */
        $__server_listening = false;
        socket_getpeername($csock, $remip, $remport);

        //printLog($fh, date("d-m-y h:i:sa") . " Connection from $remip:$remport");

        $firstInteraction = true;

        // $send_cmd = "#88#DCST##\r\n";
        // socket_send($csock, $send_cmd, strlen($send_cmd), 0);
        // error_log('ENVIOU O COMANDO DCST');
        // error_log('SOCKET: '.$csock);
        // error_log('SEND CMD: '.$send_cmd);
        // $send_cmd = "#88#DCCM#6660000#5D##\r\n";
        // socket_send($csock, $send_cmd, strlen($send_cmd), 0);
        // error_log('ENVIOU O COMANDO DCCM 666');


        socket_close($ssock);
        interact($csock);
        socket_close($csock);

        printLog($fh, date("d-m-y h:i:sa") . " Connection to $remip:$remport closed");

        fecharArquivoLog();
    } else {
        socket_close($csock);
    }
}

function interact($socket) {
    GLOBAL $fh;
    GLOBAL $command_path;
    GLOBAL $firstInteraction;
    GLOBAL $remip;
    GLOBAL $remport;

    $loopcount = 0;
    $conn_imei = "";
    /* TALK TO YOUR CLIENT */
    $rec = "";


    # Read the socket but don't wait for data..
    while (@socket_recv($socket, $rec, 2048, 0x40) !== 0) {

        # Some pacing to ensure we don't split any incoming data.
        sleep(1);

        # Timeout the socket if it's not talking...
        # Prevents duplicate connections, confusing the send commands
        // $loopcount++;
        // if ($loopcount > 120)
        //     return;

        #remove any whitespace from ends of string.
        $rec = trim($rec);
        error_log($rec);
        printLog($fh, date("d-m-Y H:i:s") . " LOG RASTREADOR : $rec");
        


    } //while
}

//fim interact

/**
 * Become a daemon by forking and closing the parent
 */
function become_daemon() {
    GLOBAL $fh;

    //printLog($fh, "pcntl_fork() in");
    $pid = pcntl_fork();
    //printLog($fh, "pcntl_fork() out");

    if ($pid == -1) {
        /* fork failed */
        //printLog($fh, "fork failure!");
        exit();
    } elseif ($pid) {
        //printLog($fh, "pid: " . $pid);
        /* close the parent */
        exit();
    } else {
        /* child becomes our daemon */
        posix_setsid();
        chdir('/');
        umask(0);
        return posix_getpid();
    }

    //printLog($fh, "become_daemon() fim");
}
?>
