<?php namespace Myth\Auth\Controllers;

use App\Models\UserModel;
use App\Controllers\BaseController;
use \Myth\Auth\Authenticate\LocalAuthentication;

class Auth extends BaseController
{
	protected $config;

    public function __construct(...$params)
    {
        parent::__construct(...$params);

        $this->config = new \Myth\Auth\Config\Auth;
    }

    //--------------------------------------------------------------------

    public function login()
    {
        helper('form');

        $auth = new LocalAuthentication($this->config);
        $auth->useModel(new UserModel());

        $redirectURL = session('redirect_url');

        // No need to login again if they are already logged in...
        if ($auth->isLoggedIn())
        {
            unset($_SESSION['redirect_url']);
            redirect($redirectURL);
        }

        $this->layout = 'login';
        $this->render('Myth\Auth\Views\login');
    }

    //--------------------------------------------------------------------

	public function attemptLogin()
	{
		$auth = new LocalAuthentication($this->config);
		$auth->useModel(new UserModel());
		$redirectURL = session('redirect_url');

		$post_data = [
			'email'    => $this->input->post('email'),
			'password' => $this->input->post('password')
		];

		$remember = (bool)$this->input->post('remember');

		if ($auth->login($post_data, $remember))
		{
			// Is the user being forced to reset their password?
			if ($auth->user()['force_pass_reset'] == 1)
			{
				redirect('change_pass');
			}

			unset($_SESSION['redirect_url']);
			$this->setMessage(lang('auth.did_login'), 'success');
			redirect($redirectURL);
		}

		$this->setMessage(lang('auth.invalid_user'), 'danger');

		redirect_with_input('login');
	}

	//--------------------------------------------------------------------

	public function logout()
    {
	    $auth = new LocalAuthentication($this->config);
	    $auth->useModel(new UserModel());

        if ($auth->isLoggedIn())
        {
            $auth->logout();

            $this->setMessage(lang('auth.did_logout'), 'success');
        }

        redirect('/');
    }

    //--------------------------------------------------------------------

    public function register()
    {
        helper('form');

//        $this->addScript('register.js');
        $this->layout = 'login';

        $this->render('Myth\Auth\Views\register');
    }

    //--------------------------------------------------------------------

	public function attemptRegister()
	{
		helper('form');

		$auth = new LocalAuthentication($this->config);
		$auth->useModel(new UserModel());

		$postData = [
			'first_name'   => $this->input->post('first_name'),
			'last_name'    => $this->input->post('last_name'),
			'email'        => $this->input->post('email'),
			'username'     => $this->input->post('username'),
			'password'     => $this->input->post('password'),
			'pass_confirm' => $this->input->post('pass_confirm')
		];

		if ($auth->registerUser($postData))
		{
			$this->setMessage(lang('auth.did_register'), 'success');
			redirect('login');
		}

		$this->setMessage($auth->error(), 'danger');
		redirect_with_input('register');
	}

	//--------------------------------------------------------------------

    public function activate_user()
    {
        $this->load->helper('form');

        if ($this->input->post())
        {
            $auth = new LocalAuthentication();
            $this->load->model('user_model');
            $auth->useModel($this->user_model);

            $post_data = [
                  'email' => $this->input->post('email'),
                  'code'  => $this->input->post('code')
            ];

            if ($auth->activateUser($post_data))
            {
                $this->setMessage(lang('auth.did_activate'), 'success');
                redirect( Route::named('login') );
            }
            else
            {
                $this->setMessage($auth->error(), 'danger');
            }
        }

        $data = [
            'email' => $this->input->get('e'),
            'code'  => $this->input->get('code')
        ];

        $this->themer->setLayout('login');
        $this->render($data);
    }

    //--------------------------------------------------------------------


    public function forgot_password()
    {
        $this->load->helper('form');

        if ($this->input->post())
        {
            $auth = new LocalAuthentication();
            $this->load->model('user_model');
            $auth->useModel($this->user_model);

            if ($auth->remindUser($this->input->post('email')))
            {
                $this->setMessage(lang('auth.send_success'), 'success');
                redirect( Route::named('reset_pass') );
            }
            else
            {
                $this->setMessage($auth->error(), 'danger');
            }
        }

        $this->themer->setLayout('login');
        $this->render();
    }

    //--------------------------------------------------------------------

    public function reset_password()
    {
        $this->load->helper('form');

        if ($this->input->post())
        {
            $auth = new LocalAuthentication();
            $this->load->model('user_model');
            $auth->useModel($this->user_model);

            $credentials = [
                'email' => $this->input->post('email'),
                'code'  => $this->input->post('code')
            ];

            $password     = $this->input->post('password');
            $pass_confirm = $this->input->post('pass_confirm');

            if ($auth->resetPassword($credentials, $password, $pass_confirm))
            {
                $this->setMessage(lang('auth.new_password_success'), 'success');
                redirect( Route::named('login') );
            }
            else
            {
                $this->setMessage($auth->error(), 'danger');
            }
        }

        $data = [
            'email' => $this->input->get('e'),
            'code'  => $this->input->get('code')
        ];

        $this->addScript('register.js');
        $this->themer->setLayout('login');
        $this->render($data);
    }

    //--------------------------------------------------------------------

	/**
	 * Allows a logged in user to enter their current password
	 * and create a new one. Often used as part of the force password
	 * reset process, but could be used within a user area.
	 */
	public function change_password()
	{
		$auth = new LocalAuthentication();
		$this->load->model('user_model');
		$auth->useModel($this->user_model);

		if (! $auth->isLoggedIn())
		{
			redirect( Route::named('login') );
		}

		$this->load->helper('form');

		if ($this->input->post())
		{
			$current_pass = $this->input->post('current_pass');
			$password     = $this->input->post('password');
			$pass_confirm = $this->input->post('pass_confirm');

			// Does the current password match?
			if (! password_verify($current_pass, $auth->user()['password_hash']))
			{
				$this->setMessage( lang('auth.bad_current_pass'), 'warning');
				redirect( current_url() );
			}

			// Do the passwords match?
			if ($password != $pass_confirm)
			{
				$this->setMessage( lang('auth.pass_must_match'), 'warning');
				redirect( current_url() );
			}

			$hash = \Myth\Auth\Password::hashPassword($password);

			if (! $this->user_model->update( $auth->id(), ['password_hash' => $hash, 'force_pass_reset' => 0]) )
			{
				$this->setMessage( 'Error: '. $this->user_model->error(), 'danger');
				redirect( current_url() );
			}

			$redirect_url = $this->session->userdata('redirect_url');
			unset($_SESSION['redirect_url']);

			$this->setMessage( lang('auth.new_password_success'), 'success' );

			$auth->logout();

			redirect( Route::named('login') );
		}

		$this->addScript('register.js');
		$this->themer->setLayout('login');
		$this->render();
	}

	//--------------------------------------------------------------------


    //--------------------------------------------------------------------
    // AJAX Methods
    //--------------------------------------------------------------------

    /**
     * Checks the password strength and returns pass/fail.
     *
     * @param $str
     */
    public function password_check($str)
    {
        $this->load->helper('auth/password');

        $strength = isStrongPassword($str);

        $this->renderJSON(['status' => $strength ? 'pass' : 'fail']);
    }

    //--------------------------------------------------------------------

}
