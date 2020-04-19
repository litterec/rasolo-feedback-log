<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://ra-solo.com.ua
 * @since      1.0.0
 *
 * @package    Rasolo_Feedback_Log
 * @subpackage Rasolo_Feedback_Log/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rasolo_Feedback_Log
 * @subpackage Rasolo_Feedback_Log/admin
 * @author     Andrew V. Galagan <andrew.galagan@gmail.com>
 */
class Rasolo_Feedback_Log_Admin {

    static public $LOG_MAX_SIZE = 40;  // How many log records allowed
    static public $NAME_MAX_SIZE = 50;  // Max lenght of user name
    static public $PHONE_MAX_SIZE = 50;  // Max lenght of user name
    static public $EMAIL_MAX_SIZE = 80;  // Max length of user name
    static public $SUBJECT_MAX_SIZE = 225;  // Max length of user name
    static public $MESSAGE_MAX_SIZE = 2000;  // Max length of user name
    static public $IP_MAX_SIZE = 300;  // Max size of the array of user's IP
    static private $SUCCESS_MESSAGE = 'Your message has been sent.';
//    static private $ERROR_MESSAGE = 'Your message has not been sent. Please try again.';
    static private $MESSAGE_EMPTY_FIELDS = 'Please fill in all required fields.';
//    static private $UNKNOWN_ERROR = 'An unknown error has been occurred.';
//    static private $COMMON_ERROR = 'A common error has been occurred.';
    static private $ERROR_TRANSFORM = 'Your message has not been sent. Data conversion error.';
    static private $BAD_CAPTCHA_01 = 'Failed to check captcha, error no. ';
    static private $BAD_CAPTCHA_02 = 'No Google CAPTCHA test data.';
    static private $BAD_CAPTCHA_03 = 'Take the test to distinguish you from the robot.';
    static private $NOT_A_HUMAN = 'An error has been occurred while checking the captcha, most likely you are a robot.';
    static private $YOU_HAVE_TO_WAIT = 'It is not a good idea to send messages so often. Please wait a moment: ';

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    private $log;
    private $external_api_key;
    private $captcha_secret_key;
    private $delete_success=false;
    private $error_msg=false;
    private $error_code=false;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->log= array();
        $this->external_api_key= false;
        $this->captcha_secret_key= false;
        $this->error_code= false;
        $this->error_msg= '';

        $log_settings=get_option(Rasolo_Feedback_Log::$LOG_SETTINGS_KEY);
        $log_sett_unser=@unserialize($log_settings);

//        echo '<pre>';
//        print_r($this);
//        print_r($log_settings);
//        echo '</pre>';
//        die('$this->external_api_key_233255');


        if(empty($log_sett_unser)){
            $log_sett_unser=array();
            update_option(Rasolo_Feedback_Log::$LOG_SETTINGS_KEY,serialize($log_sett_unser));
        };
        $this->external_api_key=isset($log_sett_unser['external_api_key'])?$log_sett_unser['external_api_key']:false;
        $this->captcha_secret_key=isset($log_sett_unser['captcha_secret_key'])?$log_sett_unser['captcha_secret_key']:false;

        $log_option=get_option(Rasolo_Feedback_Log::$LOG_OPTION_KEY);
        if(empty($log_option)){
            update_option(Rasolo_Feedback_Log::$LOG_OPTION_KEY,serialize([]));
            return;
        };
        $log_unser=@unserialize($log_option);
        if(!is_array($log_unser)){
            update_option(Rasolo_Feedback_Log::$LOG_OPTION_KEY,serialize([]));
            return;
        };
        $this->log=$log_unser;

        if(isset($_POST['my_orders']) && isset($_POST['delete_anyway']) ){
            $is_deleted=false;
            foreach ($_POST['my_orders'] as $next_order) {
                if(true===$this->remove_msg($next_order)){
                    $is_deleted=true;
                };
            };
            if($is_deleted){
                $this->write_log();
            }
//            $deletion_success=$this->has_deleted();
        };

