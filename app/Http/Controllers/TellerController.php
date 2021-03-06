<?php

namespace App\Http\Controllers;

use Request;
use App\Counter;
use App\Requests;
use Response;
use App\Http\Controllers\Controller;
use Redirect;
use Validator;
use Hash;
use App\QRCodeReader;
use Illuminate\Support\Facades\Input;
use Session;
use Auth;
use View;
use DB;
use App\DateTime;
use App\Queue;
use App\ClientInfo;
use App\Transactions;
use App\TransactionType;
use App\RegisterLicense;
use App\RegisterVehicle;
use App\Teller;

class TellerController extends Controller {

    protected $session = array();
    protected $data_view = array();

  public function __construct(){
    //debugging
    DB::enableQueryLog();

    
  }

   //login post
  public function login(Request $request) {    

    $input = Input::all();
    //if we have session then redirect
    /*if( Session::has('teller_info') ){
      $counter = Session::get('teller_info');
      //$redirect = $this->__get_page( $counter->counter_id );
       return Redirect::intended('/dashboard');
    }*/

    //if we don't have post
    if( count($input) < 1 ){
       return view('teller.login');
    }
    $remember = (Input::has('remember')) ? true : false;

    //authenticate the login information posted
    $auth = Auth::attempt([
      'email' => $input['email'],
      'password' => $input['password']
      ], $remember
    );
    //authentication successful
    if ($auth) {
      $user = Auth::user();
      $user->status = 1;
      $user->save();
      $counter_label = $this->get_counter_labels()[ $user->counter_id ];
      $counter_label = str_replace(' ', '_', $counter_label);

      //once we authentication is successful, then put the necessary information into the session
      Session::put( $counter_label, Auth::user() );
      

      //redirect to the dashboard
      return Redirect::intended( '/pages/'.$counter_label );
    } else {
      //flash error message on the page
      $data['msg'] = 'Invalid login details';
      return view('teller.login', $data);
    }
  }

    
  private function initialize( $page ){
    //check if we have session, then redirect appropriately
    
    $this->session = Session::get($page);
    $counter_label = $this->get_counter_labels()[ $this->session->counter_id ];
    $this->data_view['counter_label'] = strtoupper( $counter_label );
    $this->data_view['counter_label_'] = $counter_label;


    if( Session::has($page) ){
      $this->data_view['start'] = true;
      $this->session = Session::get($page);
      
      $this->data_view['session'] = $this->session;

      //check if the teller is starting to accept clients
      if( Session::has('start') ){
      
        //get all how many on queue on that teller
        $queue_pending = Queue::where('counterID_fk', $this->session['counter_id'])
                      ->where('status', 0)
                      ->leftJoin('tbl_register_license', 'id', '=', 'transactionID_fk')
                      ->leftJoin('tbl_client_info', 'tbl_client_info.client_id', '=', 'tbl_register_license.client_id')
                      ->orderBy('skipped','desc')
                      ->orderBy('queue_id', 'asc')
                      ->orderByRaw('FIELD("client_type", "1,2,0")')
                      ->get();
        //do not display the currently serving queue
        if( count($queue_pending) > 0 && !empty($queue_pending) ){
          $first = 0; 
          $first_queue = $queue_pending[$first];      
          unset( $queue_pending[$first] );
          //end   
        }
        $this->data_view['queue_pending_details'] = $queue_pending;

        $queue_pending = count($queue_pending);
        $this->data_view['queue_pending'] = $queue_pending;

        
        //get the current priority number
        $queue = Queue::where('counterID_fk', '=', $this->session->counter_id)
                      ->where('status', 0)
                      ->leftJoin('tbl_register_license', 'id', '=', 'transactionID_fk')
                      ->leftJoin('tbl_client_info', 'tbl_client_info.client_id', '=', 'tbl_register_license.client_id')
                      ->orderBy('queue_id', 'asc')
                      ->orderByRaw('FIELD("client_type", "1,2,0")')
                      ->limit(1)
                      ->first();


        // $queue = Queue::where('counterID_fk', $this->session['counter_id'])
        //               ->where('status', 0)
        //               ->leftJoin('tbl_register_vehicle', 'id', '=', 'transactionID_fk')
        //               ->leftJoin('tbl_client_info', 'tbl_client_info.client_id', '=', 'tbl_register_vehicle.client_id')
        //               ->orderBy('queue_id', 'asc')
        //               ->orderByRaw('FIELD("client_type", "1,2,0")')
        //               ->limit(1)
        //               ->first();

        if( count($queue) > 0 && !empty($queue) ){   

          $current_serve = $queue->queue_id;
          $current_serve_label = $queue->queue_label;  
          $this->data_view['client_info'] = $first_queue;

          //var_dump($queue);
          //get transaction type
          $transaction_info = RegisterLicense::find( $queue->transactionID_fk);
          $transaction_info = RegisterVehicle::find( $queue->transactionID_fk);
         // var_dump($transaction_info);
          $this->data_view['transaction_info'] = $transaction_info;

          //$this->data_view['transaction_info']->transaction_type_name = $this->get_transaction_labels()[ $transaction_info->transaction_type ];

          //counter timer label
          $counter_timer = Counter::find( $queue->counterID_fk );

          //get minutes 0h:0m:0s after explode array(0h, 0m, 0s);
          $counter_timer = explode(':', $counter_timer->estimated_time );
         //remove leading 0
          $minutes = ($counter_timer[1] == '00') ? 0 : ltrim($counter_timer[1], '0');
          $seconds = ($counter_timer[2] == '00') ? 0 : ltrim($counter_timer[2], '0');
        } else {
          $current_serve = 0;
          $current_serve_label = 0;
          $minutes = 0;
          $seconds = 0;
        }

        $this->data_view['minutes'] = $minutes;
        $this->data_view['seconds'] = $seconds;

        $this->data_view['current_serve'] = $current_serve;
        $this->data_view['current_serve_label'] = $current_serve_label;
      
      } else {
        $this->data_view['minutes'] = 0;
        $this->data_view['seconds'] = 0;

        $this->data_view['current_serve'] = 0;
        $this->data_view['current_serve_label'] = 0;
        $this->data_view['queue_pending'] = 0;
        $this->data_view['queue_pending_details'] = 0;
        $this->data_view['start'] = false;
      }
    } 
  }

