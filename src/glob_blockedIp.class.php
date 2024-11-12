<?php




/**
 * @global int BLOCKED_IP_HOURS_INTERVAL
 */
class glob_blockedIp extends glob_dbaseTablePrimary {

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
	public $ip;

    /**
     * @var string
     */
    public $payload;

    /**
     * @var string
     */
    public $backtrace;

    /**
     * @var string
     */
    public $timeCreated;




    /**
     * @global PDO $pdo
     * @global int BLOCKED_IP_HOURS_INTERVAL
     * @static
     * @return void
     */
    public static function db_deleteOld() {

        global $pdo;

        $blockedIpHoursInterval = 1;

        if ( defined( 'BLOCKED_IP_HOURS_INTERVAL' ) ) {

            $blockedIpHoursInterval = BLOCKED_IP_HOURS_INTERVAL;

        }

        $query = 'DELETE FROM `' . __CLASS__ . '` WHERE timeCreated < DATE_SUB( NOW(), INTERVAL ' . $blockedIpHoursInterval . ' HOUR);';

        $stmt = $pdo->prepare( $query );

        $stmt->execute();

    }

    /**
     * @static
     * @return void
     */
    public static function block() {

        if ( empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) === false ) {

            $ip = new self();
            $ip->ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
            $ip->set_payload();
            $ip->set_backtrace();
            $ip->db_insert();

        }

        if ( empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) === false ) {

            $ip = new self();
            $ip->ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
            $ip->set_payload();
            $ip->set_backtrace();
            $ip->db_insert();

        }

        if ( empty( $_SERVER[ 'REMOTE_ADDR' ] ) === false ) {

            $ip = new self();
            $ip->ip = $_SERVER[ 'REMOTE_ADDR' ];
            $ip->set_payload();
            $ip->set_backtrace();
            $ip->db_insert();

        }

    }

    /**
     * @global int BLOCKED_IP_HOURS_INTERVAL
     * @static
     * @return boolean
     */
    public static function isBlocked() {

        if ( defined( 'BLOCKED_IP_HOURS_INTERVAL' ) ) {

            self::db_deleteOld();

        }

        if ( empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) === false ) {

            $blocked = glob_blockedIp::db_getAllWhere( 'ip', $_SERVER[ 'HTTP_CLIENT_IP' ] );

            if ( count( $blocked ) > 0 ) {

                return true;

            }

        }

        if ( empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) === false ) {

            $blocked = glob_blockedIp::db_getAllWhere( 'ip', $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] );

            if ( count( $blocked ) > 0 ) {

                return true;

            }

        }

        if ( empty( $_SERVER[ 'REMOTE_ADDR' ] ) === false ) {

            $blocked = glob_blockedIp::db_getAllWhere( 'ip', $_SERVER[ 'REMOTE_ADDR' ] );

            if ( count( $blocked ) > 0 ) {

                return true;

            }

        }

        return false;

    }



    /**
     * @return glob_blockedIp
     */
    public function __construct() {

        return $this;

    }

    /**
     * @return void
     */
    public function set_payload() {

        $payload = [
            'SERVER' => $_SERVER,
            'POST' => $_POST,
            'GET' => $_GET
        ];

        $this->payload = json_encode( $payload );

    }

    /**
     * @return void
     */
    public function set_backtrace() {

        $this->backtrace = json_encode( debug_backtrace() );

    }

}