        $let_us_save=false;
        if(isset($_POST['external_api_key']) ){
            $let_us_save=true;
            $this->external_api_key=sanitize_text_field($_POST['external_api_key']);
        }
        if(isset($_POST['captcha_secret_key']) ){
            $let_us_save=true;
            $this->captcha_secret_key=sanitize_text_field($_POST['captcha_secret_key']);
        }
        if($let_us_save){
            $this->write_opts();
        }

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rasolo_Feedback_Log_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rasolo_Feedback_Log_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rasolo-feedback-log-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rasolo_Feedback_Log_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rasolo_Feedback_Log_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rasolo-feedback-log-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function get_page_arguments(){
        return array(
            __( 'Customer feedback protocols', Rasolo_Feedback_Log::$TEXTDOMAIN ),
            __( 'Feedback log', Rasolo_Feedback_Log::$TEXTDOMAIN ),
            'view_woocommerce_reports',
            'admin_orders',
            array($this, 'my_orders_options')
        );
    }
    public function my_orders_options(){
//        die('asdadas');
//        $this->delete_success=FALSE;
        $this->show_msg_html();
    }

    public function show_rasolo_logo(){

    }

    private function is_logo_set(){
        global $wp_filter;

        foreach ( $wp_filter as $key => $val ){
            if('admin_notices'==$key){
                $val_exported = var_export( $val, TRUE );
                $show_rasolo_logo_pos=strpos($val_exported,'show_rasolo_logo');
                if(false!==$show_rasolo_logo_pos){
                    return true;
                };
            };
        };
        return false;
    }

