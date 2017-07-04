<?php namespace Myth\Auth\Models;

use CodeIgniter\Model;

/**
 * Class Login_model
 *
 * Provides methods for interfacing with ALL login-related information
 * for the Auth classes.
 *
 * By default it will use the 'auth_logins' for any CIDbModel-related calls,
 * but methods are included to work with 'auth_login_attempts' and 'auth_tokens' as well.
 */
class LoginModel extends Model {

    protected $table_name = 'auth_logins';

	protected $useTimestamps = false;

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Login Attempts
    //--------------------------------------------------------------------

    /**
     * Records a login attempt. This is used to implement
     * throttling of application, ip address and user login attempts.
     *
     * @param $ipAddress
     * @param $userID
     *
     * @return void
     */
    public function recordLoginAttempt($ipAddress, $userID = null)
    {
        $datetime = date('Y-m-d H:i:s');

        // log attempt for app
        $data = [
            'type' => 'app',
            'datetime' => $datetime
        ];

        $this->db->insert('auth_login_attempts', $data);

        // log attempt for ip address
        if (! empty($ipAddress))
        {
            $data = [
                'type'       => 'ip',
                'ip_address' => $ipAddress,
                'datetime'   => $datetime
            ];

            $this->db->insert('auth_login_attempts', $data);
        }

        // log attempt for user
        if ($userID)
        {
            $data = [
                'type' => 'user',
                'user_id' => $userID,
                'datetime' => $datetime
            ];

            $this->db->insert('auth_login_attempts', $data);
        }
    }

    //--------------------------------------------------------------------

    /**
     * Purges all login attempt records from the database.
     *
     * @param $ipAddress
     * @param $userID
     *
     * @return mixed
     */
    public function purgeLoginAttempts($ipAddress, $userID)
    {
	    if ($ipAddress)
        {
            $this->db->where('ip_address', $ipAddress);
        }

        if ($userID)
        {
            $this->db->or_where('user_id', $userID);
        }

        return $this->db->delete('auth_login_attempts');
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Logins
    //--------------------------------------------------------------------

    /**
     * Records a successful login. This stores in a table so that a
     * history can be pulled up later if needed for security analyses.
     *
     * @param $user
     */
    public function recordLogin($user)
    {
        $data = [
            'user_id'    => (int)$user['id'],
            'datetime'   => date('Y-m-d H:i:s'),
            'ip_address' => $this->input->ip_address()
        ];

        return $this->db->insert('auth_logins', $data);
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Tokens
    //--------------------------------------------------------------------

    /**
     * Generates a new token for the rememberme cookie.
     *
     * The token is based on the user's email address (since everyone will have one)
     * with the '@' turned to a '.', followed by a pipe (|) and a random 128-character
     * string with letters and numbers.
     *
     * @param $user
     * @return mixed
     */
    public function generateRememberToken($user)
    {
        helper('text');

        return str_replace('@', '.', $user['email']) .'|' . random_string('alnum', 128);
    }

    //--------------------------------------------------------------------

    /**
     * Hashes the token for the Remember Me Functionality.
     *
     * @param $token
     * @return string
     */
    public function hashRememberToken($token)
    {
        return sha1(config_item('auth.salt') . $token);
    }

    //--------------------------------------------------------------------

    /**
     * Deletes a single token that matches the email/token combo.
     *
     * @param $email
     * @param $token
     * @return mixed
     */
    public function deleteRememberToken($email, $token)
    {
        $where = [
            'email' => $email,
            'hash'  => $this->hashRememberToken($token)
        ];

        $this->db->delete('auth_tokens', $where);
    }

    //--------------------------------------------------------------------

    /**
     * Removes all persistent login tokens (RememberMe) for a single user
     * across all devices they may have logged in with.
     *
     * @param $email
     * @return mixed
     */
    public function purgeRememberTokens($email)
    {
        return $this->db->delete('auth_tokens', ['email' => $email]);
    }

    //--------------------------------------------------------------------


    /**
     * Purges the 'auth_tokens' table of any records that are too old
     * to be of any use anymore. This equates to 1 week older than
     * the remember_length set in the config file.
     */
    public function purgeOldRememberTokens()
    {
        if (! config_item('auth.allow_remembering'))
        {
            return;
        }

        $date = time() - config_item('auth.remember_length') - 604800; // 1 week
        $date = date('Y-m-d 00:00:00', $date);

        $this->db->where('created <=', $date)
                 ->delete('auth_tokens');
    }

    //--------------------------------------------------------------------

    /**
     * Gets the timestamp of the last attempted login for this user.
     *
     * @param $ip_address
     * @param $user_id
     * @return int|null
     */
    public function lastLoginAttemptTime($ip_address, $user_id)
    {
        $query = $this->db->where('type', 'ip')
                          ->where('ip_address', $ip_address)
                          ->order_by('datetime', 'desc')
                          ->limit(1)
                          ->get('auth_login_attempts');

        $last_ip = ! $query->num_rows() ? 0 : strtotime($query->row()->datetime);

        if (! $user_id)
        {
            return $last_ip;
        }

        $query = $this->db->where('type', 'user')
                          ->where('user_id', $user_id)
                          ->order_by('datetime', 'desc')
                          ->limit(1)
                          ->get('auth_login_attempts');

        $last_user = ! $query->num_rows() ? 0 : strtotime($query->row()->datetime);

        return ($last_user > $last_ip) ? $last_user : $last_ip;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the number of failed login attempts for a given type.
     *
     * @param $ip_address
     * @param $user_id
     * @return int
     */
    public function countLoginAttempts($ip_address, $user_id)
    {
        $count_ip = $this->db->where('type', 'ip')
                             ->where('ip_address', $ip_address)
                             ->count_all_results('auth_login_attempts');

        if (! $user_id)
        {
            return $count_ip;
        }

        $count_user = $this->db->where('type', 'user')
                               ->where('user_id', $user_id)
                               ->count_all_results('auth_login_attempts');

        return ($count_user > $count_ip) ? $count_user : $count_ip;
    }

    //--------------------------------------------------------------------

}
