<?php
/**
 * EmailVerifier.php
 *
 * PHP Version 5
 *
 * EmailVerifier File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RS;

/**
 * Verifies an email exists
 *
 * Please report bugs on https://github.com/espino316/rsphp/issues
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */
class EmailVerifier
{

    //  Es el email que vamos a verificar
    private $_email;

    //  Esta es la conexión al servidor smtp
    private $_smtpConn;

    //  El log, los comandos y las respuestas
    private $_recordLog;

    //  La dirección del servidor SMTP
    private $_server;

    //  Guarda la ultima respuesta del servidor
    private $_lastResponse;

    //  Guarda el resultado, si es válido o no
    //  Lo inicializamos para comenzar con los comandos
    //  y comparar los resultados
    public $isValid = true;

    /**
     * Crea una nueva instancia de EmailVerifier
     *
     * @param String $email el email a verificar
     *
     * @return void
     */
    function __construct( $email )
    {
        //  Establecemos los valores
        $this->_email = $email;
        $this->_recordLog = '';
    } // end function __construct

    /**
     *  Regresa la parte izquierda de la cadena
     *
     *  @param String $str    The String to trim
     *  @param Int    $length The length to trim
     *
     *  @return String
     */
    static function left($str, $length)
    {
        return substr($str, 0, $length);
    } // end function left

    /**
     *  Verifica que el email exista
     *  Establece una conexión y envia una serie de comandos
     *  para probar la dirección de correo
     *
     *  @return Boolean
     */
    function verify()
    {
        try {
            //  Creamos la conexión
            $this->_createSMTPConnection();
            //  Primer HELO, es como iniciar el telnet
            $this->_sendCommand("HELO $this->_server");
            //  Segundo HELO, este si lo cacha el servidor SMTP
            $this->_sendCommand("HELO $this->_server");
            //  Establecemos el FROM
            //  El FROM no necesariamente tiene que ser un correo
            //  válido en el sistema
            $this->_sendCommand("MAIL FROM: <$this->_email>");
            //  Establecemos el destinatario
            //  Aqui verifica que el correo exista de verdad
            $this->_sendCommand("RCPT TO: <$this->_email>");
            //  Salimos
            $this->_sendCommand("QUIT");

            //  Cerramos
            return $this->_close();

        } catch ( Exception $ex ) {
            $this->isValid = false;
            $this->_addLog("error  ".$ex->getMessage());
            return false;
        } // end catch
    } // end function verify

    /**
     *  Cierra la conexión, imprime los comandos y las respuestas
     *  y regresa el resultado
     *
     *  @return Boolean
     */
    private function _close()
    {
        //  Cerramos la conexión
        $this->_closeSMTPConnection();
        //  Regresamos el resultado
        return $this->isValid;
    } // end function _close

    /**
     *  Agrega una linea de texto al log
     *
     *  @return void
     */
    function getLog()
    {
        return $this->_recordLog;
    } // end function _addLog

    /**
     *  Agrega una linea de texto al log
     *
     *  @param String $logMessage The message to log
     *
     *  @return void
     */
    private function _addLog( $logMessage )
    {
        $logMessage.=NEW_LINE;
        $this->_recordLog.= $logMessage;
    } // end function _addLog

    /**
     *  Cierra la conexión SMTP
     *
     *  @return void
     */
    private function _closeSMTPConnection()
    {
        fclose($this->_smtpConn);
        return;
    } // end function _closeSMTPConnection

    /**
     *  Envía el comando y procesamos la respuesta
     *
     *  @param String $command El comando a ejecutar
     *
     *  @return Boolean
     */
    private function _sendCommand( $command )
    {

        //  Solo si aun es válido
        //  si no, ya no realiza los comandos
        //  Puesto que ya es inválido
        if (!$this->isValid ) {
            return true;
        }

        //  Agregamos el comando al log
        $command.= NEW_LINE;
        $this->_addLog($command);

        //  Enviamos el comando
        fwrite($this->_smtpConn, $command);

        //  Obtenemos la respuesta
        $this->_getResponse();

        //  Procesamos la respuesta
        $this->_parseResponse();

        //  Regresamos que el comando se envío correctamente
        //  no el resultado
        return true;
    }

    /**
     *  Procesa la respuesta
     *
     *  @return void
     */
    private function _parseResponse()
    {

        //  Obtenemos el codigo de la respuesta
        $response = $this->left($this->_lastResponse, 3);
        //  Estas son las válidas
        $ok = array( 220, 221, 250);

        //  Si está en las válidas
        if (in_array($response, $ok) ) {
            //  Es válido
            $this->isValid = true;
        } else {
            //  No es válido
            $this->isValid = false;
        } // end if
    } // end function _parseResponse

    /**
     *  Obtiene la respuesta
     *
     *  @return String
     */
    private function _getResponse()
    {

        //  Aqui almacenaremos la respuesta
        $output = '';
        //  Mientras haya conexión y mande datos
        while ( is_resource($this->_smtpConn) && !feof($this->smtpConn) ) {
            //  Obtenemos una linea
            $line = @fgets($this->_smtpConn, 515);
            //  La ponemos en el buffer
            $output.= $line;
            //  Si el cuarto caracter es espacio, se termina
            if ((isset($line[3]) and $line[3] == ' ')) {
                break;
            } // end if $line[3]
        } // end while is_resource

        //  Agregamos la salida al log
        $this->_addLog($output);

        //  Si la respuesta que obtuvimos es la misma
        //  que la ultima, volvemos a obtener la respuesta
        if ($this->_lastResponse ) {
            if ($this->_lastResponse == $output ) {
                $output = '';
                while ( is_resource($this->_smtpConn) && !feof($this->smtpConn) ) {
                    $line = @fgets($this->_smtpConn, 515);
                    $output.= $line;
                    if ((isset($line[3]) and $line[3] == ' ')) {
                        break;
                    }
                }
                $this->_addLog($output);
            }
        }

        //  Ahora si la ponemos en la última respuesta
        $this->_lastResponse = $output;

        //  Regresamos la respuesta
        return $output;
    } // end function _getResponse

    /**
     *  Crea una conexión SMTP
     *
     *  @return Boolean
     */
    private function _createSMTPConnection()
    {
        //  Obtenemos las partes del email
        $parts = explode('@', $this->_email);
        //  La segunda parte es el dominio
        $domain = $parts[1];

        //  Obtenemos los records mx
        $mxhosts = array();
        $mxweights = array();
        $isMx = getmxrr($domain, $mxhosts, $mxweight);

        if (empty($mxhosts) ) {
            throw new Exception('No mx records');
        }

        //  El dominio, el servidor al que vamos a apuntar
        //  es el primer registro mx
        $domain = $mxhosts[0];

        //  El puerto es el 25
        $port = 25;

        //  Obtenemos la dirección IP
        $address = gethostbyname($domain);

        //  formamos la dirección con protocolo, ip y puerto
        $to = "tcp://$address:$port";
        //  Intentamos crear el socket
        $this->_smtpConn = stream_socket_client($to, $errno, $errorMessage, 10);

        //  Si no se logro conectar
        //  hay un error
        if ($this->_smtpConn === false ) {
            throw
                new UnexpectedValueException(
                    "Connection failed, error: $errorMessage"
                );
        } // end if

        //  Establecemos el servidor
        $this->_server = $domain;

        //  True por que nos conectamos
        return true;
    } // end _createSMTPConnection
} // end class EmailVerifier