    public function show_msg_html(){
//        $this->delete_success=false;

//        $path1=__('Feedback log','rasolo-feedback-log');
//        $path2=Rasolo_Feedback_Log::$TEXTDOMAIN;
//        myvar_dump($path1,'$path1');
//        myvar_dump($path2,'$path2');
//        die('load_plugin_textdomain_34232_2_22_11132'.$path1);
//        myvar_dump($wp_filter,'$wp_filter _234223');
//        die('$wp_filter');

        if(!$this->is_logo_set()){
            $this->show_rasolo_logo();
        };

        ?>
        <h1 class="left_h1"><?php _e( 'Site Visitor Data', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?></h1>
        <hr>

        <form method="POST" action="">
<table id="user_orders_list"><tbody>
    <?php
        $total_rows=count($this->log);
        if( $total_rows>0){

    ?>
    <tr><th><?php _e( 'Date', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?></th><th>IP</th><th><?php _e( 'Name', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?></th><th><?php _e( 'Phn', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?></th><th>E-mail</th><th><?php _e( 'Title', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?></th><th><?php _e( 'Content', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?></th><th><img
 src="<?php echo this_theme_url(); ?>img/delete_record.png"></th></tr><?php
            } else {
            echo '<h2>'.__( 'There are no records in the feedback log', Rasolo_Feedback_Log::$TEXTDOMAIN ).'.</h2>';
        };
        $n_columns=7;
        $order_cntr=0;   // An order counter
        foreach($this->log as $msg_key=>$nth_msg){
            $order_cntr++;
            if($order_cntr>self::$LOG_MAX_SIZE){
                ?><tr><td id="extra_orders" colspan="<?php echo $n_columns;
                ?>">*** <?php _e( 'Some old records remained so far. Number', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?>: <?php
                    echo ($total_rows-self::$LOG_MAX_SIZE); ?>. ***</td></tr>
                <?php
                break;
            };

            $sel='';
            if(isset($_POST['my_orders'])){
                if(in_array($msg_key,$_POST['my_orders'])){
                    $sel=' checked';
                };
//    } else {
//        echo '<!-- POST WITH $my_order->orderid='.$my_order->orderid.' does not exist -->'.chr(10);
            };

            ?>

<tr>
    <td><?php echo date("d-m-Y G:i",$nth_msg['time']); ?></td>
    <td><?php echo $nth_msg['ip']; ?></td>
    <td><?php echo $nth_msg['u_name']; ?></td>
    <td><?php echo $nth_msg['u_phone']; ?></td>
    <td><?php echo $nth_msg['u_mail']; ?></td>
    <td><?php echo $nth_msg['sub']; ?></td>
    <td><?php echo $nth_msg['mes']; ?></td>
    <td><input<?php echo $sel;
        ?> type="checkbox" value="<?php echo $msg_key; ?>" name="my_orders[]" /></td>

</tr>
            <?php

        };

    ?>
    </tbody></table>

        <div id="delete_submit_wrap">
            <input id="delete_orders_submit" type="submit"
                   value="<?php _e( 'Delete selected', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?>" class="button-primary"/>
        </div>
        <?php
        if(isset($_POST['my_orders']) && !isset($_POST['delete_anyway'])){
            ?>
            <b><?php _e( 'Confirm deletion of selected entries', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?></b>: <input type="checkbox" name="delete_anyway" />
        <?php
        };
        ?>


        <?php if($this->delete_success){ ?>

            <div id="note_orders" onClick="jQuery('#note_orders').hide();"><?php _e( 'Selected records have been deleted successfully', Rasolo_Feedback_Log::$TEXTDOMAIN );
            ?></div>
            <script>
                jQuery("#note_orders").delay(5000).queue(function(n) {
                    jQuery(this).hide(); n();
                });
            </script>

        <?php } else { ?>
            <!-- There was not success unfortunately -->

        <?php }; ?>
        </form>
<?php

if(!current_user_can('manage_options')){
    return;
}
?>      <hr>
<h2 class="title"><?php _e( 'Admin options', Rasolo_Feedback_Log::$TEXTDOMAIN );
?></h2>
<form method="POST" action="">
<table class="form-table" role="presentation">
<tbody>
<tr>
<th scope="row"><label for="mailserver_login"><?php _e( 'External API password', Rasolo_Feedback_Log::$TEXTDOMAIN );
?></label></th>
<td><input name="external_api_key" type="text" id="external_api_key" value="<?php
    echo $this->external_api_key;
    ?>" class="regular-text ltr"></td>
</tr>
<tr>
<th scope="row"><label for="mailserver_login"><?php _e( 'Captcha secret key', Rasolo_Feedback_Log::$TEXTDOMAIN );
?></label></th>
<td><input name="captcha_secret_key" type="text" id="captcha_secret_key" value="<?php
    echo $this->captcha_secret_key;
    ?>" class="regular-text ltr"></td>
</tr>
<tr>
	</tbody></table>

<p class="submit"><input type="submit" name="submit_rsfb_opts"
 id="submit_rsfb_opts" class="button button-primary" value="<?php _e( 'Save changes', Rasolo_Feedback_Log::$TEXTDOMAIN );
?>"></p>

</form>
<?php


    } // The end of show_msg_html

    public function create_adm_menu(){
//        die('create_adm_menu_3424232');
        add_menu_page(
            __( 'Customer feedback protocols', Rasolo_Feedback_Log::$TEXTDOMAIN ),
            __( 'Feedback log', Rasolo_Feedback_Log::$TEXTDOMAIN ),
            'view_woocommerce_reports',
            'admin_orders',
            array($this, 'my_orders_options'),
            'dashicons-id-alt'

        );

    }

    public function has_deleted(){
        return $this->delete_success;
    }

    public function get_error_msg(){
        return $this->error_msg;
    }

    public function get_error_code(){
        return $this->error_code;
    }

    public function remove_msg($some_msg_id){
        if(!is_numeric($some_msg_id)){
            $this->error_msg=__( 'The input log index is not numeric', Rasolo_Feedback_Log::$TEXTDOMAIN );
            $this->error_code=273;
            return false;
        };
        if(!isset($this->log[$some_msg_id])) {
            $this->error_msg=__( 'Such log item is not in array so far', Rasolo_Feedback_Log::$TEXTDOMAIN );
            $this->error_code=274;
            return false;
        };
//        die('We are deleting now');
        unset($this->log[$some_msg_id]);
        $this->delete_success=true;
        return true;
    }

    private function write_opts(){
        $log_settings=array(
            'external_api_key'=>$this->external_api_key,
            'captcha_secret_key'=>$this->captcha_secret_key,
        );
        update_option(Rasolo_Feedback_Log::$LOG_SETTINGS_KEY,serialize($log_settings));
        return true;
    }
    private function write_log(){
        update_option(Rasolo_Feedback_Log::$LOG_OPTION_KEY,serialize($this->log));
        return true;
    }

    public function add_msg($u_name,$u_phone,$u_mail,$u_sub,$u_mes,$u_ip){
        $array_for_next_line=array();
        $array_for_next_line['u_name']=self::limit_txt($u_name,self::$NAME_MAX_SIZE);
        $array_for_next_line['u_phone']=self::limit_txt($u_phone,self::$PHONE_MAX_SIZE).'debug07';
        $array_for_next_line['u_mail']=self::limit_txt($u_mail,self::$EMAIL_MAX_SIZE);
        $array_for_next_line['sub']=self::limit_txt($u_sub,self::$SUBJECT_MAX_SIZE);
        $array_for_next_line['mes']=self::limit_txt($u_mes,self::$MESSAGE_MAX_SIZE);
        $array_for_next_line['ip']=self::limit_txt($u_ip,self::$IP_MAX_SIZE);
//        myvar_dump($this,'$this_before');
        if(function_exists('current_time')){
            $array_for_next_line['time']=strval(current_time('timestamp') );
        } else {
            $array_for_next_line['time']=strval(time());
        };
        $this->log[]=$array_for_next_line;
//        myvar_dump($array_for_next_line,'$array_for_next_line');
//        myvar_dd($this,'$this_3422334_after');
        $this->write_log();

    }

    private static function limit_txt($src_str,$max_ln){
        if ( ! function_exists( 'limit_str' ) ) {
            return $src_str;
        } else {
            return limit_str($src_str,$max_ln);
        }
    }  // The end of limit_txt

    public static function check_email($email)
    {
        if(@preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){
            return true;
        } else {
            return false;
        }
    } // The end of check_email


    private static function get_post_key($srcpostkey,$reply_if_absent=false)
    {
// Clears data for database
        if(empty($_POST[$srcpostkey])){
            if($reply_if_absent===false){
                return false;
            } else {
                return sprintf(__('The field %s has been remained blank.',Rasolo_Feedback_Log::$TEXTDOMAIN ),$reply_if_absent);
            }
        }
        $srcstr=$_POST[$srcpostkey];
        $local_str = strip_tags($srcstr);
        $local_str = stripslashes($local_str);
        $local_str = htmlspecialchars($local_str);
        return $local_str;

    } //The end of get_post_key

    public function get_fb(){

if(empty($_POST['is_human'])){
                //myvar_dump($_POST,'$POST__22222__');
    //exit;
    if(empty($_POST['g-recaptcha-response'])){
        echo json_encode(array(
                'info' => 'error',
                'status' => 981,
                'data' => false,
                'msg' => __(self::$BAD_CAPTCHA_02, Rasolo_Feedback_Log::$TEXTDOMAIN).'<br>'.
                            __(self::$BAD_CAPTCHA_03, Rasolo_Feedback_Log::$TEXTDOMAIN)
                        ));
        exit;
    };


    $params = array ('secret' => $this->captcha_secret_key,
        'response' => $_POST['g-recaptcha-response'],
        'remoteip' => $_SERVER['REMOTE_ADDR']);

    $query = http_build_query ($params);

    $contextData = array (
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
            "Content-Length: ".strlen($query)."\r\n".
            "User-Agent:MyAgent/1.0\r\n",
        'content'=> $query );

    // Create context resource for our request
    $context = stream_context_create (array ( 'http' => $contextData ));

    // Read page rendered as result of your POST request
    $result =  file_get_contents (
        'https://www.google.com/recaptcha/api/siteverify',  // page url
        false,
        $context);


    if (empty($result)) {
        echo json_encode(array(
            'info' => 'error',
            'status' => 982,
            'data' => false,
            'msg' => __(self::$BAD_CAPTCHA_01,Rasolo_Feedback_Log::$TEXTDOMAIN).' 82547.'
                        ));
        exit;
    };

    try {
        $res_decoded=json_decode($result,true);
    } catch(Exception $e) {
            echo json_encode(
                array(
                       'is_human' => true,
                       'info' => 'error',
                       'data' => false,
                       'status' => 912,
                       'msg' =>__('Error have been encountered while decoding CAPTCHA results. Error code is ',Rasolo_Feedback_Log::$TEXTDOMAIN).
                            '40591 ('.$e->getCode().
                           '). '.
                           __('Message is: ',Rasolo_Feedback_Log::$TEXTDOMAIN).
                           $e->getMessage().'.'
                      )
            );
            exit;

    }

    //$post_keys=array_keys($_POST);


    if (empty($res_decoded['success'])) {
        echo json_encode(array(
            'info' => 'error',
            'status' => 982,
            'data' => false,
            'msg' => __(self::$BAD_CAPTCHA_01,Rasolo_Feedback_Log::$TEXTDOMAIN).' 82548.'
                        ));
        exit;
    };

    if(!is_bool($res_decoded['success'])) {
        echo json_encode(array(
            'info' => 'error',
            'status' => 983,
            'data' => false,
            'msg' => __(self::$BAD_CAPTCHA_01,Rasolo_Feedback_Log::$TEXTDOMAIN).' 82549.'
                        ));
        exit;
    };

    if(!$res_decoded['success']) {
        echo json_encode(array(
            'info' => 'error',
            'status' => 984,
            'data' => false,
            'msg' => __(self::$NOT_A_HUMAN,Rasolo_Feedback_Log::$TEXTDOMAIN)
        ));
        exit;
    };




};  // The end of captcha check

if(empty($_POST['usr_email']) || empty($_POST['msg_txt'])){
        echo json_encode(array(
                'is_human' => true,
                'info' => 'error',
                'status' => 901,
                'data' => false,
                'msg' => self::$MESSAGE_EMPTY_FIELDS
                    ));
    exit;
}

$name = self::get_post_key('usr_name',__('User name',Rasolo_Feedback_Log::$TEXTDOMAIN));

$usr_phone = self::get_post_key('usr_phone',__('User phone',Rasolo_Feedback_Log::$TEXTDOMAIN));
$usr_phone=str_replace(' ','',$usr_phone);
$mail = self::get_post_key('usr_email',__('User E-mail',Rasolo_Feedback_Log::$TEXTDOMAIN));
$subjectForm =self::get_post_key('msg_subj',__('Message title',Rasolo_Feedback_Log::$TEXTDOMAIN));
$messageForm = self::get_post_key('msg_txt',__('Message body',Rasolo_Feedback_Log::$TEXTDOMAIN));

if(mb_strlen($name,'utf-8')<2){
    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 985,
        'data' => mb_strlen($name,'utf-8'),
        'msg' => __('Please enter your name, at least 3 characters.',Rasolo_Feedback_Log::$TEXTDOMAIN)
                            ));
    exit;
} else if(empty($mail) && check_email($mail) == false){
    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 986,
        'data' => false,
        'msg' => __('Enter the correct E-mail address.',Rasolo_Feedback_Log::$TEXTDOMAIN)
                            ));
    exit;
} else if(mb_strlen($messageForm,'utf-8')<8){
    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 987,
        'data' => mb_strlen($messageForm,'utf-8'),
        'msg' => __('Enter your message, min. 8 characters.',Rasolo_Feedback_Log::$TEXTDOMAIN)
                            ));
    exit;
} else if(mb_strlen($usr_phone ,'utf-8')<>10){
    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 1901,
        'data' => mb_strlen($usr_phone,'utf-8'),
        'msg' => __('The phone number must consist of 10 digits, plus spaces.',Rasolo_Feedback_Log::$TEXTDOMAIN)
                            ));
    exit;
} else if (!preg_match('/^[0-9]{10}$/', $usr_phone)){
    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 1902,
        'data' => mb_strlen($usr_phone,'utf-8'),
        'msg' => __('The phone number should consist of only 10 numeric characters, plus spaces.',Rasolo_Feedback_Log::$TEXTDOMAIN)
                            ));
    exit;
}