  public function totalServed()
  {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
    header('Access-Control-Allow-Credentials: true');


    $status = Queue::where('status', '=', 0)->count();

    return Response::json($status);
    
  }

  //login get in routes
  //default page
  public function index(){
  //check if we have session if has, then redirect
    if( Session::has('teller_info') ){
      $counter = Session::get('teller_info');
      //$redirect = $this->__get_page( $counter->counter_id );
       return view('dashboard.index', $this->data_view  );
    } else {
      return view('teller.login');  
    }
  }

  //get all counter labels
  private function get_counter_labels(){
    $counters = Counter::all();
    foreach( $counters as $counter ){
      $tmp_array[ $counter->counter_id ] = $counter->counter_name;
    }
    return $tmp_array;
  }

  //get all transaction type labels
  private function get_transaction_labels(){
    $transaction_types = TransactionType::all();
    foreach( $transaction_types as $transaction_type ){
      $tmp_array[ $transaction_type->transaction_type_id ] = $transaction_type->transaction_type_name;
    }
    return $tmp_array;
  }

  //registration logic
  public function register(){
    
    $counters = $this->get_counter_labels();
    return view('teller.register')->with('owners', $counters);
  }

  public function get_logout(Request $request){
    //flush session if we are going to logout since we're using itr
    $which = Input::get('s');
    $user = Auth::user();
      $user->status = 0;
      $user->save();
      Session::forget( $which );
    Session::forget('start');

    return Redirect::intended('/');
  }

  public function page_registration(){

    $page = 'registration';   
    if( Session::has($page) ){
      $this->initialize($page);
      return view('dashboard.index', $this->data_view);  
    } else {
      return Redirect::intended('/');
    }
  }

  public function page_approving(){

    $page = 'approving';   
    if( Session::has($page) ){
      $this->initialize($page);
      return view('dashboard.index', $this->data_view);  
    } else {
      return Redirect::intended('/');
    }
    
  }

  public function page_releasing(){

    $page = 'releasing';   
    if( Session::has($page) ){
      $this->initialize($page);
      return view('dashboard.index', $this->data_view);  
    } else {
      return Redirect::intended('/');
    }
    
  }

  public function page_photo_and_signature(){

    $page = 'photo_and_signature';   
    if( Session::has($page) ){
      $this->initialize($page);
      return view('dashboard.index', $this->data_view);  
    } else {
      return Redirect::intended('/');
    }
    
  }

  public function page_cashier(){

    $page = 'cashier';   
    if( Session::has($page) ){
      $this->initialize($page);
      return view('dashboard.index', $this->data_view);  
    } else {
      return Redirect::intended('/');
    }
    
  }


 
    public function store(Request $request)
    {
        $data = Request::all();


        $rules = [
            'lastname'=> 'required|string',
            'firstname' => 'required|string',
            'gender'=> 'required|string',
            'birth'=> 'required|string',
            'address'=> 'required',
            'mobile'=> 'required|numeric',
            'email' => 'required|email',
            'counter' => 'required|string'
        ];

        $messages = [
            'lastname.required'=> 'Should not be empty',
            'lastname.string' => 'Letters only',
            'firstname.required' => 'Should not be empty',
            'firstname.string'=> 'Letters only',
            'gender.required'=> 'Should not be empty',
            'gender.string'  => 'Letters only',
            'birth.required'=> 'Should not be empty',
            'birthdate.date' => 'Date only',
            'address.required' => 'Should not be empty',
            'mobile.required' => 'Should not be empty',
            'email.required' => 'Should not be empty',
            'email.email' => 'Should be email'
        ];

        $validation = Validator::make($data, $rules, $messages);
        if ($validation->passes()) {
                $password = $data['password'];
                $password = Hash::make($password);

                $r = Input::get('counter');

                $teller = new Teller;
                $teller->firstname= $data['firstname'];
                $teller->lastname= $data['lastname'];
                $teller->gender= $data['gender'];
                $teller->birth= $data['birth'];
                $teller->address = $data['address'];
                $teller->mobile  = $data['mobile'];
                $teller->email = $data['email'];
                $teller->counter_id = $r;
                $teller->password = $password;
                $teller->save();
                return Redirect::to('/teller/login');
               } 
               else {
                    return Redirect::back()->withInput()->withErrors($validation);
               }
    }


   
   

  
    
}
