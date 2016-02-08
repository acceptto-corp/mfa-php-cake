<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Network\Http\Client;


/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
	
	public function beforeFilter(\Cake\Event\Event $event)
	{
	    $this->Auth->allow(['add', 'mfa']);
	}

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
        $this->set('_serialize', ['users']);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);

        $this->set('user', $user);
        $this->set('_serialize', ['user']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
	
	/**
	* Login method
	*/
	public function login()
	{
	    if ($this->request->is('post')) {
	        $user = $this->Auth->identify();
	        if ($user) {
				if (strlen($user['mfa_email']) == 0) {
		            $this->Auth->setUser($user);
		            return $this->redirect($this->Auth->redirectUrl());	
				}
				else {
					$session = $this->request->session();
					$session->write('user', $user);
					$http = new Client();
					$mfa_email = $user['mfa_email'];
					$uid = '2d75bf9f5f34fb214b8ee95b682365efbd9090e68bd481d15460043cf7357eba';
					$secret = '91d48760d5f65ae27a643c7d11f2a70f4e6e1d8c4d4faa82b0141c3a46d63b6b';
					$response = $http->get("https://www.acceptto.com/api/v9/authenticate_with_options?message=My+Application+is+wishing+to+authorize&type=Login&email=$mfa_email&uid=$uid&secret=$secret&timeout=90");
					$data = $response->json;
					$channel = $data['channel'];
					
					if (strlen($channel) > 0) {
						$session->write('channel', $channel);
						return $this->redirect("https://mfa.acceptto.com/mfa/index?channel=$channel&callback_url=http://localhost:8765/users/mfa");	
					}
					else {
						$error = $data['message'];
						$this->Flash->error("Login failed: $error");
					}
				}
	        }
			else {
	        	$this->Flash->error('Your username or password is incorrect.');
			}
	    }
	}
	
	/**
	* Check MFA Method
	*/
	public function mfa()
	{
		$session = $this->request->session();
		$user = $session->read('user');
		$channel = $session->read('channel');
		
		if (empty($channel) || empty($user)) {
			$this->Flash->error('Invalid parameters.');
			return $this->redirect($this->Auth->redirectUrl());	
		}
		
		$mfaEmail = $user['mfa_email'];
		$http = new Client();
		$response = $http->get("https://www.acceptto.com/api/v9/check?email=$mfaEmail&channel=$channel");
		$data = $response->json;
		$status = $data['status'];
		$message = "Multi factor authorization request was $status";
		
		if ($status == 'approved') {
			$this->Flash->success(__($message));
			$this->Auth->setUser($user);
			$this->redirect($this->Auth->redirectUrl());	
		}
		else {
			$this->Flash->error(__($message));
			$this->redirect(['action' => 'login']);
		}
	}
	
	/**
	* Logout method
	*/
	public function logout()
	{
	    $this->Flash->success('You are now logged out.');
	    return $this->redirect($this->Auth->logout());
	}
}