$my_url='https://cp.ra-solo.com.ua/extrnlmsgs';

$request_data=array();
//        $request_data['rasolo_passw']=$passw_encrypted;
//        $request_data['hash']='NjQ2YjVlN2VhZThhODk4YjQzOTYxZDZkNThlNzU2MjBmYjUxODJlNzcwZDljNjQ2';
$request_data['srv_name']=$_SERVER['SERVER_NAME'];
$request_data['site_title']=get_bloginfo();
//        $request_data['email']=$mail;
$request_data['user_name']=$name;
$request_data['user_mail']=$mail;
$request_data['user_phone']=$usr_phone;
$request_data['user_ip']=$_SERVER['REMOTE_ADDR'];
$request_data['fb_subj']=$subjectForm;
$request_data['fb_mess']=$messageForm;
$request_data['hash']=$this->external_api_key;

$crnt_user=wp_get_current_user();
if(!empty($crnt_user->ID)){
    $request_data['auth_id']=$crnt_user->ID;
    $crnt_user_data=$crnt_user->data;
    $request_data['auth_nick']=$crnt_user_data->user_login;
} else {
    $request_data['auth_id']=false;
    $request_data['auth_nick']=false;
}

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $my_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,  http_build_query($request_data));

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
//get response from resource

try {

    $response = curl_exec($ch);

} catch(Exception $e) {

    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 1903,
        'data' => '',
        'msg' => __('An error occurred during an external request. The code is: ',Rasolo_Feedback_Log::$TEXTDOMAIN).
            $e->getCode().'.'.__(' Message is: ',Rasolo_Feedback_Log::$TEXTDOMAIN).$e->getMessage().'.'
                            ));
    exit;

}

