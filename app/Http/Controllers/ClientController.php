<?php

namespace App\Http\Controllers;

use App\Client;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Support\Facades\Input;

use Redirect;
use Validator;
use Hash;
use Session;
use Auth;
use View;
use DB;
use App\DateTime;
use Illuminate\Http\Request;

use App\Transactions;
use App\Queue;

class ClientController extends Controller {
 
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;
  
  	protected $loginPath = 'client/login';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
	public function __construct(){

	}


    protected function validator(array $data)
    {
        return Validator::make($data, [
            'transaction_id' => 'required|max:255',
            'verification_code' => 'required',            
        ]);
    }

    public function index(){
    	return view('client.login');
    }

    public function post_login(){
    	//verify post if transaction id exists
    	//once login insert into tbl_queue

       $input = Input::all();
        $if_record_exist = Transactions::where('transactions_id', Input::get('transactionsID'))->where('verification_code', Input::get('verification_code'))->get();

        
       if ( $if_record_exist ) {
         //if we have valid transaction then insert in the queue
            $queue = new Queue;
            $queue->transactionID_fk = Input::get('transactionsID');
            $queue->processID_fk = 1;
            $queue->counterID_fk = 1;            
            $queue->save();

            $data['msg'] = 'Transaction verified';
            return view('client.login', $data);
        } else {
            $data['msg'] = 'Transaction unverified, please check your transaction details';
            return view('client.login', $data);
        }

    }

   
}