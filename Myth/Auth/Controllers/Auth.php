<?php namespace Myth\Auth\Controllers;

use App\Domains\Users\User;
use App\Domains\Users\UserModel;
use App\Controllers\BaseController;
use \Myth\Auth\Authenticate\LocalAuthentication;
use Myth\Auth\Models\LoginModel;

class Auth extends BaseController
{
	protected $config;

	/**
	 * @var LocalAuthentication
	 */
	protected $auth;

    public function __construct(...$params)
    {
        parent::__construct(...$params);

        $this->config = new \Myth\Auth\Config\Auth;
        $this->auth = new LocalAuthentication($this->config, new UserModel(), new LoginModel());
    }

    //--------------------------------------------------------------------

    public function login()
    {
        helper('form');

        $redirectURL = session('redirect_url');

        // No need to login again if they are already logged in...
        if ($this->auth->isLoggedIn())
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
		$redirectURL = session('redirect_url');

		$post_data = [
			'email'    => $this->request->getVar('email'),
			'password' => $this->request->getVar('password')
		];

		$remember = (bool)$this->request->getVar('remember');

		if ($this->auth->login($post_data, $remember))
		{
			// Is the user being forced to reset their password?
			if ($this->auth->user()['force_pass_reset'] == 1)
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
        if ($this->auth->isLoggedIn())
        {
            $this->auth->logout();

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
		if (! $this->validate([
			'first_name' => 'required|min_length[2]|max_length[255]',
			'last_name' => 'required|min_length[2]|max_length[255]',
			'email' => 'required|valid_email|max_length[255]|is_unique[users.email]',
			'username' => 'required|min_length[5]|max_length[255]|is_unique[users.username]',
			'password' => 'required|min_length[8]|max_length[255]|strong_password',
			'pass_confirm' => 'required|matches[password]'
		]))
		{
			redirect_with_input('register');
		};

		helper('form');

		$user = new User([
			'first_name'   => $this->request->getPost('first_name'),
			'last_name'    => $this->request->getPost('last_name'),
			'email'        => $this->request->getPost('email'),
			'username'     => $this->request->getPost('username'),
			'password'     => $this->request->getPost('password'),
		]);

		$user = $this->auth->registerUser($user);

		if (! empty($user))
		{
			$user->addToGroup($this->config->defaultGroup);
			$this->setMessage(lang('Auth.didRegister'), 'success');
			redirect('login');
		}

		$this->setMessage(implode('<br>', $this->auth->error()), 'danger');
		redirect_with_input('register');
	}

	//--------------------------------------------------------------------

    public function activateUser()
    {
        helper('form');

        if ($this->request->getMethod() === 'post')
        {
            $post_data = [
                  'email' => $this->request->getPost('email'),
                  'code'  => $this->request->getPost('code')
            ];

            if ($this->auth->activateUser($post_data))
            {
                $this->setMessage(lang('auth.did_activate'), 'success');
                redirect( Route::named('login') );
            }
            else
            {
                $this->setMessage($this->auth->error(), 'danger');
            }
        }

        $data = [
            'email' => $this->request->getPost('e'),
            'code'  => $this->request->getPost('code')
        ];

        $this->layout = 'login';
        $this->render('Myth\Auth\Views\activate_user', $data);
    }

    //--------------------------------------------------------------------


    public function forgotPassword()
    {
        helper('form');

        if ($this->request->getMethod() === 'post')
        {
            if ($this->auth->remindUser($this->request->getPost('email')))
            {
                $this->setMessage(lang('auth.send_success'), 'success');
                redirect('reset_pass' );
            }
            else
            {
                $this->setMessage($this->auth->error(), 'danger');
            }
        }

        $this->layout = 'login';
        $this->render('Myth\Auth\Views\login');
    }

    //--------------------------------------------------------------------

    public function resetPassword()
    {
        helper('form');

        if ($this->request->getMethod() === 'post')
        {
            $credentials = [
                'email' => $this->request->getPost('email'),
                'code'  => $this->request->getPost('code')
            ];

            $password     = $this->request->getPost('password');
            $pass_confirm = $this->request->getPost('pass_confirm');

            if ($this->auth->resetPassword($credentials, $password, $pass_confirm))
            {
                $this->setMessage(lang('auth.new_password_success'), 'success');
                redirect('login');
            }
            else
            {
                $this->setMessage($this->auth->error(), 'danger');
            }
        }

        $data = [
            'email' => $this->request->getVar('e'),
            'code'  => $this->request->getVar('code')
        ];

//        $this->addScript('register.js');
        $this->layout = 'login';
        $this->render('Myth\Auth\Views\reset_password', $data);
    }

    //--------------------------------------------------------------------

	/**
	 * Allows a logged in user to enter their current password
	 * and create a new one. Often used as part of the force password
	 * reset process, but could be used within a user area.
	 */
	public function changePassword()
	{
		if (! $this->auth->isLoggedIn())
		{
			redirect('login');
		}

		helper('form');

		if ($this->request->getMethod() === 'post')
		{
			$current_pass = $this->request->getVar('current_pass');
			$password     = $this->request->getVar('password');
			$pass_confirm = $this->request->getVar('pass_confirm');

			// Does the current password match?
			if (! password_verify($current_pass, $this->auth->user()['password_hash']))
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

			if (! $this->userModel->update( $auth->id(), ['password_hash' => $hash, 'force_pass_reset' => 0]) )
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
        helper('auth/password');

        $strength = isStrongPassword($str);

        $this->renderJSON(['status' => $strength ? 'pass' : 'fail']);
    }

    //--------------------------------------------------------------------

}