if(!$response){
    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 1904,
        'data' => '',
        'msg' => __('Missing external query result.',Rasolo_Feedback_Log::$TEXTDOMAIN).' '.curl_error($ch)
                            ));
    exit;
}


try {

    $laravel_result = json_decode($response,true);

} catch(Exception $e) {

    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 1905,
        'data' => '',
        'msg' => __('An error occurred during an external request. The code is: ',Rasolo_Feedback_Log::$TEXTDOMAIN).
            $e->getCode().'.'.__(' Message is: ',Rasolo_Feedback_Log::$TEXTDOMAIN).$e->getMessage().'.'
                            ));
    exit;

}

if(!is_array($laravel_result)){

    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 1906,
        'data' => '',
        'msg' => __(self::$ERROR_TRANSFORM,Rasolo_Feedback_Log::$TEXTDOMAIN).' '.
                  __('Code is ',Rasolo_Feedback_Log::$TEXTDOMAIN).'01.'
                ));
    exit;
}

if(empty($laravel_result['code'])){
    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 1907,
        'data' => '',
        'msg' => __(self::$ERROR_TRANSFORM,Rasolo_Feedback_Log::$TEXTDOMAIN).' '.
                   __('Code is ',Rasolo_Feedback_Log::$TEXTDOMAIN).'02.'
                ));
    exit;
}

