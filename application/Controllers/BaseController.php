<?php namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;

class BaseController extends Controller
{
    /**
     * Stores view data.
     * @var array
     */
    protected $data = [];

    protected $layout = 'master';

    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function render(string $view)
    {
        $content = view($view, $this->data);

        $layout = view('layouts/'.$this->layout, $this->data);
        $layout = str_replace('{content}', $content, $layout);

        echo $layout;
    }
}
