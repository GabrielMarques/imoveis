<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');

/**
 * MY_Log Class
 *
 * This library assumes that you have a config item called
 * $config['show_in_log'] = array();
 * you can then create any error level you would like, using the following format
 * $config['show_in_log']= array('DEBUG','ERROR','INFO','SPECIAL','MY_ERROR_GROUP','ETC_GROUP');
 * Setting the array to empty will log all error messages.
 * Deleting this config item entirely will default to the standard
 * error loggin threshold config item.
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Logging
 * @author        ExpressionEngine Dev Team. Mod by Chris Newton
 */
class MY_Log extends CI_Log {
    /**
     * Constructor
     *
     * @access    public
     * @param    array the array of loggable items
     * @param    string    the log file path
     * @param     string     the error threshold
     * @param    string    the date formatting codes
     */
    public function __construct()
    {
        parent::__construct();
        $config =& get_config();

        if (isset ($config['show_in_log']))
        {
            $show_in_log=$config['show_in_log'];
        }
        else
        {
            $show_in_log="";
        }

        if (is_array($show_in_log))
        {
            $this->_logging_array = $show_in_log;
        }
    }

    // --------------------------------------------------------------------
    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @access    public
     * @param    string    the error level
     * @param    string    the error message
     * @param    bool    whether the error is a native PHP error
     * @return    bool
     */
    function write_log($level = 'error', $msg, $php_error = FALSE)
    {
        if ($this->_enabled === FALSE)
        {
            return FALSE;
        }

        $level = strtoupper($level);

        if (isset($this->_logging_array))
        {
            if ((! in_array($level, $this->_logging_array)) && (! empty($this->_logging_array)))
            {
                return FALSE;
            }
        }
        else
        {
            if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
            {
                return FALSE;
            }
        }



        $filepath = $this->_log_path.'log-'.date('Y-m-d').EXT;
        $message  = '';

        if ( ! file_exists($filepath))
        {
            $message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
        }

        if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
        {
            return FALSE;
        }

        $message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";

        flock($fp, LOCK_EX);
        fwrite($fp, $message);
        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($filepath, FILE_WRITE_MODE);
        return TRUE;
    }
}

/* End of File: my_log.php */
/* Location: ./application/libraries/MY_log.php */