if(10165==$laravel_result['code']){
    $time_to_wait=600;
    if(!empty($laravel_result['data'])){
        $time_to_wait=intval($laravel_result['data']);
    }
    if(function_exists('time_diff')){
        $time_in_words=time_diff($time_to_wait);
    } else {
        $time_in_words=$time_to_wait.__(' second(s).',Rasolo_Feedback_Log::$TEXTDOMAIN);
    }

    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 1908,
        'data' => '',
        'msg' => __(self::$YOU_HAVE_TO_WAIT,Rasolo_Feedback_Log::$TEXTDOMAIN).$time_in_words
                            ));
    exit;
}

if(200<>$laravel_result['code']){
    echo json_encode(array(
        'is_human' => true,
        'info' => 'error',
        'status' => 1909,
        'data' => '',
        'msg' => __(self::$ERROR_TRANSFORM,Rasolo_Feedback_Log::$TEXTDOMAIN).' '.
            __('Code is ',Rasolo_Feedback_Log::$TEXTDOMAIN).$laravel_result['code'].'.'
                            ));
     exit;
}

echo json_encode(array(
    'is_human' => true,
    'info' => 'success',
        'status' => 1908,
        'data' => '',
        'msg' => __(self::$SUCCESS_MESSAGE,Rasolo_Feedback_Log::$TEXTDOMAIN)
                        ));
exit;


//$post_keys=array_keys($res_decoded);
//echo json_encode(array(
//    'is_human' => true,
//    'info' => 'error',
//    'status' => 987,
//    'data' => 'somedata',
//    'msg' => 'ajaxforevermore!E!'.$result.'{== gettype='.gettype($res_decoded).'{=='.implode(', ',$post_keys)
//));
//exit;






       } // The end of get_fb


/*

if(isset($_POST['my_orders']) && isset($_POST['delete_anyway']) ){

//    echo '<!-- delete_attempt!:  -->'.chr(10);

    foreach ($_POST['my_orders'] as $next_order) {
//          echo '<!-- attempt for $next_order='.$next_order.'  -->'.chr(10);
          if(is_numeric($next_order)){
              $wpdb->delete( 'wp_det_orders', array( 'orderid' => $next_order ), array( '%d' ) );
          };
    };

    $deletion_success=TRUE;

};


*/

}
